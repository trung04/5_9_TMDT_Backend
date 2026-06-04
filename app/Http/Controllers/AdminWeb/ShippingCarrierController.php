<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\Admin\ShippingCarrierRequest;
use App\Models\ShippingCarrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingCarrierController extends AdminWebController
{
    public function __construct(\App\Support\AdminNavigation $navigation)
    {
        parent::__construct($navigation);
    }

    public function index(Request $request)
    {
        return $this->render('admin-web.shipping-carriers.index', [
            'carriers' => $this->carriers($request),
        ]);
    }

    public function create()
    {
        return $this->render('admin-web.shipping-carriers.create', [
            'carrier' => new ShippingCarrier([
                'provider' => ShippingCarrier::PROVIDER_MANUAL,
                'default_weight' => 1000,
                'default_length' => 20,
                'default_width' => 20,
                'default_height' => 10,
                'default_service_type_id' => 2,
                'default_payment_type_id' => 1,
                'default_required_note' => 'KHONGCHOXEMHANG',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(ShippingCarrierRequest $request): RedirectResponse
    {
        $carrier = ShippingCarrier::query()->create($this->carrierPayload($request->validated()));

        return redirect()->to(
            $this->adminUser()->hasAdminPermission('admin.shipping_carriers.update')
                ? route('admin-web.shipping-carriers.edit', $carrier)
                : ($this->adminUser()->hasAdminPermission('admin.shipping_carriers.view')
                    ? route('admin-web.shipping-carriers.index')
                    : route('admin-web.shipping-carriers.create'))
        )
            ->with('status', 'Đã tạo đơn vị vận chuyển thành công.');
    }

    public function edit(ShippingCarrier $carrier)
    {
        return $this->render('admin-web.shipping-carriers.edit', [
            'carrier' => $carrier,
        ]);
    }

    public function update(ShippingCarrierRequest $request, ShippingCarrier $carrier): RedirectResponse
    {
        $attributes = $request->validated();

        if (($attributes['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $attributes)) {
            $attributes['is_deleted'] = false;
        }

        $carrier->update($this->carrierPayload($attributes, $carrier));
        $carrier = $carrier->refresh();

        return redirect()
            ->route('admin-web.shipping-carriers.edit', $carrier)
            ->with('status', 'Đã cập nhật đơn vị vận chuyển thành công.');
    }

    public function destroy(ShippingCarrier $carrier): RedirectResponse
    {
        $carrier->markDeleted();

        return redirect()
            ->route('admin-web.shipping-carriers.index')
            ->with('status', 'Đã xóa đơn vị vận chuyển thành công.');
    }

    private function carriers(Request $request)
    {
        $query = ShippingCarrier::query()->orderByDesc('id');

        if ($request->boolean('active_only')) {
            $query->available();
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->query('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('provider', 'like', "%{$keyword}%");
            });
        }

        return $query
            ->paginate((int) $request->integer('per_page', 20))
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function carrierPayload(array $attributes, ?ShippingCarrier $carrier = null): array
    {
        return [
            'code' => $attributes['code'] ?? $carrier?->code,
            'name' => $attributes['name'] ?? $carrier?->name,
            'provider' => $attributes['provider'] ?? $carrier?->provider ?? ShippingCarrier::PROVIDER_MANUAL,
            'tracking_url_template' => ($attributes['tracking_url_template'] ?? $carrier?->tracking_url_template) ?: null,
            'default_weight' => $attributes['default_weight'] ?? $carrier?->default_weight ?? 1000,
            'default_length' => $attributes['default_length'] ?? $carrier?->default_length ?? 20,
            'default_width' => $attributes['default_width'] ?? $carrier?->default_width ?? 20,
            'default_height' => $attributes['default_height'] ?? $carrier?->default_height ?? 10,
            'default_service_type_id' => $attributes['default_service_type_id'] ?? $carrier?->default_service_type_id ?? 2,
            'default_payment_type_id' => $attributes['default_payment_type_id'] ?? $carrier?->default_payment_type_id ?? 1,
            'default_required_note' => $attributes['default_required_note'] ?? $carrier?->default_required_note ?? 'KHONGCHOXEMHANG',
            'pickup_name' => ($attributes['pickup_name'] ?? $carrier?->pickup_name) ?: null,
            'pickup_phone' => ($attributes['pickup_phone'] ?? $carrier?->pickup_phone) ?: null,
            'pickup_address' => ($attributes['pickup_address'] ?? $carrier?->pickup_address) ?: null,
            'pickup_ward_code' => ($attributes['pickup_ward_code'] ?? $carrier?->pickup_ward_code) ?: null,
            'pickup_ward_name' => ($attributes['pickup_ward_name'] ?? $carrier?->pickup_ward_name) ?: null,
            'pickup_district_id' => $attributes['pickup_district_id'] ?? $carrier?->pickup_district_id,
            'pickup_district_name' => ($attributes['pickup_district_name'] ?? $carrier?->pickup_district_name) ?: null,
            'pickup_province_id' => $attributes['pickup_province_id'] ?? $carrier?->pickup_province_id,
            'pickup_province_name' => ($attributes['pickup_province_name'] ?? $carrier?->pickup_province_name) ?: null,
            'settings' => $attributes['settings'] ?? $carrier?->settings,
            'is_active' => $attributes['is_active'] ?? $carrier?->is_active ?? true,
            'is_deleted' => $attributes['is_deleted'] ?? $carrier?->is_deleted ?? false,
        ];
    }
}
