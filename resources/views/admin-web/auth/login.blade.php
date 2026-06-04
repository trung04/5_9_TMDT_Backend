<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập quản trị</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at top, #dcfce7, #eff6ff 45%, #e2e8f0 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #10213a;
        }
        .panel {
            width: min(440px, calc(100vw - 32px));
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
        }
        h1 { margin: 0 0 10px; }
        p { margin: 0 0 22px; color: #5b6b81; }
        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
            color: #475569;
        }
        input {
            border-radius: 14px;
            border: 1px solid #d8dfeb;
            padding: 12px 14px;
            font: inherit;
        }
        button {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 13px 16px;
            background: #14532d;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
        }
        .notice {
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Đăng nhập quản trị</h1>
        <p>Dùng tài khoản quản trị để truy cập khu vực quản lý dựng bằng Laravel.</p>

        @if(session('status'))
            <div class="notice" style="border-color: #bbf7d0; background: #f0fdf4; color: #166534;">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="notice">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin-web.login.store') }}">
            @csrf
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>
                Mật khẩu
                <input type="password" name="password" required>
            </label>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
