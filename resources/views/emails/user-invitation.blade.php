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
            <h1>Welcome to the Data Portal</h1>
        </div>
        <div class="body-content">
            <p>Dear {{ $name }},</p>
            
            <p>Temadigital is pleased to announce the launch of our new beta product, 3D Hub, and we would like to invite you to be among a select group of valued participants to experience it firsthand.</p>
            
            <p>With 3D Hub, you can transform your drone aerial images into:</p>
            <ul style="margin-top: 0; margin-bottom: 20px; padding-left: 20px; line-height: 1.6;">
                <li>3D mesh models</li>
                <li>Basemaps (true orthophotos)</li>
                <li>Digital surface models (DSM)</li>
            </ul>
            
            <p>These outputs can be processed within hours and will be shared with you for your own use and evaluation.</p>
            
            <p><strong>How to Participate:</strong><br>To begin, please complete your account setup by clicking the button below.</p>
            
            <div class="btn-wrapper">
                <a href="{{ $setupUrl }}" class="btn">Complete Account Setup</a>
            </div>

            <p>Or copy and paste this link into your browser:</p>
            <p style="word-wrap: break-word; font-size: 13px; color: #696cff;">
                <a href="{{ $setupUrl }}">{{ $setupUrl }}</a>
            </p>

            <hr style="border: 0; border-top: 1px solid #eeeeee; margin: 30px 0;">
            
            <h3 style="color: #32325d; margin-bottom: 15px;">Quick Guide: Getting Started with 3D Hub</h3>
            <p style="margin-bottom: 20px;">Once you have completed your setup and logged in, you can take advantage of the portal's core functions:</p>
            
            <table cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td style="vertical-align: top; width: 40px; padding-top: 2px;">
                        <span style="display: inline-block; background-color: #eef2ff; color: #696cff; font-weight: bold; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 14px;">1</span>
                    </td>
                    <td>
                        <strong style="color: #32325d; font-size: 15px;">Request Custom 3D Data:</strong>
                        <p style="margin: 4px 0 12px 0; color: #4b5563; font-size: 14px; line-height: 1.5;">Go to <strong>"New Inquiry"</strong> and draw an area on the map to request custom 3D formats prepared by our team.</p>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 40px; padding-top: 2px;">
                        <span style="display: inline-block; background-color: #eef2ff; color: #696cff; font-weight: bold; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 14px;">2</span>
                    </td>
                    <td>
                        <strong style="color: #32325d; font-size: 15px;">Upload Your Drone Photos:</strong>
                        <p style="margin: 4px 0 12px 0; color: #4b5563; font-size: 14px; line-height: 1.5;">Create a project in your dashboard. You can paste a shared Google Drive/OneDrive folder link, or upload raw images directly via high-speed SFTP.</p>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 40px; padding-top: 2px;">
                        <span style="display: inline-block; background-color: #eef2ff; color: #696cff; font-weight: bold; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 14px;">3</span>
                    </td>
                    <td>
                        <strong style="color: #32325d; font-size: 15px;">Manage Free Storage:</strong>
                        <p style="margin: 4px 0 12px 0; color: #4b5563; font-size: 14px; line-height: 1.5;">You have <strong>{{ \App\Models\ClientUpload::getStorageLimitBytes() / (1024 * 1024 * 1024) }} GB of free storage</strong> to host your drone datasets. Track and manage your usage directly from your user dashboard.</p>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; width: 40px; padding-top: 2px;">
                        <span style="display: inline-block; background-color: #eef2ff; color: #696cff; font-weight: bold; width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-size: 14px;">4</span>
                    </td>
                    <td>
                        <strong style="color: #32325d; font-size: 15px;">Explore Existing 3D Models:</strong>
                        <p style="margin: 4px 0 12px 0; color: #4b5563; font-size: 14px; line-height: 1.5;">Visit the <strong>Overview Map</strong> or homepage showcases to interactively view and inspect pre-existing 3D models directly in your browser.</p>
                    </td>
                </tr>
            </table>

            <hr style="border: 0; border-top: 1px solid #eeeeee; margin: 30px 0;">

            <p>This invitation link is valid for the next 1 week.</p>
            <p>Thank you,<br>The 3DHub Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.
        </div>
    </div>
</body>
</html>
