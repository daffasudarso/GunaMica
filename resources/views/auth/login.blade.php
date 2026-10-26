<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guna Mica</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logogunamica.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background: #e7edf3;
            display: grid;
            place-items: center;
            padding: 18px;
        }
        .auth-shell {
            width: min(980px, 100%);
            min-height: 620px;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(18, 32, 56, 0.2);
            display: grid;
            grid-template-columns: 1.2fr 1fr;
        }
        .auth-visual {
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
            padding: 28px;
            color: #fff;
        }
        .auth-visual h2 {
            margin: 0 0 6px;
            font-size: 1.8rem;
        }
        .auth-visual p {
            margin: 0;
            color: rgba(255,255,255,.88);
            font-size: .95rem;
        }
        .auth-form-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 26px;
            background: rgba(245, 249, 255, 0.9);
        }
        .auth-form {
            width: min(360px, 100%);
            background: rgba(255,255,255,.85);
            border: 1px solid #d8e3f1;
            border-radius: 16px;
            padding: 24px;
        }
        .auth-form h1 {
            margin: 0 0 18px;
            text-align: center;
            font-size: 1.7rem;
            color: #1f2f46;
        }
        label {
            font-size: .86rem;
            color: #4c607b;
            display: block;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            border: 1px solid #cfd9e6;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-family: inherit;
        }
        button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px 12px;
            background: linear-gradient(135deg, #2d76ff, #1f5ed4);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .notice {
            margin-bottom: 12px;
            background: #ffe8ea;
            color: #991b1b;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: .85rem;
        }
        .meta {
            margin-top: 12px;
            text-align: center;
            font-size: .86rem;
            color: #3f4d61;
        }
        .meta a { color: #2d76ff; text-decoration: none; }
        @media (max-width: 900px) {
            .auth-shell {
                grid-template-columns: 1fr;
                min-height: 0;
            }
            .auth-visual {
                min-height: 170px;
            }
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-visual" id="loginVisual" data-bg="{{ asset('images/Bannerlog.png') }}">
        <div>
            <h2>Guna Mica</h2>
            <p>Masuk ke akun Anda untuk lanjut belanja, lacak pesanan, dan kelola profil.</p>
        </div>
    </div>

    <div class="auth-form-wrap">
        <form action="{{ route('login.submit') }}" method="post" class="auth-form">
            @csrf
            <h1>Login</h1>

            @if ($errors->any())
                <div class="notice">{{ $errors->first() }}</div>
            @endif

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit">Login</button>

            <div class="meta">
                Belum punya akun? <a href="{{ route('register') }}">Register</a>
            </div>
        </form>
    </div>
</div>
<script>
(() => {
    const visual = document.getElementById('loginVisual');
    if (!visual) return;

    const bg = visual.dataset.bg || '';
    visual.style.background = `linear-gradient(125deg, rgba(18,39,77,.5), rgba(19,95,196,.35)), url('${bg}') center/cover no-repeat`;
})();
</script>
</body>
</html>
