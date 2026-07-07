<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f5;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0a2540;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .body-content {
            padding: 40px 30px;
        }
        .body-content p {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 40px 0;
        }
        .btn {
            display: inline-block;
            background-color: #696cff;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            background-color: #f8f9fa;
            color: #6c757d;
            text-align: center;
            padding: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $logoUrl = env('MAIL_LOGO_URL');
                if (!$logoUrl) {
                    $logoFile = 'assets/img/front-pages/landing-page/3DHub-logo-email.png';
                    $logoVer  = @filemtime(public_path($logoFile)) ?: time();
                    $logoUrl  = asset($logoFile) . '?v=' . $logoVer;
                    $requestHost = request()->getSchemeAndHttpHost();
                    if (str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost') || str_contains($logoUrl, 'dataportal_app')) {
                        if (!str_contains($requestHost, '127.0.0.1') && !str_contains($requestHost, 'localhost') && !str_contains($requestHost, 'dataportal_app')) {
                            $logoUrl = $requestHost . '/' . $logoFile . '?v=' . $logoVer;
                        } else {
                            $logoUrl = rtrim(config('app.url'), '/') . '/' . $logoFile . '?v=' . $logoVer;
                        }
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 160px; margin-bottom: 10px; vertical-align: middle; filter: brightness(2.5) contrast(1.2) drop-shadow(0 0 2px rgba(255,255,255,0.95)) drop-shadow(0 0 6px rgba(255,255,255,0.6));">
            <h1>Reset Your Password</h1>
        </div>
        <div class="body-content">
            <p>Hello {{ $name }},</p>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
            </div>

            <p>This password reset link will expire in 60 minutes.</p>
            
            <p>If you did not request a password reset, no further action is required.</p>
            
            <hr style="border: 0; border-top: 1px solid #eeeeee; margin: 30px 0;">
            
            <p style="font-size: 13px; color: #6c757d; line-height: 1.5;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
            <p style="word-wrap: break-word; font-size: 13px; color: #696cff; margin-bottom: 30px;">
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>

            <p>Regards,<br>The 3DHub Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.
        </div>
    </div>
</body>
</html>
