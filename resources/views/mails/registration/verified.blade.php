<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="background-color: #0a0a0a; color: #E2E2DE; font-family: 'Courier New', Courier, monospace; margin: 0; padding: 40px 20px;">
    
    <div style="max-width: 600px; margin: 0 auto; background-color: #1a1a1a; border: 1px solid #494947; padding: 40px;">
        
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 1px dashed #494947; padding-bottom: 30px;">
            <img src="{{ $message->embed(public_path('assets/logo INNOF.png')) }}" alt="INNOFASHION 8 LOGO" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
        </div>

        <p style="color: #979086; font-size: 12px; letter-spacing: 4px; margin-top: 0;">[ SYSTEM NOTIFICATION ]</p>
        
        <h1 style="color: #22c55e; font-size: 24px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 30px;">
            > PROTOCOL VERIFIED
        </h1>

        <p style="font-size: 14px; line-height: 1.6; color: #E2E2DE;">
            Greetings, <strong>{{ $registration->user->name }}</strong>.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #979086;">
            Your registration data for the following {{ $type }} has been successfully reviewed and approved by the Administrator:
        </p>

        <div style="background-color: #1C1C1B; border: 1px solid #494947; padding: 20px; margin: 25px 0;">
            <p style="margin: 0; color: #B7AC9B; font-size: 12px; letter-spacing: 2px;">{{ $type }} NAME</p>
            <p style="margin: 5px 0 0 0; color: #E2E2DE; font-size: 18px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">
                {{ $itemName }}
            </p>
        </div>

        @if($type === 'EVENT' && $qrCodeData)
            <div style="margin-top: 40px; text-align: center;">
                <p style="color: #22c55e; font-weight: bold; font-size: 14px; letter-spacing: 2px; margin-bottom: 15px;">
                    ⬇ YOUR SECURE ACCESS PASS ⬇
                </p>
                
                <div style="display: inline-block; padding: 15px; background-color: #E2E2DE;">
                    <img src="{{ $message->embedData($qrCodeData, 'Access_Pass_Innofashion.png') }}" alt="QR Code Access Pass" style="width: 250px; height: 250px; display: block;">
                </div>
                
                <p style="color: #979086; font-size: 12px; margin-top: 15px; letter-spacing: 1px;">
                    Please present this QR code at the entrance.<br>
                    A copy of this QR has been attached to this email.
                </p>
            </div>
        @endif

        <div style="margin-top: 50px; border-top: 1px solid #494947; padding-top: 20px;">
            <p style="color: #7b787a; font-size: 10px; letter-spacing: 1px; text-align: center;">
                INNOFASHION SHOW 8 AUTOMATED SYSTEM<br>
                DO NOT REPLY TO THIS EMAIL
            </p>
        </div>
    </div>

</body>
</html>