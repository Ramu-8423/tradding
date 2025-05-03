<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #4b6cb7, #182848);
            height: 100vh;
            margin: 0;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            z-index: 2;
            position: relative;
        }

        .login-box h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-label {
            margin-top: 10px;
            font-weight: 500;
        }

        .btn-primary {
            width: 100%;
            margin-top: 20px;
        }

        /* Dot styling */
        .dot {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0.8;
            animation: fall 5s linear infinite;
        }

        @keyframes fall {
            0% {
                transform: translateY(-50px) rotate(0deg);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .dot.anticlockwise {
            animation: fall-reverse 5s linear infinite;
        }

        @keyframes fall-reverse {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(-360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>

<!-- DYNAMIC DOTS -->


<!-- LOGIN FORM -->
<div class="login-box">
		
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <h3>Login Here</h3>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
		@if(session('msg'))
			<div style="color: red; font-weight: bold;">
				{{ session('msg') }}
			</div>
		@endif
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" placeholder="Username" id="username" class="form-control" required>

        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" placeholder="Password" id="password" class="form-control" required>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

</body>
</html>
