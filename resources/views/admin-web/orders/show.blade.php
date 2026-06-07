@extends('admin-web.layouts.app')

@section('title', 'Chi tiet don hang')

@section('content')
    @php
        $labels = \App\Support\AdminWebLabel::class;
        $shipment = $order->shipment;
        $ghnCarrier = $shippingCarriers->firstWhere('provider', \App\Models\ShippingCarrier::PROVIDER_GHN);
        $selectedCarrier = old('shipping_carrier_id', $ghnCarrier?->id ?? $shippingCarriers->first()?->id);
        $canCreateShipment = ! $shipment
            && $order->status === \App\Models\Order::STATUS_CONFIRMED
            && (bool) $order->stock_deducted
            && $shippingCarriers->isNotEmpty();
        $selectedProvinceId = (int) old('shipping_province_id', $order->shipping_province_id ?? 0);
    @endphp

    <div class="toolbar">
        <div>
            <h2>{{ $order->order_no }}</h2>
            <p>Trang chi tiet cho cac thao tac xu ly, thanh toan va van don.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('admin-web.orders.index') }}">Quay lai dieu phoi</a>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Tom tat don hang</h3>
            <table>
                <tbody>
                    <tr><th>Khach hang</th><td>{{ $orderPayload['customer']['full_name'] ?? $order->recipient_name }}</td></tr>
                    <tr><th>Nguoi nhan</th><td>{{ $order->recipient_name }} - {{ $order->recipient_phone }}</td></tr>
                    <tr><th>Trang thai</th><td>{{ $labels::orderStatus($order->status) }}</td></tr>
                    <tr><th>Thanh toan</th><td>{{ $labels::paymentStatus($order->payment?->payment_status) }}</td></tr>
                    <tr><th>Phuong thuc</th><td>{{ $labels::paymentMethod($order->payment_method) }}</td></tr>
                    <tr><th>Tong tien</th><td>{{ number_format((float) $order->total_amount) }}</td></tr>
                    <tr><th>Phi ship checkout</th><td>{{ number_format((float) $order->shipping_fee) }}</td></tr>
                    <tr><th>Dia chi giao hang</th><td>{{ $order->shipping_address }}</td></tr>
                    <tr><th>Ghi chu</th><td>{{ $order->note ?: 'Khong co' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Thao tac trang thai</h3>
            <form method="POST" action="{{ route('admin-web.orders.status.update', $order->id) }}" class="form-grid">
                @csrf
                @method('PATCH')
                <label>
                    Trang thai tiep theo
                    <select name="status">
                        @foreach($allStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status') === $status)>{{ $labels::orderStatus($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Hoan kho
                    <select name="restock_inventory">
                        <option value="">Khong thay doi</option>
                        <option value="1">Co</option>
                        <option value="0">Khong</option>
                    </select>
                </label>
                <label class="full">
                    Ghi chu
                    <textarea name="note">{{ old('note') }}</textarea>
                </label>
                <div class="full">
                    <button class="btn btn-primary" type="submit">Cap nhat trang thai</button>
                </div>
            </form>

            <h3 style="margin-top: 22px;">Thao tac thanh toan</h3>
            <form method="POST" action="{{ route('admin-web.orders.payment.update', $order->id) }}" class="form-grid">
                @csrf
                @method('PATCH')
                <label>
                    Trang thai thanh toan tiep theo
                    <select name="payment_status">
                        @foreach($allPaymentStatuses as $status)
                            <option value="{{ $status }}" @selected(old('payment_status') === $status)>{{ $labels::paymentStatus($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="full">
                    Ghi chu
                    <textarea name="note">{{ old('payment_note') }}</textarea>
                </label>
                <div class="full">
                    <button class="btn btn-primary" type="submit">Cap nhat thanh toan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Van don GHN</h3>

            @if($shipment)
                <table>
                    <tbody>
                        <tr><th>Don vi</th><td>{{ $shipment->carrier?->name ?? $shipment->provider }}</td></tr>
                        <tr><th>Nha cung cap</th><td>{{ $labels::shippingProvider($shipment->provider) }}</td></tr>
                        <tr><th>Trang thai GHN</th><td>{{ $shipment->status }}</td></tr>
                        <tr><th>Ma van don</th><td>{{ $shipment->tracking_code ?: 'Chua co' }}</td></tr>
                        <tr>
                            <th>Tracking</th>
                            <td>
                                @if($shipment->tracking_url)
                                    <a href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener">{{ $shipment->tracking_url }}</a>
                                @else
                                    Chua co
                                @endif
                            </td>
                        </tr>
                        <tr><th>Phi GHN</th><td>{{ $shipment->shipping_fee !== null ? number_format((float) $shipment->shipping_fee) : 'Chua co' }}</td></tr>
                        <tr><th>COD</th><td>{{ $shipment->cod_amount !== null ? number_format((float) $shipment->cod_amount) : '0' }}</td></tr>
                        <tr><th>Du kien giao</th><td>{{ $shipment->expected_delivery_time?->format('d/m/Y H:i') ?: 'Chua co' }}</td></tr>
                        <tr><th>Dong bo lan cuoi</th><td>{{ $shipment->synced_at?->format('d/m/Y H:i') ?: 'Chua dong bo' }}</td></tr>
                    </tbody>
                </table>

                <div class="row" style="margin-top: 14px;">
                    <form method="POST" action="{{ route('admin-web.orders.shipment.sync', $order->id) }}">
                        @csrf
                        <button class="btn btn-secondary" type="submit">Dong bo GHN</button>
                    </form>
                    <form method="POST" action="{{ route('admin-web.orders.shipment.destroy', $order->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit" data-confirm="Huy van don nay?">Huy van don</button>
                    </form>
                </div>
            @elseif($canCreateShipment)
                <form method="POST" action="{{ route('admin-web.orders.shipment.store', $order->id) }}" class="form-grid" data-ghn-shipment-form>
                    @csrf
                    <label>
                        Don vi van chuyen
                        <select name="shipping_carrier_id">
                            @foreach($shippingCarriers as $carrier)
                                <option value="{{ $carrier->id }}" @selected((int) $selectedCarrier === $carrier->id)>
                                    {{ $carrier->name }} ({{ $labels::shippingProvider($carrier->provider) }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Loai dich vu
                        <select name="service_type_id">
                            <option value="2" @selected((int) old('service_type_id', $ghnCarrier?->default_service_type_id ?? 2) === 2)>2 - Thuong mai dien tu</option>
                            <option value="5" @selected((int) old('service_type_id', $ghnCarrier?->default_service_type_id ?? 2) === 5)>5 - Hang nhe</option>
                            <option value="1" @selected((int) old('service_type_id', $ghnCarrier?->default_service_type_id ?? 2) === 1)>1 - Dich vu nhanh</option>
                        </select>
                    </label>
                    <label>
                        Nguoi nhan
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}">
                    </label>
                    <label>
                        So dien thoai
                        <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}">
                    </label>
                    <label class="full">
                        Dia chi chi tiet
                        <input type="text" name="shipping_line1" value="{{ old('shipping_line1', $order->shipping_line1 ?: $order->shipping_address) }}">
                    </label>
                    <label>
                        Tinh/thanh
                        <input type="hidden" name="shipping_province_name" value="{{ old('shipping_province_name', $order->shipping_province_name) }}">
                        <select name="shipping_province_id" data-location-level="province" data-location-next="[name='shipping_district_id']" data-location-name-target="shipping_province_name">
                            <option value="">Chon</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['ProvinceID'] }}" @selected($selectedProvinceId === $province['ProvinceID'])>{{ $province['ProvinceName'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Quan/huyen
                        <input type="hidden" name="shipping_district_name" value="{{ old('shipping_district_name', $order->shipping_district_name) }}">
                        <select name="shipping_district_id" data-location-level="district" data-location-next="[name='shipping_ward_code']" data-location-name-target="shipping_district_name">
                            <option value="{{ old('shipping_district_id', $order->shipping_district_id) }}">{{ old('shipping_district_name', $order->shipping_district_name ?: 'Chon') }}</option>
                        </select>
                    </label>
                    <label>
                        Phuong/xa
                        <input type="hidden" name="shipping_ward_name" value="{{ old('shipping_ward_name', $order->shipping_ward_name) }}">
                        <select name="shipping_ward_code" data-location-name-target="shipping_ward_name">
                            <option value="{{ old('shipping_ward_code', $order->shipping_ward_code) }}">{{ old('shipping_ward_name', $order->shipping_ward_name ?: 'Chon') }}</option>
                        </select>
                    </label>
                    <label>
                        Ben tra phi
                        <select name="payment_type_id">
                            <option value="1" @selected((int) old('payment_type_id', $ghnCarrier?->default_payment_type_id ?? 1) === 1)>Shop tra phi</option>
                            <option value="2" @selected((int) old('payment_type_id', $ghnCarrier?->default_payment_type_id ?? 1) === 2)>Khach tra phi</option>
                        </select>
                    </label>
                    <label>
                        Ghi chu bat buoc
                        <select name="required_note">
                            @foreach(['KHONGCHOXEMHANG' => 'Khong cho xem hang', 'CHOXEMHANGKHONGTHU' => 'Cho xem hang khong thu', 'CHOTHUHANG' => 'Cho thu hang'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('required_note', $ghnCarrier?->default_required_note ?? 'KHONGCHOXEMHANG') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Can nang (gram)
                        <input type="number" min="1" name="weight" value="{{ old('weight', $ghnCarrier?->default_weight ?? 1000) }}">
                    </label>
                    <label>
                        Dai (cm)
                        <input type="number" min="1" name="length" value="{{ old('length', $ghnCarrier?->default_length ?? 20) }}">
                    </label>
                    <label>
                        Rong (cm)
                        <input type="number" min="1" name="width" value="{{ old('width', $ghnCarrier?->default_width ?? 20) }}">
                    </label>
                    <label>
                        Cao (cm)
                        <input type="number" min="1" name="height" value="{{ old('height', $ghnCarrier?->default_height ?? 10) }}">
                    </label>
                    <label class="full">
                        Ghi chu van don
                        <textarea name="note">{{ old('shipment_note', $order->note) }}</textarea>
                    </label>
                    <div class="full">
                        <button class="btn btn-primary" type="submit">Tao van don GHN</button>
                    </div>
                </form>
            @else
                <div class="empty">
                    Can xac nhan don, tru kho va cau hinh don vi van chuyen truoc khi tao van don GHN.
                </div>
            @endif
        </div>

        <div class="card">
            <h3>San pham</h3>
            <table>
                <thead>
                    <tr>
                        <th>San pham</th>
                        <th>So luong</th>
                        <th>Don gia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderPayload['items'] as $item)
                        <tr>
                            <td>{{ $item['product_name_snapshot'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>{{ number_format((float) $item['unit_price']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
