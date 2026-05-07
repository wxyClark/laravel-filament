<!DOCTYPE html>
<html>
<head>
    <title>注册</title>
</head>
<body>
    <h2>注册</h2>
    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <label for="name">姓名</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label for="email">邮箱</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label for="password">密码</label>
            <input id="password" type="password" name="password" required>
        </div>
        <div>
            <label for="password_confirmation">确认密码</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <button type="submit">注册</button>
    </form>
    <a href="{{ route('login') }}">已有账号？</a>
</body>
</html>
