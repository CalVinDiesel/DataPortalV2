<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inquiry Received</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; line-height: 1.7; }
        .wrapper { max-width: 620px; margin: 40px auto; }
        .header { background: #0a2540; border-radius: 14px 14px 0 0; padding: 20px 40px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { margin: 8px 0 0; color: rgba(255,255,255,0.85); font-size: 14px; }
        .body { background: #ffffff; padding: 40px; }
        .badge-id { display: inline-block; background: #f0f0ff; color: #696cff; border: 1.5px solid #d0d0ff; border-radius: 8px; padding: 8px 18px; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; margin: 16px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        .info-table tr td { padding: 10px 14px; font-size: 14px; }
        .info-table tr td:first-child { color: #888; font-weight: 600; width: 38%; }
        .info-table tr:nth-child(odd) { background: #fafafa; }
        .info-table tr:nth-child(even) { background: #ffffff; }
        .tag { display: inline-block; background: #ede9fe; color: #6d28d9; border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600; margin: 2px 3px 2px 0; }
        .status-badge { display: inline-block; background: #fffbeb; color: #d97706; border: 1.5px solid #fcd34d; border-radius: 20px; padding: 5px 16px; font-size: 13px; font-weight: 700; }
        .cta-btn { display: block; width: fit-content; margin: 28px auto 0; background: linear-gradient(135deg, #696cff, #9155fd); color: #fff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 15px; text-align: center; }
        .note-box { background: #f8f9fa; border-left: 4px solid #696cff; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 28px 0; font-size: 14px; color: #555; }
        .footer { background: #f8f9fa; border-radius: 0 0 14px 14px; padding: 24px 40px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            @php
                $logoUrl = env('MAIL_LOGO_URL');
                if (!$logoUrl) {
                    $logoUrl = asset('assets/img/front-pages/landing-page/3DHub%20logo1_glow_v4.png');
                    $requestHost = request()->getSchemeAndHttpHost();
                    if (str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost') || str_contains($logoUrl, 'dataportal_app')) {
                        if (!str_contains($requestHost, '127.0.0.1') && !str_contains($requestHost, 'localhost') && !str_contains($requestHost, 'dataportal_app')) {
                            $logoUrl = $requestHost . '/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v4.png';
                        } else {
                            $logoUrl = 'https://dataportal.geovidia.my/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v4.png';
                        }
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 160px; margin-bottom: 10px; vertical-align: middle;">
            <h1>ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ Inquiry Request Received</h1>
            <p>We have received your inquiry request</p>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $inquiry->user->name ?? $inquiry->user_email }}</strong>,</p>
            <p>Thank you for submitting your inquiry request to <strong>3DHub Data Portal</strong>. We have successfully received your request and our team will review it shortly.</p>

            <div style="text-align: center;">
                <div class="badge-id">ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã¢â‚¬Â¹ {{ $inquiry->inquiry_id }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td>3D Model</td>
                    <td><strong>{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</strong></td>
                </tr>
                <tr>
                    <td>Date Submitted</td>
                    <td>{{ $inquiry->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                <tr>
                    <td>Output Formats</td>
                    <td>
                        @if(is_array($inquiry->output_categories))
                            @foreach($inquiry->output_categories as $cat)
                                <span class="tag">{{ $cat }}</span>
                            @endforeach
                        @else
                            {{ $inquiry->output_categories }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td><span class="status-badge">ÃƒÂ¢Ã‚ÂÃ‚Â³ Pending Review</span></td>
                </tr>
            </table>

            <div class="note-box">
                <strong>What happens next?</strong><br>
                Our team will review your request and calculate a quotation based on the area you have selected and the output formats requested. You will receive another email with the quotation details, including pricing and payment instructions.
            </div>

            <a href="{{ url('/inquiry/my') }}" class="cta-btn">View My Inquiries</a>

            <p style="margin-top: 30px; color: #888; font-size: 13px;">If you have any questions, feel free to reach out to us at <a href="mailto:{{ config('support.email', env('SUPPORT_EMAIL', 'support@3dhub.com')) }}" style="color: #696cff;">{{ config('support.email', env('SUPPORT_EMAIL', 'support@3dhub.com')) }}</a>.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.
        </div>
    </div>
</body>
</html>
