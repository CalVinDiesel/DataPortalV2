<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Received</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #333; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { background-color: #696cff; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body-content { padding: 40px 30px; }
        .footer { background-color: #f8f9fa; color: #6c757d; text-align: center; padding: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Request Received</h1>
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
