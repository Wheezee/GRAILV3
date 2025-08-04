<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GRAIL Login</title>
            <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            min-width: 100vw;
            background: #faf7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-container {
            background: #fff;
            border: 1px solid #f3caca;
            border-radius: 1.5vw;
            box-shadow: 0 2px 12px rgba(179,7,7,0.06);
            width: 90vw;
            max-width: 350px;
            min-width: 250px;
            padding: 4vw 2vw 4vw 2vw;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-title {
            color: #b70707;
            font-size: 2.2vw;
            min-font-size: 1.2rem;
            margin-bottom: 0.5vw;
            text-align: center;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .login-subtitle {
            color: #991b1b;
            font-size: 1.2vw;
            min-font-size: 1rem;
            text-align: center;
            margin-bottom: 2vw;
        }
        .form-label {
            display: block;
            font-size: 1vw;
            min-font-size: 0.97rem;
            color: #991b1b;
            margin-bottom: 0.5vw;
            font-weight: 500;
        }
        form {
            width: 100%;
        }
        .form-input {
            width: 90%;
            display: block;
            margin-left: auto;
            margin-right: auto;
            padding: 0.7vw 1vw;
            border: 1px solid #f3caca;
            border-radius: 0.7vw;
            background: #fff;
            font-size: 1vw;
            min-font-size: 1rem;
            margin-bottom: 1.2vw;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        .form-input:focus {
            border: 1.5px solid #d30707;
            outline: none;
        }
        .form-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #ffcccc;
            border-radius: 0.7vw;
            padding: 0.7vw 1vw;
            font-size: 1vw;
            min-font-size: 0.95rem;
            margin-bottom: 1.2vw;
            width: 100%;
            box-sizing: border-box;
        }
        .login-btn {
            width: 100%;
            background: #d30707;
            color: #fff;
            border: none;
            border-radius: 0.7vw;
            padding: 0.9vw 0;
            font-size: 1.1vw;
            min-font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            margin-top: 0.2vw;
        }
        .login-btn:hover, .login-btn:focus {
            background: #991b1b;
        }
        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5vw;
            font-size: 1vw;
            min-font-size: 0.97rem;
            color: #991b1b;
        }
        .login-link a {
            color: #d30707;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.18s;
        }
        .login-link a:hover {
            color: #991b1b;
        }
        
        .google-btn {
            width: 100%;
            background: #4285f4;
            color: #fff;
            border: none;
            border-radius: 0.7vw;
            padding: 0.9vw 0;
            font-size: 1.1vw;
            min-font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            margin-top: 1.2vw;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5vw;
        }
        
        .google-btn:hover {
            background: #3367d6;
        }
        
        .google-icon {
            width: 1.2vw;
            min-width: 18px;
            height: 1.2vw;
            min-height: 18px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5vw 0;
            color: #991b1b;
            font-size: 0.9vw;
            min-font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f3caca;
        }
        
        .divider::before {
            margin-right: 1vw;
        }
        
        .divider::after {
            margin-left: 1vw;
        }
        @media (max-width: 500px) {
            .login-container {
                max-width: 98vw;
                padding: 6vw 3vw;
            }
            .login-title, .login-subtitle, .form-label, .form-input, .form-error, .login-btn, .login-link {
                font-size: 1rem !important;
            }
        }
            </style>
</head>
<body>
    <div class="login-container">
        <div class="login-title">GRAIL</div>
        <div class="login-subtitle">Sign in to your account</div>
        @if(session('error'))
            <div class="form-error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}" style="width:100%">
            @csrf
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" required autofocus class="form-input" placeholder="Enter your email">

            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" required class="form-input" placeholder="Enter your password">

            <button type="submit" class="login-btn">Sign in</button>
        </form>
        
        <div class="divider">or</div>
        
        <a href="{{ route('google.login') }}" class="google-btn">
            <svg class="google-icon" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>
        
        <div class="login-link">
            Don't have an account?
            <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
    </body>
</html>
