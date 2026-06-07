<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Trang quản trị')</title>.
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css"
        rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css" rel="stylesheet">

    <script src="https://unpkg.com/filepond-plugin-file-poster/dist/filepond-plugin-file-poster.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <style>
        .filepond--item {
            width: calc(25% - 0.5em);
        }

        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --text: #122033;
            --muted: #5e6d81;
            --line: #d8dfeb;
            --brand: #14532d;
            --brand-soft: #dcfce7;
            --danger: #991b1b;
            --danger-soft: #fee2e2;
            --warning: #92400e;
            --warning-soft: #fef3c7;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #eef3ff 0%, var(--bg) 45%, #eef6f0 100%);
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            padding: 24px 18px;
            background: rgba(9, 30, 66, 0.96);
            color: #f8fafc;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand {
            margin-bottom: 24px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
        }

        .brand h1 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .brand p,
        .nav-section-label,
        .meta {
            color: rgba(241, 245, 249, 0.72);
        }

        .nav-section {
            margin-bottom: 22px;
        }

        .nav-section-label {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0 0 10px;
        }

        .nav-link {
            display: block;
            padding: 12px 14px;
            border-radius: 12px;
            text-decoration: none;
            margin-bottom: 6px;
            background: transparent;
            transition: background 0.18s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.14);
        }

        .main {
            padding: 28px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .toolbar h2 {
            margin: 0 0 6px;
            font-size: 30px;
        }

        .toolbar p {
            margin: 0;
            color: var(--muted);
        }

        .toolbar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .card {
            background: var(--surface);
            border: 1px solid rgba(216, 223, 235, 0.9);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 12px;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .grid.cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid.cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metric {
            padding: 18px;
            border-radius: 16px;
            background: var(--surface-alt);
            border: 1px solid var(--line);
        }

        .metric.card-link {
            display: block;
            color: inherit;
            text-decoration: none;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .metric.card-link:hover {
            transform: translateY(-1px);
            border-color: rgba(202, 138, 4, 0.35);
            box-shadow: var(--shadow);
        }

        .metric.card-link:focus-visible {
            outline: 2px solid var(--brand);
            outline-offset: 2px;
        }

        .metric .value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 8px;
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: center;
        }

        .row.between {
            justify-content: space-between;
        }

        .filters,
        form.inline-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 14px;
            color: var(--muted);
            min-width: 140px;
        }

        input,
        select,
        textarea,
        button {
            font: inherit;
        }

        input,
        select,
        textarea {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: 11px 12px;
            background: #fff;
            color: var(--text);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .btn {
            border: none;
            border-radius: 12px;
            padding: 11px 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-warning {
            background: #f59e0b;
            color: #0f172a;
        }

        .btn-link {
            background: transparent;
            color: var(--brand);
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid #e8edf6;
            vertical-align: top;
        }

        th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            background: #e2e8f0;
        }

        .badge.success {
            background: var(--brand-soft);
            color: var(--brand);
        }

        .badge.danger {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .badge.warning {
            background: var(--warning-soft);
            color: var(--warning);
        }

        .notice {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .notice.success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: var(--brand);
        }

        .notice.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        .muted {
            color: var(--muted);
        }

        .small {
            font-size: 13px;
        }

        .empty {
            padding: 18px;
            border-radius: 14px;
            background: var(--surface-alt);
            color: var(--muted);
            border: 1px dashed var(--line);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        @media (max-width: 1120px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
            }
        }

        @media (max-width: 760px) {
            .main {
                padding: 16px;
            }

            .toolbar {
                flex-direction: column;
            }

            .grid.cols-2,
            .grid.cols-3,
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <h1>Quản lý</h1>
                <div class="meta small" style="margin-top: 14px;">
                    <div>Xin chào {{ $adminUser->full_name }}!</div>
                    <div>Quản trị viên</div>
                </div>
            </div>

            @foreach ($adminNavSections as $section => $modules)
                <div class="nav-section">
                    <p class="nav-section-label">{{ $adminNavLabels[$section] ?? $section }}</p>
                    @foreach ($modules as $module)
                        @php($active = str_starts_with(url()->current(), $module['url']))
                        <a href="{{ $module['url'] }}" class="nav-link {{ $active ? 'active' : '' }}">
                            {{ $module['label'] }}
                        </a>
                    @endforeach
                </div>
            @endforeach

            <form action="{{ route('admin-web.logout') }}" method="POST" style="margin-top: 24px;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Đăng xuất</button>
            </form>
        </aside>

        <main class="main">
            @include('admin-web.partials.alerts')
            @yield('content')
        </main>
    </div>
    <script>
        function closestAdminControl(target, selector) {
            if (!(target instanceof Element)) {
                return null;
            }

            return target.closest(selector);
        }

        document.addEventListener("click", (event) => {
            const confirmButton = closestAdminControl(event.target, "[data-confirm]");

            if (confirmButton && !window.confirm(confirmButton.getAttribute("data-confirm"))) {
                event.preventDefault();
            }
        });

        document.addEventListener("change", async (event) => {
            const nameOnlySelect = closestAdminControl(event.target, "select[data-location-name-target]");

            if (nameOnlySelect) {
                const form = nameOnlySelect.closest("form");
                const nameTarget = nameOnlySelect.getAttribute("data-location-name-target");
                const input = nameTarget ? form?.querySelector(`[name="${nameTarget}"]`) : null;

                if (input) {
                    input.value = nameOnlySelect.selectedOptions[0]?.textContent?.trim() ?? "";
                }
            }

            const select = closestAdminControl(event.target, "select[data-location-level]");

            if (!select) {
                return;
            }

            const level = select.getAttribute("data-location-level");
            const form = select.closest("form");
            const nextSelector = select.getAttribute("data-location-next");

            if (!nextSelector || !select.value) {
                return;
            }

            const next = form?.querySelector(nextSelector);

            if (!(next instanceof HTMLSelectElement)) {
                return;
            }

            const endpoint = level === "province"
                ? `/api/shipping/ghn/districts?province_id=${encodeURIComponent(select.value)}`
                : `/api/shipping/ghn/wards?district_id=${encodeURIComponent(select.value)}`;

            next.innerHTML = '<option value="">Dang tai...</option>';

            try {
                const response = await fetch(endpoint, { headers: { Accept: "application/json" } });
                const payload = await response.json();
                const items = payload.data ?? [];
                const valueKey = level === "province" ? "DistrictID" : "WardCode";
                const labelKey = level === "province" ? "DistrictName" : "WardName";

                next.innerHTML = '<option value="">Chon</option>' + items.map((item) => {
                    const value = item[valueKey] ?? item.id ?? "";
                    const label = item[labelKey] ?? item.name ?? value;

                    return `<option value="${String(value).replace(/"/g, "&quot;")}">${label}</option>`;
                }).join("");
            } catch {
                next.innerHTML = '<option value="">Khong tai duoc du lieu</option>';
            }
        });
    </script>
    @stack('scripts')
</body>


</html>
