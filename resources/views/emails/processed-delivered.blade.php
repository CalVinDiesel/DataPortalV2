<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 12px; }
        .header { background: #1a202c; color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
        .content { padding: 30px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #718096; }
        .btn { display: inline-block; padding: 12px 24px; background: #3182ce; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .method-box { background: #f7fafc; padding: 20px; border-left: 4px solid #3182ce; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Data Ready!</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>Great news! Your 3D model processing for project <strong>{{ $upload->project_title }}</strong> is complete and ready for download.</p>

            <div class="method-box">
                @if($method === 'portal')
                    <h3>Delivery Method: Web Portal</h3>
                    <p>You can download your processed .zip file directly from your user dashboard. For security, the file is streamed via our high-capacity storage server.</p>
                @elseif($method === 'sftp')
                    <h3>Delivery Method: Direct SFTP</h3>
                    <p>Your processed data has been placed in your dedicated SFTP folder:</p>
                    <code>{{ $upload->sftp_delivery_path ?: '/processed/' }}</code>
                    <p>Please log in using your SFTP credentials to retrieve the files.</p>
                @elseif($method === 'google_drive')
                    <h3>Delivery Method: Google Drive</h3>
                    <p>A new folder has been shared with you on Google Drive containing the processed results.</p>
                @else
                    <h3>Delivery Method: standard Download</h3>
                    <p>Your data is ready for retrieval via the web portal.</p>
                @endif
            </div>

            <a href="{{ url('/my-uploads') }}" class="btn">Go to Dashboard</a>

            <p style="margin-top: 30px;">Thank you for using our 3D Data Portal.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
