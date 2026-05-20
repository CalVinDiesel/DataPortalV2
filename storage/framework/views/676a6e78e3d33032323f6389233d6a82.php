<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Public Sans', sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
        .header { background: #696cff; color: #fff; padding: 15px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #696cff; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Inquiry</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">From:</span> <?php echo e($name); ?>

            </div>
            <div class="field">
                <span class="label">Email:</span> <?php echo e($email); ?>

            </div>
            <div class="field">
                <span class="label">Message:</span><br>
                <div style="white-space: pre-wrap; background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 5px;">
                    <?php echo e($messageBody); ?>

                </div>
            </div>
        </div>
        <div class="footer">
            This message was sent from the 3D Hub Data Portal Contact Form.
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/emails/contact-inquiry.blade.php ENDPATH**/ ?>