<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use App\Models\ShippingCarrier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderShipmentService
{
    public function __construct(private readonly GhnClient $ghnClient)
    {
    }

    public function createShipment(Order $order, User $actor, array $attributes): Order
    {
        return DB::transaction(function () use ($order, $actor, $attributes): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with(['items', 'payment', 'shipment'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->shipment) {
                throw ValidationException::withMessages([
                    'shipment' => ['Don hang da co van don.'],
                ]);
            }

            if ($lockedOrder->status !== Order::STATUS_CONFIRMED) {
                throw ValidationException::withMessages([
                    'status' => ['Chi don da xac nhan moi co the tao van don.'],
                ]);
            }

            $carrier = ShippingCarrier::query()
                ->available()
                ->findOrFail((int) $attributes['shipping_carrier_id']);

            $this->applyAddressOverrides($lockedOrder, $attributes);

            if ($carrier->provider === ShippingCarrier::PROVIDER_GHN) {
                $shipment = $this->createGhnShipment($lockedOrder, $carrier, $actor, $attributes);
            } else {
                $shipment = $this->createManualShipment($lockedOrder, $carrier, $actor, $attributes);
            }

            $this->moveOrderAfterShipment($lockedOrder, $shipment, $actor, $attributes['note'] ?? null);

            return $lockedOrder->refresh()->load(['items', 'payment', 'shipment.carrier', 'statusHistory', 'paymentStatusHistory', 'user']);
        });
    }

    public function syncShipment(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with(['payment', 'shipment.carrier'])
                ->lockForUpdate()
                ->firstOrFail();

            $shipment = $lockedOrder->shipment;

            if (! $shipment) {
                throw ValidationException::withMessages([
                    'shipment' => ['Don hang chua co van don de dong bo.'],
                ]);
            }

            if ($shipment->provider !== ShippingCarrier::PROVIDER_GHN) {
                throw ValidationException::withMessages([
                    'shipment' => ['Chi van don GHN moi co the dong bo tu dong.'],
                ]);
            }

            $response = $this->ghnClient->detailByClientOrderCode((string) $lockedOrder->order_no);
            $data = is_array($response['data'] ?? null) ? $response['data'] : [];
            $status = (string) ($data['status'] ?? $shipment->status);

            $shipment->update([
                'status' => $status ?: $shipment->status,
                'raw_response' => $response,
                'synced_at' => now(),
                'updated_by_user_id' => $actor->id,
            ]);

            $this->applyGhnStatusToOrder($lockedOrder, $status, $actor);

            return $lockedOrder->refresh()->load(['items', 'payment', 'shipment.carrier', 'statusHistory', 'paymentStatusHistory', 'user']);
        });
    }

    public function cancelShipment(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with('shipment.carrier')
                ->lockForUpdate()
                ->firstOrFail();

            $shipment = $lockedOrder->shipment;

            if (! $shipment) {
                throw ValidationException::withMessages([
                    'shipment' => ['Don hang chua co van don.'],
                ]);
            }

            if (! in_array($lockedOrder->status, [Order::STATUS_CONFIRMED, Order::STATUS_PACKED], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Chi co the huy van don khi don chua ban giao van chuyen.'],
                ]);
            }

            $this->cancelShipmentRecord($shipment, $actor);
            $fromStatus = $lockedOrder->status;

            $lockedOrder->update([
                'status' => Order::STATUS_CONFIRMED,
                'shipping_carrier' => null,
                'shipping_code' => null,
                'shipped_at' => null,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $lockedOrder->id,
                'changed_by_user_id' => $actor->id,
                'from_status' => $fromStatus,
                'to_status' => Order::STATUS_CONFIRMED,
                'note' => 'Da huy van don va dua don ve trang thai da xac nhan.',
                'changed_at' => now(),
            ]);

            return $lockedOrder->refresh()->load(['items', 'payment', 'shipment.carrier', 'statusHistory', 'paymentStatusHistory', 'user']);
        });
    }

    public function cancelActiveShipmentForOrder(Order $order, User $actor): void
    {
        $shipment = $order->shipment()->with('carrier')->first();

        if (! $shipment || $shipment->cancelled_at) {
            return;
        }

        if (! in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PACKED], true)) {
            return;
        }

        $this->cancelShipmentRecord($shipment, $actor);
    }

    public function shipmentPayload(?OrderShipment $shipment): ?array
    {
        if (! $shipment) {
            return null;
        }

        $shipment->loadMissing('carrier');

        return [
            'id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'shipping_carrier_id' => $shipment->shipping_carrier_id,
            'provider' => $shipment->provider,
            'status' => $shipment->status,
            'tracking_code' => $shipment->tracking_code,
            'tracking_url' => $shipment->tracking_url,
            'service_type_id' => $shipment->service_type_id,
            'payment_type_id' => $shipment->payment_type_id,
            'required_note' => $shipment->required_note,
            'weight' => $shipment->weight,
            'length' => $shipment->length,
            'width' => $shipment->width,
            'height' => $shipment->height,
            'shipping_fee' => $shipment->shipping_fee,
            'cod_amount' => $shipment->cod_amount,
            'expected_delivery_time' => optional($shipment->expected_delivery_time)->toISOString(),
            'synced_at' => optional($shipment->synced_at)->toISOString(),
            'cancelled_at' => optional($shipment->cancelled_at)->toISOString(),
            'carrier' => $shipment->carrier ? [
                'id' => $shipment->carrier->id,
                'code' => $shipment->carrier->code,
                'name' => $shipment->carrier->name,
                'provider' => $shipment->carrier->provider,
            ] : null,
            'created_at' => $shipment->created_at,
            'updated_at' => $shipment->updated_at,
        ];
    }

    private function createGhnShipment(Order $order, ShippingCarrier $carrier, User $actor, array $attributes): OrderShipment
    {
        $this->ensureGhnAddressIsComplete($order);
        $payload = $this->ghnCreatePayload($order, $carrier, $attributes);
        $response = $this->ghnClient->createOrder($payload);
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $trackingCode = (string) ($data['order_code'] ?? '');

        if ($trackingCode === '') {
            throw ValidationException::withMessages([
                'shipment' => ['GHN khong tra ve ma van don.'],
            ]);
        }

        return OrderShipment::query()->create([
            'order_id' => $order->id,
            'shipping_carrier_id' => $carrier->id,
            'provider' => $carrier->provider,
            'status' => 'ready_to_pick',
            'tracking_code' => $trackingCode,
            'tracking_url' => $this->trackingUrl($carrier, $trackingCode),
            'service_type_id' => (int) $payload['service_type_id'],
            'payment_type_id' => (int) $payload['payment_type_id'],
            'required_note' => (string) $payload['required_note'],
            'weight' => (int) $payload['weight'],
            'length' => (int) $payload['length'],
            'width' => (int) $payload['width'],
            'height' => (int) $payload['height'],
            'shipping_fee' => $this->decimal((float) ($data['total_fee'] ?? 0)),
            'cod_amount' => $this->decimal((float) $payload['cod_amount']),
            'expected_delivery_time' => ! empty($data['expected_delivery_time'])
                ? Carbon::parse((string) $data['expected_delivery_time'])
                : null,
            'raw_request' => $payload,
            'raw_response' => $response,
            'synced_at' => now(),
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);
    }

    private function createManualShipment(Order $order, ShippingCarrier $carrier, User $actor, array $attributes): OrderShipment
    {
        $trackingCode = trim((string) ($attributes['tracking_code'] ?? ''));

        if ($trackingCode === '') {
            throw ValidationException::withMessages([
                'tracking_code' => ['Can nhap ma van don cho don vi van chuyen thu cong.'],
            ]);
        }

        return OrderShipment::query()->create([
            'order_id' => $order->id,
            'shipping_carrier_id' => $carrier->id,
            'provider' => $carrier->provider,
            'status' => OrderShipment::STATUS_CREATED,
            'tracking_code' => $trackingCode,
            'tracking_url' => $attributes['tracking_url'] ?: $this->trackingUrl($carrier, $trackingCode),
            'service_type_id' => $attributes['service_type_id'] ?? $carrier->default_service_type_id,
            'payment_type_id' => $attributes['payment_type_id'] ?? $carrier->default_payment_type_id,
            'required_note' => $attributes['required_note'] ?? $carrier->default_required_note,
            'weight' => $attributes['weight'] ?? $carrier->default_weight,
            'length' => $attributes['length'] ?? $carrier->default_length,
            'width' => $attributes['width'] ?? $carrier->default_width,
            'height' => $attributes['height'] ?? $carrier->default_height,
            'cod_amount' => $this->decimal($this->codAmount($order)),
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);
    }

    private function moveOrderAfterShipment(Order $order, OrderShipment $shipment, User $actor, ?string $note): void
    {
        $fromStatus = $order->status;

        $order->update([
            'status' => Order::STATUS_PACKED,
            'shipping_carrier' => $shipment->carrier?->name ?? $shipment->provider,
            'shipping_code' => $shipment->tracking_code,
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => Order::STATUS_PACKED,
            'note' => $note ?: 'Da tao van don va chuyen don sang trang thai dong goi.',
            'changed_at' => now(),
        ]);
    }

    private function applyAddressOverrides(Order $order, array $attributes): void
    {
        $keys = [
            'shipping_line1',
            'shipping_province_id',
            'shipping_province_name',
            'shipping_district_id',
            'shipping_district_name',
            'shipping_ward_code',
            'shipping_ward_name',
        ];
        $updates = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $attributes) && $attributes[$key] !== null && $attributes[$key] !== '') {
                $updates[$key] = $attributes[$key];
            }
        }

        if ($updates !== []) {
            $updates['shipping_address'] = $this->formatAddress(
                $updates['shipping_line1'] ?? $order->shipping_line1 ?? $order->shipping_address,
                $updates['shipping_ward_name'] ?? $order->shipping_ward_name,
                $updates['shipping_district_name'] ?? $order->shipping_district_name,
                $updates['shipping_province_name'] ?? $order->shipping_province_name,
            );
            $order->update($updates);
            $order->refresh();
        }
    }

    private function ghnCreatePayload(Order $order, ShippingCarrier $carrier, array $attributes): array
    {
        $weight = (int) ($attributes['weight'] ?? $carrier->default_weight);
        $length = (int) ($attributes['length'] ?? $carrier->default_length);
        $width = (int) ($attributes['width'] ?? $carrier->default_width);
        $height = (int) ($attributes['height'] ?? $carrier->default_height);
        $content = $order->items
            ->map(fn ($item): string => "{$item->product_name_snapshot} x {$item->quantity}")
            ->implode('; ');

        return [
            'payment_type_id' => (int) ($attributes['payment_type_id'] ?? $carrier->default_payment_type_id),
            'note' => (string) ($attributes['note'] ?? $order->note ?? ''),
            'required_note' => (string) ($attributes['required_note'] ?? $carrier->default_required_note),
            'return_phone' => $carrier->pickup_phone,
            'return_address' => $carrier->pickup_address,
            'client_order_code' => $order->order_no,
            'from_name' => $carrier->pickup_name ?: config('app.name', 'Heritage Harvest'),
            'from_phone' => $carrier->pickup_phone ?: '0900000999',
            'from_address' => $carrier->pickup_address ?: 'Kho chinh',
            'from_ward_name' => $carrier->pickup_ward_name ?: 'Phuong Dich Vong Hau',
            'from_district_name' => $carrier->pickup_district_name ?: 'Quan Cau Giay',
            'from_province_name' => $carrier->pickup_province_name ?: 'Ha Noi',
            'to_name' => $order->recipient_name,
            'to_phone' => $order->recipient_phone,
            'to_address' => $order->shipping_line1 ?: $order->shipping_address,
            'to_ward_name' => $order->shipping_ward_name,
            'to_district_name' => $order->shipping_district_name,
            'to_province_name' => $order->shipping_province_name,
            'cod_amount' => (int) round($this->codAmount($order)),
            'content' => mb_substr($content !== '' ? $content : (string) $order->order_no, 0, 2000),
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'weight' => $weight,
            'insurance_value' => min(5000000, (int) round((float) $order->subtotal)),
            'service_type_id' => (int) ($attributes['service_type_id'] ?? $carrier->default_service_type_id),
            'coupon' => null,
            'items' => $order->items->map(fn ($item): array => [
                'name' => mb_substr((string) $item->product_name_snapshot, 0, 255),
                'code' => (string) $item->product_id,
                'quantity' => (int) $item->quantity,
                'price' => (int) round((float) $item->unit_price),
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'weight' => max(1, (int) floor($weight / max(1, $order->items->sum('quantity')))),
            ])->values()->all(),
        ];
    }

    private function ensureGhnAddressIsComplete(Order $order): void
    {
        foreach (['shipping_line1', 'shipping_province_name', 'shipping_district_name', 'shipping_ward_name'] as $field) {
            if (! $order->{$field}) {
                throw ValidationException::withMessages([
                    'shipping_address' => ['Can bo sung day du tinh, quan/huyen, phuong/xa va dia chi chi tiet truoc khi tao van don GHN.'],
                ]);
            }
        }
    }

    private function applyGhnStatusToOrder(Order $order, string $ghnStatus, User $actor): void
    {
        $nextStatus = match ($ghnStatus) {
            'delivered' => Order::STATUS_DELIVERED,
            'delivery_fail', 'waiting_to_return', 'return', 'return_transporting', 'return_sorting', 'returning', 'return_fail', 'returned', 'exception', 'damage', 'lost' => Order::STATUS_DELIVERY_FAILED,
            'cancel' => Order::STATUS_CANCELLED,
            'picking', 'picked', 'storing', 'transporting', 'sorting', 'delivering', 'money_collect_delivering' => Order::STATUS_SHIPPED,
            default => null,
        };

        if (! $nextStatus || $order->status === $nextStatus) {
            return;
        }

        $fromStatus = $order->status;
        $updates = ['status' => $nextStatus];

        if ($nextStatus === Order::STATUS_SHIPPED && ! $order->shipped_at) {
            $updates['shipped_at'] = now();
        }

        if ($nextStatus === Order::STATUS_DELIVERED) {
            $updates['delivered_at'] = now();
        }

        if ($nextStatus === Order::STATUS_CANCELLED) {
            $updates['cancelled_at'] = now();
        }

        $order->update($updates);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => $nextStatus,
            'note' => "Dong bo trang thai GHN: {$ghnStatus}.",
            'changed_at' => now(),
        ]);

        if ($nextStatus === Order::STATUS_DELIVERED && $order->payment_method === Order::PAYMENT_METHOD_COD) {
            $this->markPaymentSuccessForCod($order, $actor);
        }
    }

    private function cancelShipmentRecord(OrderShipment $shipment, User $actor): void
    {
        if ($shipment->provider === ShippingCarrier::PROVIDER_GHN && $shipment->tracking_code) {
            $response = $this->ghnClient->cancelOrders([$shipment->tracking_code]);
        } else {
            $response = ['manual_cancel' => true];
        }

        $shipment->update([
            'status' => OrderShipment::STATUS_CANCELLED,
            'raw_response' => $response,
            'cancelled_at' => now(),
            'synced_at' => now(),
            'updated_by_user_id' => $actor->id,
        ]);
    }

    private function markPaymentSuccessForCod(Order $order, User $actor): void
    {
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();

        if (! $payment || $payment->payment_status === Payment::STATUS_SUCCESS) {
            return;
        }

        $fromStatus = $payment->payment_status;
        $payment->update([
            'payment_status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);

        PaymentStatusHistory::query()->create([
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => Payment::STATUS_SUCCESS,
            'note' => 'Tu dong xac nhan thanh toan COD khi GHN bao da giao thanh cong.',
            'changed_at' => now(),
        ]);
    }

    private function trackingUrl(ShippingCarrier $carrier, string $trackingCode): ?string
    {
        if (! $carrier->tracking_url_template) {
            return null;
        }

        return str_replace('{code}', rawurlencode($trackingCode), $carrier->tracking_url_template);
    }

    private function codAmount(Order $order): float
    {
        return $order->payment_method === Order::PAYMENT_METHOD_COD ? (float) $order->total_amount : 0.0;
    }

    private function formatAddress(?string $line1, ?string $ward, ?string $district, ?string $province): string
    {
        return collect([$line1, $ward, $district, $province])
            ->filter(fn ($part): bool => is_string($part) && trim($part) !== '')
            ->implode(', ');
    }

    private function decimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
