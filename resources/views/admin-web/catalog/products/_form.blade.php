@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label>
        Danh mục
        <select name="category_id" required>
            @foreach($categoriesForForm as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Nhà cung cấp
        <select name="supplier_id">
            <option value="">Không có</option>
            @foreach($suppliersForForm as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Khu vực
        <select name="region_id">
            <option value="">Không có</option>
            @foreach($regionsForForm as $region)
                <option value="{{ $region->id }}" @selected((string) old('region_id', $product->region_id ?? '') === (string) $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        SKU
        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
    </label>
    <label>
        Đường dẫn tĩnh
        <input type="text" name="slug" value="{{ old('slug', $product->slug ?? '') }}">
    </label>
    <label class="full">
        Tên
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required>
    </label>
    <label class="full">
        Mô tả
        <textarea name="description">{{ old('description', $product->description ?? '') }}</textarea>
    </label>
    <label class="full">
        Mô tả ngắn
        <textarea name="short_description">{{ old('short_description', $product->short_description ?? '') }}</textarea>
    </label>
    <label>
        URL ảnh
        <input type="url" name="image_url" value="{{ old('image_url', $product->image_url ?? '') }}">
    </label>
    <label>
        Xuất xứ
        <input type="text" name="origin" value="{{ old('origin', $product->origin ?? '') }}">
    </label>
    <label>
        Khối lượng
        <input type="text" name="weight" value="{{ old('weight', $product->weight ?? '') }}">
    </label>
    <label>
        Hạn sử dụng
        <input type="text" name="shelf_life" value="{{ old('shelf_life', $product->shelf_life ?? '') }}">
    </label>
    <label>
        Giá bán
        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? 0) }}" required>
    </label>
    <label>
        Số lượng tồn
        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
    </label>
    <label class="full">
        Chứng nhận
        <input type="text" name="certifications[]" value="{{ old('certifications.0', $product->certifications[0] ?? '') }}" placeholder="Phiên bản SSR hiện tại chỉ gửi 1 chứng nhận mỗi lần">
    </label>
    <label class="full">
        Thư viện ảnh
        <input type="text" name="gallery[]" value="{{ old('gallery.0', $product->gallery[0] ?? '') }}" placeholder="Phiên bản SSR hiện tại chỉ gửi 1 URL ảnh mỗi lần">
    </label>
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $product->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $product->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $product->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $product->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.products.index') }}">Quay lại sản phẩm</a>
    </div>
</form>
