@extends('admin-web.layouts.app')

@section('title', 'Sửa đơn vị vận chuyển')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa đơn vị vận chuyển</h2>
            <p>{{ $carrier->name }} ({{ $carrier->code }})</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.shipping_carriers.delete'))
            <div class="toolbar-actions">
                <form method="POST" action="{{ route('admin-web.shipping-carriers.destroy', $carrier->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa</button>
                </form>
            </div>
        @endif
    </div>

    <div class="card">
        @include('admin-web.shipping-carriers._form', [
            'carrier' => $carrier,
            'formAction' => route('admin-web.shipping-carriers.update', $carrier->id),
            'formMethod' => 'PUT',
            'submitLabel' => 'Lưu thay đổi',
        ])
    </div>
@endsection
