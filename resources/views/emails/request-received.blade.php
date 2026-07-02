<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Received</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #333; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { background-color: #0a2540; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body-content { padding: 40px 30px; }
        .footer { background-color: #f8f9fa; color: #6c757d; text-align: center; padding: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $logoUrl = env('MAIL_LOGO_URL');
                if (!$logoUrl) {
                    $logoUrl = asset('assets/img/front-pages/landing-page/3DHub%20logo1_glow_v6.png');
                    $requestHost = request()->getSchemeAndHttpHost();
                    if (str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost') || str_contains($logoUrl, 'dataportal_app')) {
                        if (!str_contains($requestHost, '127.0.0.1') && !str_contains($requestHost, 'localhost') && !str_contains($requestHost, 'dataportal_app')) {
                            $logoUrl = $requestHost . '/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v6.png';
                        } else {
                            $logoUrl = 'https://dataportal.geovidia.my/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v6.png';
                        }
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 160px; margin-bottom: 10px; vertical-align: middle;">
            <h1 style="margin: 0;">Request Received</h1>
        </div>
        <div class="body-content">
            <p>Hello {{ $name }},</p>
            <p>Thank you for your interest in the 3DHub Data Portal. We have successfully received your request for access.</p>
            <p>Our administration team is currently reviewing your application. You will receive another email from us once your request has been reviewed and approved.</p>
            <p>Please note that this process may take up to 24-48 hours.</p>
            <p>Thank you for your patience,<br>The 3DHub Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.
        </div>
    </div>
</body>
</html>
