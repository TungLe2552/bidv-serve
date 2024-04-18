<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP của bạn</title>
    <style>
        /* CSS inline để hỗ trợ hiển thị đẹp trên các trình duyệt và thiết bị email */
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .otp {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #007bff;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">
            Vui lòng sử dụng mã OTP này để tiếp tục thao tác của bạn. Lưu ý rằng mã OTP này chỉ có hiệu lực trong vòng 30 giây.
        </div>
        <div class="otp">
            Mã OTP của bạn là: <strong>{{ $otp }}</strong>
        </div>
        <div class="footer">
            Email này đã được gửi tự động. Vui lòng không trả lời lại.
        </div>
    </div>
</body>
</html>
