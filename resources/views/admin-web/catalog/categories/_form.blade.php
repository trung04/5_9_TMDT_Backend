@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label class="full">
        Tên
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required>
    </label>
    <label class="full">
        Mô tả
        <textarea name="description">{{ old('description', $category->description ?? '') }}</textarea>
    </label>
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $category->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $category->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $category->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $category->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.categories.index') }}">Quay lại danh mục</a>
    </div>
</form>
