@extends('admin-web.layouts.app')

@section('title', 'Cộng đồng')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Cộng đồng</h2>
            <p>Theo dõi nhà cung cấp, khách hàng và luồng lời mời ngay trong SSR admin.</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.community.posts.view'))
                <a class="btn btn-secondary" href="{{ route('admin-web.posts.index') }}">Quản lý bài viết</a>
            @endif
            @if($adminUser->hasAdminPermission('admin.community.invitation.create'))
                <a class="btn btn-primary" href="{{ route('admin-web.community.invitations.create') }}">Tạo lời mời</a>
            @endif
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Lời mời</h3>
            @if(empty($payload['invitations']))
                <div class="empty">Chưa có lời mời nào được tạo.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Nhà cung cấp</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['invitations'] as $invitation)
                            <tr>
                                <td>{{ $invitation['supplier_name'] }}</td>
                                <td>{{ $invitation['contact_name'] }}<br><span class="muted small">{{ $invitation['email'] }}</span></td>
                                <td>{{ $labels::invitationStatus($invitation['status']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3>Nhà cung cấp</h3>
            @if(empty($payload['suppliers']))
                <div class="empty">Chưa có hồ sơ nhà cung cấp nào.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Liên hệ</th>
                            <th>Sản phẩm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['suppliers'] as $supplier)
                            <tr>
                                <td>{{ $supplier['name'] }}</td>
                                <td>{{ $supplier['contact_name'] }}<br><span class="muted small">{{ $supplier['email'] }}</span></td>
                                <td>{{ $supplier['product_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3>Khách hàng</h3>
            @if(empty($payload['customers']))
                <div class="empty">Chưa có dữ liệu cộng đồng của khách hàng.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['customers'] as $customer)
                            <tr>
                                <td>{{ $customer['full_name'] }}</td>
                                <td>{{ $customer['email'] }}</td>
                                <td>{{ $customer['order_count'] }}</td>
                                <td>{{ number_format((float) $customer['total_spend']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
