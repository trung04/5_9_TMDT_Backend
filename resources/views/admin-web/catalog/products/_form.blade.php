@php
    $labels = \App\Support\AdminWebLabel::class;
    $isEdit = $formMethod !== 'POST';
    $galleryPaths = collect(old('existing_gallery', explode('|', (string) ($product->gallery ?? ''))))
        ->map(fn ($path) => trim((string) $path))
        ->filter()
        ->values();
    $galleryFiles = $galleryPaths
        ->map(fn ($path) => [
            'source' => $path,
            'options' => [
                'type' => 'local',
                'metadata' => [
                    'poster' => $product->displayImageUrl($path),
                ],
            ],
        ])
        ->values();
    $thumbnailFiles = $isEdit && $product->image_url
        ? [[
            'source' => $product->image_url,
            'options' => [
                'type' => 'local',
                'metadata' => [
                    'poster' => $product->displayImageUrl(),
                ],
            ],
        ]]
        : [];
@endphp

<form method="POST" action="{{ $formAction }}" class="form-grid" enctype="multipart/form-data">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <label>
        Ten
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required>
    </label>
    <label>
        SKU
        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
    </label>
    <label class="full">
        Mo ta
        <textarea name="description">{{ old('description', $product->description ?? '') }}</textarea>
    </label>
    <label>
        Danh muc
        <select name="category_id" required>
            @foreach ($categoriesForForm as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Nha cung cap
        <select name="supplier_id">
            <option value="">Khong co</option>
            @foreach ($suppliersForForm as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Khu vuc
        <select name="region_id">
            <option value="">Khong co</option>
            @foreach ($regionsForForm as $region)
                <option value="{{ $region->id }}" @selected((string) old('region_id', $product->region_id ?? '') === (string) $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Anh dai dien (Thumbnail)
        <input type="file" class="image_url" name="image_url" accept="image/jpeg,image/png,image/webp" @required(! $isEdit)>
    </label>
    <label class="full">
        Anh san pham
        <input type="file" class="gallery" name="images[]" multiple accept="image/jpeg,image/png,image/webp" @required(! $isEdit)>
    </label>

    <div data-existing-gallery-inputs>
        @foreach ($galleryPaths as $path)
            <input type="hidden" name="existing_gallery[]" value="{{ $path }}" data-existing-gallery="{{ $path }}">
        @endforeach
    </div>

    <label>
        Gia ban
        <input min="0" type="number" step="0.01" name="sale_price"
            value="{{ old('sale_price', $product->sale_price ?? 0) }}" required>
    </label>
    <label>
        So luong ton
        <input min="1" type="number" name="stock_quantity"
            value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
    </label>
    <label>
        Dang hoat dong
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $product->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(!(bool) old('is_active', $product->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Da xoa
        <select name="is_deleted">
            <option value="0" @selected(!(bool) old('is_deleted', $product->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $product->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.products.index') }}">Quay lai san pham</a>
    </div>
</form>

<script>
    FilePond.registerPlugin(FilePondPluginImagePreview);

    const resolveImageSource = (source) => {
        if (!source) {
            return source;
        }

        return source.startsWith('http://') || source.startsWith('https://') || source.startsWith('/')
            ? source
            : `/storage/${source}`;
    };

    const filePondLoad = (source, load, error, progress, abort) => {
        fetch(resolveImageSource(source))
            .then((response) => response.blob())
            .then(load)
            .catch(() => error('Khong load duoc anh'));

        return {
            abort: () => abort(),
        };
    };

    FilePond.create(document.querySelector('.image_url'), {
        storeAsFile: true,
        files: @json($thumbnailFiles),
        server: {
            load: filePondLoad,
        },
    });

    const existingGalleryInputs = document.querySelector('[data-existing-gallery-inputs]');
    const galleryPond = FilePond.create(document.querySelector('.gallery'), {
        allowMultiple: true,
        storeAsFile: true,
        files: @json($galleryFiles),
        server: {
            load: filePondLoad,
        },
    });

    galleryPond.on('removefile', (error, file) => {
        if (error || !file?.source || !existingGalleryInputs) {
            return;
        }

        existingGalleryInputs
            .querySelectorAll('input[name="existing_gallery[]"]')
            .forEach((input) => {
                if (input.value === file.source) {
                    input.remove();
                }
            });
    });
</script>
