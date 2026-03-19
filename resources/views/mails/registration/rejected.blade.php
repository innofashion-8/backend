<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="background-color: #0a0a0a; color: #E2E2DE; font-family: 'Courier New', Courier, monospace; margin: 0; padding: 40px 20px;">
    
    <div style="max-width: 600px; margin: 0 auto; background-color: #1a1a1a; border: 1px solid #494947; padding: 40px;">
        
        <p style="color: #979086; font-size: 12px; letter-spacing: 4px; margin-top: 0;">[ SYSTEM NOTIFICATION ]</p>
        
        <h1 style="color: #ef4444; font-size: 24px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 30px;">
            > DATA REJECTED
        </h1>

        <p style="font-size: 14px; line-height: 1.6; color: #E2E2DE;">
            Greetings, <strong>{{ $registration->user->name }}</strong>.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #979086;">
            We regret to inform you that your registration protocol for <strong>{{ $itemName }}</strong> has been rejected by the Administrator.
        </p>

        <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; padding: 20px; margin: 25px 0;">
            <p style="margin: 0; color: #ef4444; font-size: 12px; font-weight: bold; letter-spacing: 2px;">REJECTION REASON:</p>
            <p style="margin: 10px 0 0 0; color: #fca5a5; font-size: 14px; line-height: 1.5;">
                {{ $reason }}
            </p>
        </div>

        <p style="font-size: 12px; color: #B7AC9B; font-weight: bold; letter-spacing: 1px; margin-top: 30px;">
            > ACTION REQUIRED:
        </p>
        <p style="font-size: 14px; color: #979086; line-height: 1.6;">
            Please log in to your dashboard to recalibrate your identity and submit a revised registration protocol.
        </p>

        <div style="margin-top: 50px; border-top: 1px solid #494947; padding-top: 20px;">
            <p style="color: #7b787a; font-size: 10px; letter-spacing: 1px; text-align: center;">
                INNOFASHION SHOW 8 AUTOMATED SYSTEM<br>
                DO NOT REPLY TO THIS EMAIL
            </p>
        </div>
    </div>

</body>
</html>