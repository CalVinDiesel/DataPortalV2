<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Public Sans', sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .header { background: #0a2540; color: #fff; padding: 25px 15px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #696cff; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
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
                            $logoUrl = 'https://dataportal.geovidia.my/' . $logoFile . '?v=' . $logoVer;
                        }
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 160px; margin-bottom: 10px; vertical-align: middle;">
            <h2 style="margin: 0;">New Contact Inquiry</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">From:</span> {{ $name }}
            </div>
            <div class="field">
                <span class="label">Email:</span> {{ $email }}
            </div>
            <div class="field">
                <span class="label">Message:</span><br>
                <div style="white-space: pre-wrap; background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 5px;">
                    {{ $messageBody }}
                </div>
            </div>
        </div>
        <div class="footer">
            This message was sent from the 3D Hub Data Portal Contact Form.
        </div>
    </div>
</body>
</html>
