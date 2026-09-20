<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب خدمة جديد</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; color: #0a3356; line-height: 1.8;">
    <h1 style="font-size: 20px;">طلب خدمة جديد من الموقع</h1>
    <p><strong>الاسم:</strong> {{ $lead->name }}</p>
    <p><strong>الجوال:</strong> <span dir="ltr">{{ $lead->phone }}</span></p>
    <p><strong>الخدمة:</strong> {{ $lead->service?->title ?? 'استفسار عام' }}</p>
    <p><strong>التفاصيل:</strong></p>
    <p>{{ $lead->message ?: '-' }}</p>
</body>
</html>
