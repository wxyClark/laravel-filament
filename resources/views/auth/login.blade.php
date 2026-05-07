<!DOCTYPE html>
<html>
<head>
    <title>登录</title>
</head>
<body>
    <h2>登录</h2>
    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label for="email">邮箱</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label for="password">密码</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div>
            <label>
                <input type="checkbox" name="remember"> 记住我
            </label>
        </div>
        <button type="submit">登录</button>
    </form>
    <a href="{{ route('register') }}">还没有账号？</a>
</body>
</html>
