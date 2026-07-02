<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Inquiry - Admin Alert</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; line-height: 1.7; }
        .wrapper { max-width: 620px; margin: 40px auto; }
        .header { background: #0a2540; border-radius: 14px 14px 0 0; padding: 20px 40px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 8px 0 0; color: rgba(255,255,255,0.8); font-size: 13px; }
        .body { background: #ffffff; padding: 36px 40px; }
        .badge-id { display: inline-block; background: #eef2ff; color: #4338ca; border: 1.5px solid #c7d2fe; border-radius: 8px; padding: 8px 18px; font-size: 15px; font-weight: 700; margin: 10px 0 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 0 0 24px; }
        .info-table tr td { padding: 10px 14px; font-size: 14px; }
        .info-table tr td:first-child { color: #888; font-weight: 600; width: 36%; }
        .info-table tr:nth-child(odd) { background: #f8fafc; }
        .tag { display: inline-block; background: #ede9fe; color: #6d28d9; border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600; margin: 2px 3px 2px 0; }
        .cta-btn { display: block; width: fit-content; margin: 24px auto 0; background: linear-gradient(135deg, #3730a3, #6d28d9); color: #fff; text-decoration: none; padding: 13px 34px; border-radius: 10px; font-weight: 700; font-size: 15px; text-align: center; }
        .footer { background: #f8f9fa; border-radius: 0 0 14px 14px; padding: 20px 40px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            @php
                $logoUrl = env('MAIL_LOGO_URL');
                if (!$logoUrl) {
                    $logoUrl = asset('assets/img/front-pages/landing-page/3DHub%20logo1_glow_v3.png');
                    $requestHost = request()->getSchemeAndHttpHost();
                    if (str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost') || str_contains($logoUrl, 'dataportal_app')) {
                        if (!str_contains($requestHost, '127.0.0.1') && !str_contains($requestHost, 'localhost') && !str_contains($requestHost, 'dataportal_app')) {
                            $logoUrl = $requestHost . '/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v3.png';
                        } else {
                            $logoUrl = 'https://dataportal.geovidia.my/assets/img/front-pages/landing-page/3DHub%20logo1_glow_v3.png';
                        }
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 160px; margin-bottom: 10px; vertical-align: middle;">
            <h1>Ã°Å¸â€â€ New Inquiry Submitted</h1>
            <p>A client has submitted a new inquiry request</p>
        </div>
        <div class="body">
            <p>Hello Admin,</p>
            <p>A new inquiry request has been submitted and is waiting for your review.</p>

            <div style="text-align: center;">
                <div class="badge-id">{{ $inquiry->inquiry_id }}</div>
            </div>

            <table class="info-table">
                <tr>
                    <td>Client Email</td>
                    <td><strong>{{ $inquiry->user_email }}</strong></td>
                </tr>
                <tr>
                    <td>Client Name</td>
                    <td>{{ $inquiry->user->name ?? 'Ã¢â‚¬â€' }}</td>
                </tr>
                <tr>
                    <td>3D Model</td>
                    <td><strong>{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</strong></td>
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
                    <td>Date Submitted</td>
                    <td>{{ $inquiry->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            </table>

            <p>Please log in to the Admin Panel to review this inquiry, preview the area the client has selected on the 3D map, and send a formal quotation with pricing details.</p>

            <a href="{{ url('/admin/inquiries') }}" class="cta-btn">Open Admin Panel Ã¢â€ â€™</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal Ã¢â‚¬â€ Admin Notification
        </div>
    </div>
</body>
</html>
