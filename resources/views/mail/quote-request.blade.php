<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب عرض سعر جديد</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f9;font-family:Tahoma,Arial,sans-serif;color:#0a3356;line-height:1.8;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d9e2ec;">
                    <tr>
                        <td style="background:#0a3356;color:#ffffff;padding:22px 28px;">
                            <p style="margin:0 0 6px;font-size:13px;opacity:0.85;">ركيزة للخدمات المتكاملة</p>
                            <h1 style="margin:0;font-size:22px;font-weight:700;">طلب عرض سعر جديد</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 20px;">وصل طلب جديد من نموذج الموقع. تفاصيل العميل أدناه:</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;width:140px;font-weight:700;vertical-align:top;">الاسم</td>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;">{{ $customerName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;font-weight:700;vertical-align:top;">رقم الجوال</td>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;" dir="ltr">{{ $phone }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;font-weight:700;vertical-align:top;">البريد الإلكتروني</td>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;" dir="ltr">{{ $customerEmail }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;font-weight:700;vertical-align:top;">نوع الخدمة</td>
                                    <td style="padding:12px 0;border-bottom:1px solid #e6eef5;">{{ $serviceType }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0;font-weight:700;vertical-align:top;">تفاصيل الطلب</td>
                                    <td style="padding:12px 0;white-space:pre-wrap;">{{ $details }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px;color:#5b6b7c;font-size:13px;">
                            تم إرسال هذه الرسالة تلقائياً من موقع ركيزة.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
