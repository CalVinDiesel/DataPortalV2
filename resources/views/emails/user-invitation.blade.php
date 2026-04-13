<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>You're Invited!</title>
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
            background-color: #696cff;
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
            <h1>Welcome to the Data Portal</h1>
        </div>
        <div class="body-content">
            <p>Hello {{ $name }},</p>
            <p>Great news! Your request for access has been approved by the administrator. You are invited to join the exclusive Data Portal.</p>
            <p>Please click the button below to verify your email, set up your account contact information, and choose how you would like to sign in.</p>
            
            <div class="btn-wrapper">
                <a href="{{ $setupUrl }}" class="btn">Complete Account Setup</a>
            </div>

            <p>Or copy and paste this link into your browser:</p>
            <p style="word-wrap: break-word; font-size: 13px; color: #696cff;">
                <a href="{{ $setupUrl }}">{{ $setupUrl }}</a>
            </p>

            <p>This invitation link is valid for the next 48 hours.</p>
            <p>Thank you,<br>The 3DHub Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.
        </div>
    </div>
</body>
</html>
