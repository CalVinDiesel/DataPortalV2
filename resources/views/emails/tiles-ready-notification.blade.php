<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your 3D Model Tiles Are Ready – {{ $inquiry->inquiry_id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 0; line-height: 1.7; }
        .wrapper { max-width: 640px; margin: 40px auto; }

        /* Header — green gradient matching the download button */
        .header { background: #0a2540; border-radius: 14px 14px 0 0; padding: 36px 40px; text-align: center; }
        .header .icon { font-size: 48px; line-height: 1; margin-bottom: 12px; display: block; }
        .header h1 { margin: 0; color: #fff; font-size: 26px; font-weight: 800; letter-spacing: -0.4px; }
        .header p { margin: 8px 0 0; color: rgba(255,255,255,0.9); font-size: 14px; }

        .body { background: #ffffff; padding: 40px; }

        /* Inquiry ID badge */
        .badge-id { display: inline-block; background: #f0fdf4; color: #059669; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 8px 18px; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; margin: 12px 0 24px; font-family: 'Courier New', monospace; }

        .section-title { font-size: 13px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.8px; margin: 28px 0 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table tr td { padding: 10px 14px; font-size: 14px; }
        .info-table tr td:first-child { color: #888; font-weight: 600; width: 40%; }
        .info-table tr:nth-child(odd) { background: #fafafa; }
        .info-table tr:nth-child(even) { background: #ffffff; }
        .tag { display: inline-block; background: #ede9fe; color: #6d28d9; border-radius: 20px; padding: 3px 12px; font-size: 12px; font-weight: 600; margin: 2px 3px 2px 0; }

        /* Ready highlight box */
        .ready-box { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 2px solid #6ee7b7; border-radius: 12px; padding: 28px 32px; margin: 28px 0; text-align: center; }
        .ready-box .check { font-size: 44px; line-height: 1; margin-bottom: 10px; display: block; }
        .ready-box h2 { margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #065f46; }
        .ready-box p { margin: 0; font-size: 14px; color: #047857; }

        /* Steps */
        .steps-list { list-style: none; padding: 0; margin: 16px 0; }
        .steps-list li { display: table; width: 100%; padding: 10px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
        .steps-list li:last-child { border-bottom: none; }
        .step-num-wrap { display: table-cell; width: 38px; vertical-align: top; padding-top: 1px; }
        .step-num { background: #059669; color: #fff; border-radius: 50%; width: 26px; height: 26px; display: inline-block; text-align: center; line-height: 26px; font-weight: 700; font-size: 13px; }
        .step-text { display: table-cell; vertical-align: middle; font-size: 14px; }

        /* CTA Button */
        .cta-wrap { text-align: center; margin: 32px 0 16px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #059669, #0d9488); color: #fff !important; text-decoration: none; padding: 15px 40px; border-radius: 10px; font-weight: 700; font-size: 15px; letter-spacing: 0.2px; }

        /* Notice box */
        .notice-box { background: #f8f9fa; border-left: 4px solid #696cff; border-radius: 0 8px 8px 0; padding: 14px 20px; margin: 24px 0; font-size: 13px; color: #555; }
        .notice-box strong { color: #4338ca; }

        /* Footer */
        .footer { background: #f8f9fa; border-radius: 0 0 14px 14px; padding: 24px 40px; text-align: center; font-size: 12px; color: #999; }
        .footer a { color: #059669; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">

        {{-- Header --}}
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
            <h1>Your 3D Model Tiles Are Ready!</h1>
            <p>Your inquiry order has been fulfilled — download your tiles now</p>
        </div>

        <div class="body">
            <p>Hello <strong>{{ $inquiry->user->name ?? $inquiry->user_email }}</strong>,</p>
            <p>Great news! Our team has finished processing your order and your <strong>3D model tile files</strong> are now ready to download from the 3DHub Data Portal.</p>

            {{-- Inquiry ID badge --}}
            <div style="text-align: center;">
                <div class="badge-id">📋 {{ $inquiry->inquiry_id }}</div>
            </div>

            {{-- Ready highlight box --}}
            <div class="ready-box">
                <span class="check">✅</span>
                <h2>Files Ready for Download</h2>
                <p>Log in to the portal and visit <strong>My Inquiries</strong> to download your 3D model tiles.</p>
                @if($inquiry->delivered_at)
                    <p style="font-size:12.5px; color:#6b7280; margin-top: 8px;">Made available on {{ $inquiry->delivered_at->format('d M Y, h:i A') }}</p>
                @endif
            </div>

            {{-- Order summary --}}
            <p class="section-title">📍 Order Summary</p>
            <table class="info-table">
                <tr>
                    <td>Inquiry ID</td>
                    <td><strong style="font-family: 'Courier New', monospace; color: #4f46e5;">{{ $inquiry->inquiry_id }}</strong></td>
                </tr>
                <tr>
                    <td>3D Model / Location</td>
                    <td><strong>{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</strong></td>
                </tr>
                <tr>
                    <td>Output Formats</td>
                    <td>
                        @foreach($inquiry->output_categories ?? [] as $cat)
                            <span class="tag">{{ $cat }}</span>
                        @endforeach
                    </td>
                </tr>
                @if($inquiry->quoted_price)
                <tr>
                    <td>Amount Paid</td>
                    <td><strong style="color: #059669;">RM {{ number_format($inquiry->quoted_price, 2) }}</strong></td>
                </tr>
                @endif
                <tr>
                    <td>Order Submitted</td>
                    <td>{{ $inquiry->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            </table>

            {{-- How to download steps --}}
            <p class="section-title">🚀 How to Download</p>
            <ul class="steps-list">
                <li>
                    <div class="step-num-wrap"><span class="step-num">1</span></div>
                    <div class="step-text">Log in to the <strong>3DHub Data Portal</strong> at <a href="{{ url('/') }}" style="color:#059669;">{{ url('/') }}</a></div>
                </li>
                <li>
                    <div class="step-num-wrap"><span class="step-num">2</span></div>
                    <div class="step-text">Navigate to <strong>Inquiry → My Inquiry</strong> in the top navigation bar</div>
                </li>
                <li>
                    <div class="step-num-wrap"><span class="step-num">3</span></div>
                    <div class="step-text">Find your order <strong>{{ $inquiry->inquiry_id }}</strong> and click to expand it</div>
                </li>
                <li>
                    <div class="step-num-wrap"><span class="step-num">4</span></div>
                    <div class="step-text">Click the <strong>⬇ Download 3D Model Tiles</strong> green button to start the download</div>
                </li>
            </ul>

            {{-- CTA Button --}}
            <div class="cta-wrap">
                <a href="{{ url('/inquiry/my') }}" class="cta-btn">⬇ Go to My Inquiries</a>
            </div>

            {{-- Notice box --}}
            <div class="notice-box">
                <strong>📌 Important:</strong> Please keep a backup copy of the downloaded tiles after saving them. If you experience any issues with the download, contact our support team and we will assist you promptly.
            </div>

            <p>Thank you for choosing <strong>3DHub</strong>. We hope the 3D model data meets your needs perfectly!</p>
            <p style="margin-top: 24px;">Best regards,<br><strong>The 3DHub Team</strong></p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>This email was sent to <strong>{{ $inquiry->user_email }}</strong> because you placed an inquiry order on 3DHub Data Portal.</p>
            <p style="margin-top: 8px;">
                <a href="{{ url('/inquiry/my') }}">My Inquiries</a> &nbsp;·&nbsp;
                <a href="{{ url('/#landingContact') }}">Contact Support</a>
            </p>
            <p style="margin-top: 8px; color: #bbb;">© {{ date('Y') }} 3DHub Data Portal. All rights reserved.</p>
        </div>

    </div>
</body>
</html>
