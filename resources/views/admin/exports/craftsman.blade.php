<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="utf-8">
    <title>طلب حرفي — {{ $craftsman->name }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:RTL/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page { size: A4; margin: 1.8cm; }
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; color: #0a3356; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p { margin: 0 0 14px; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; font-size: 13px; }
        th, td { border: 1px solid #0a3356; padding: 8px 10px; text-align: right; }
        th { width: 28%; background: #0a3356; color: #ffffff; }
    </style>
</head>
<body>
    <h1>طلب تسجيل حرفي — ركيزة</h1>
    <p>تاريخ التصدير: {{ $exportedAt }}</p>

    <table>
        <tr>
            <th>الاسم</th>
            <td>{{ $craftsman->name }}</td>
        </tr>
        <tr>
            <th>الجوال</th>
            <td dir="ltr">{{ $craftsman->phone }}</td>
        </tr>
        <tr>
            <th>المنطقة</th>
            <td>{{ $craftsman->city }}</td>
        </tr>
        <tr>
            <th>التخصص</th>
            <td>{{ $craftsman->specialty }}</td>
        </tr>
        <tr>
            <th>سنوات الخبرة</th>
            <td>{{ $craftsman->experience_years }} سنة</td>
        </tr>
        <tr>
            <th>معدات خاصة</th>
            <td>{{ $craftsman->has_tools ? 'نعم' : 'لا' }}</td>
        </tr>
        <tr>
            <th>النبذة</th>
            <td>{{ $craftsman->bio ?: '—' }}</td>
        </tr>
        <tr>
            <th>حالة الطلب</th>
            <td>{{ $craftsman->status->label() }}</td>
        </tr>
        <tr>
            <th>تاريخ التقديم</th>
            <td>{{ $craftsman->created_at?->format('Y-m-d H:i') }}</td>
        </tr>
    </table>
</body>
</html>
