<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Access Request</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f5; color: #333; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { background-color: #ff3e1d; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .body-content { padding: 40px 30px; }
        .details-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .details-box p { margin: 5px 0; }
        .footer { background-color: #f8f9fa; color: #6c757d; text-align: center; padding: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Access Request</h1>
        </div>
        <div class="body-content">
            <p>Administrator,</p>
            <p>A new user has requested access to the 3DHub Data Portal. Here are the details:</p>
            
            <div class="details-box">
                <p><strong>Name:</strong> {{ $userName }}</p>
                <p><strong>Email:</strong> {{ $userEmail }}</p>
                @if($companyName)<p><strong>Company:</strong> {{ $companyName }}</p>@endif
                @if($reason)<p><strong>Reason:</strong> {{ $reason }}</p>@endif
            </div>

            <p>Please log in to the admin dashboard to review and approve or reject this request.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 3DHub Data Portal. Admin Notification.
        </div>
    </div>
</body>
</html>
