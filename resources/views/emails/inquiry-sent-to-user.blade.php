<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Inquiry Quotation - {{ $inquiry->inquiry_id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; line-height: 1.7; }
        .wrapper { max-width: 640px; margin: 40px auto; }
        .header { background: #0a2540; border-radius: 14px 14px 0 0; padding: 36px 40px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 24px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { margin: 8px 0 0; color: rgba(255,255,255,0.9); font-size: 14px; }
        .body { background: #ffffff; padding: 40px; }
        .badge-id { display: inline-block; background: #f0fdf4; color: #059669; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 8px 18px; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; margin: 12px 0 20px; }
        .section-title { font-size: 13px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.8px; margin: 28px 0 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table tr td { padding: 10px 14px; font-size: 14px; }
        .info-table tr td:first-child { color: #888; font-weight: 600; width: 40%; }
        .info-table tr:nth-child(odd) { background: #fafafa; }
        .info-table tr:nth-child(even) { background: #ffffff; }
        .tag { display: inline-block; background: #ede9fe; color: #6d28d9; border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600; margin: 2px 3px 2px 0; }
        .price-box { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 2px solid #6ee7b7; border-radius: 12px; padding: 24px 28px; margin: 24px 0; text-align: center; }
        .price-box .amount { font-size: 38px; font-weight: 800; color: #059669; letter-spacing: -1px; }
        .price-box .currency { font-size: 20px; font-weight: 700; color: #059669; margin-right: 4px; }
        .price-box .label { font-size: 13px; color: #888; margin-top: 4px; }
        .bank-box { background: #fffbeb; border: 2px solid #fcd34d; border-radius: 12px; padding: 24px 28px; margin: 24px 0; }
        .bank-box h3 { margin: 0 0 16px; font-size: 16px; color: #92400e; display: flex; align-items: center; gap: 8px; }
        .bank-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #fde68a; font-size: 14px; }
        .bank-row:last-child { border-bottom: none; }
        .bank-row .bank-label { color: #92400e; font-weight: 600; }
        .bank-row .bank-value { font-weight: 700; color: #1a1a1a; text-align: right; }
        .deadline-box { background: #fff7ed; border-left: 4px solid #f97316; border-radius: 0 8px 8px 0; padding: 14px 20px; margin: 20px 0; font-size: 14px; color: #7c2d12; }
        .deadline-box strong { color: #c2410c; }
        .notes-box { background: #f8f9fa; border-left: 4px solid #696cff; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #555; }
        .steps-list { list-style: none; padding: 0; margin: 16px 0; }
        .steps-list li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
        .steps-list li:last-child { border-bottom: none; }
        .step-num { background: #059669; color: #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0; margin-top: 1px; }
        .cta-btn { display: block; width: fit-content; margin: 28px auto 0; background: linear-gradient(135deg, #059669, #0d9488); color: #fff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 15px; text-align: center; }
        .footer { background: #f8f9fa; border-radius: 0 0 14px 14px; padding: 24px 40px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            @php
                $logoUrl = env('MAIL_LOGO_URL');
                if (!$logoUrl) {
                    $logoUrl = asset('assets/img/front-pages/landing-page/3DHub logo1.png');
                    $requestHost = request()->getSchemeAndHttpHost();
                    if ((str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost')) && 
                        !str_contains($requestHost, '127.0.0.1') && !str_contains($requestHost, 'localhost')) {
                        $logoUrl = $requestHost . '/assets/img/front-pages/landing-page/3DHub logo1.png';
                    }
                }
            @endphp
            <img src="{{ $logoUrl }}" alt="3DHub Logo" style="height: 50px; margin-bottom: 15px; filter: drop-shadow(0 0 8px rgba(0, 224, 255, 0.75)) drop-shadow(0 0 3px rgba(0, 224, 255, 0.5)); -webkit-filter: drop-shadow(0 0 8px rgba(0, 224, 255, 0.75)) drop-shadow(0 0 3px rgba(0, 224, 255, 0.5)); vertical-align: middle;">
            <h1>💼 Your Quotation is Ready</h1>
            <p>We have processed your inquiry and issued a quotation</p>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $inquiry->user->name ?? $inquiry->user_email }}</strong>,</p>
            <p>We have reviewed your inquiry request and are pleased to present you with a formal quotation. Please see the attached PDF quotation for details including pricing, bank transfer information, and payment instructions.</p>

            <div style="text-align: center;">
                <div class="badge-id">📋 {{ $inquiry->inquiry_id }}</div>
            </div>

            <p class="section-title">📍 Order Details</p>
            <table class="info-table">
                <tr>
                    <td style="padding: 10px 14px; font-size: 14px; color: #888; font-weight: 600; width: 40%;">3D Model / Location</td>
                    <td style="padding: 10px 14px; font-size: 14px;"><strong>{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 10px 14px; font-size: 14px; color: #888; font-weight: 600;">Date Requested</td>
                    <td style="padding: 10px 14px; font-size: 14px;">{{ $inquiry->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 14px; font-size: 14px; color: #888; font-weight: 600;">Quotation Date</td>
                    <td style="padding: 10px 14px; font-size: 14px;">{{ $inquiry->quoted_at ? $inquiry->quoted_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 14px; font-size: 14px; color: #888; font-weight: 600;">Output Formats</td>
                    <td style="padding: 10px 14px; font-size: 14px;">
                        @if(is_array($inquiry->output_categories))
                            @foreach($inquiry->output_categories as $cat)
                                <span class="tag">{{ $cat }}</span>
                            @endforeach
                        @else
                            {{ $inquiry->output_categories }}
                        @endif
                    </td>
                </tr>
            </table>

            @if($inquiry->current_admin_note)
            <div class="notes-box">
                <strong>📝 Notes from Our Team:</strong><br>
                {{ $inquiry->current_admin_note }}
            </div>
            @endif

            <div style="background-color: #f0f7ff; border-left: 4px solid #0070f3; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; font-size: 13.5px; color: #0050b3; line-height: 1.6;">
                <strong style="text-transform: uppercase; font-size: 11px; letter-spacing: 0.8px; color: #0070f3; display: block; margin-bottom: 8px;">⚠️ Important 3D Model Pricing Notices:</strong>
                <ol style="margin: 0; padding-left: 18px;">
                    <li style="margin-bottom: 8px;">
                        <strong>Different Year/Capture Pricing:</strong> The same 3D model captured in different years will have different prices. A more recent capture is more expensive than older ones.
                    </li>
                    <li>
                        <strong>Custom/Larger Area Requests:</strong> You can request a 3D model area larger than the boundaries shown on the map. This custom service is more expensive because it requires deploying a drone to capture the area specifically for you.
                    </li>
                </ol>
            </div>

            <p class="section-title">📌 How to Proceed</p>
            <ul class="steps-list">
                <li>
                    <div class="step-num">1</div>
                    <div>Open the attached <strong>PDF quotation</strong> to view pricing and payment bank details.</div>
                </li>
                <li>
                    <div class="step-num">2</div>
                    <div>Follow the instructions inside the PDF to make your bank transfer.</div>
                </li>
                <li>
                    <div class="step-num">3</div>
                    <div>After transfer, go to the portal and upload your <strong>payment receipt / screenshot</strong>.</div>
                </li>
                <li>
                    <div class="step-num">4</div>
                    <div>Once ready, you will be notified to download your processed 3D model data from the portal.</div>
                </li>
            </ul>

            <a href="{{ url('/inquiry/my') }}" class="cta-btn">View My Inquiries</a>

            <p style="margin-top: 30px; color: #888; font-size: 13px;">If you have any questions about this quotation, please contact us at <a href="mailto:{{ config('support.email', env('SUPPORT_EMAIL', 'support@3dhub.com')) }}" style="color: #059669;">{{ config('support.email', env('SUPPORT_EMAIL', 'support@3dhub.com')) }}</a>, quoting your Inquiry ID <strong>{{ $inquiry->inquiry_id }}</strong>.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. All rights reserved.<br>
            This email was sent regarding Inquiry {{ $inquiry->inquiry_id }}.
        </div>
    </div>
</body>
</html>
