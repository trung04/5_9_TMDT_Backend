<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Không có quyền truy cập</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(180deg, #fef2f2, #eff6ff);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #122033;
        }
        .panel {
            width: min(560px, calc(100vw - 32px));
            padding: 32px;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 24px;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
        }
        h1 { margin: 0 0 10px; }
        p { color: #5e6d81; }
        a {
            display: inline-flex;
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 14px;
            text-decoration: none;
            background: #14532d;
            color: #fff;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>403 Không có quyền truy cập</h1>
        <p>{{ $exception->getMessage() ?: 'Bạn không có quyền truy cập trang này.' }}</p>
        <a href="{{ route('admin-web.login') }}">Quay lại đăng nhập quản trị</a>
    </div>
</body>
</html>
