@extends('admin-web.layouts.app')

@section('title', 'Đơn vị vận chuyển')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Đơn vị vận chuyển</h2>
            <p>Quản lý danh bạ đối tác vận chuyển và thông tin mặc định cho vận đơn từ các form Laravel.</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.shipping_carriers.create'))
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="{{ route('admin-web.shipping-carriers.create') }}">Tạo đơn vị vận chuyển</a>
            </div>
        @endif
    </div>

    <div class="card">
        <h3>Danh sách đơn vị vận chuyển</h3>
        <form class="filters" method="GET" action="{{ route('admin-web.shipping-carriers.index') }}">
            <label>
                Từ khóa
                <input type="text" name="keyword" value="{{ request('keyword') }}">
            </label>
            <label>
                Chỉ hiển thị đang hoạt động
                <select name="active_only">
                    <option value="">Tất cả</option>
                    <option value="1" @selected(request('active_only') === '1')>Chỉ đang hoạt động</option>
                </select>
            </label>
            <button class="btn btn-secondary" type="submit">Lọc</button>
        </form>

        @if($carriers->isEmpty())
            <div class="empty">Không tìm thấy đơn vị vận chuyển nào.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Nhà cung cấp</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carriers as $carrier)
                        <tr>
                            <td>{{ $carrier->code }}</td>
                            <td>{{ $carrier->name }}</td>
                            <td>{{ $labels::shippingProvider($carrier->provider) }}</td>
                            <td>{{ $labels::active((bool) $carrier->is_active) }}</td>
                            <td>
                                <div class="row">
                                    @if($adminUser->hasAdminPermission('admin.shipping_carriers.update'))
                                        <a class="btn btn-secondary" href="{{ route('admin-web.shipping-carriers.edit', $carrier->id) }}">Sửa</a>
                                    @endif
                                    @if($adminUser->hasAdminPermission('admin.shipping_carriers.delete'))
                                        <form method="POST" action="{{ route('admin-web.shipping-carriers.destroy', $carrier->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">Xóa</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @include('admin-web.partials.pagination', ['paginator' => $carriers])
        @endif
    </div>
@endsection
