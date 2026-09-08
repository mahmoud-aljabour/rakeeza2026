<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="utf-8">
    <title>طلبات الحرفيين — ركيزة</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:RTL/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page { size: A4 landscape; margin: 1.4cm; }
        body { font-family: Tahoma, Arial, sans-serif; direction: rtl; color: #0a3356; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p { margin: 0 0 10px; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #0a3356; padding: 6px 8px; text-align: right; vertical-align: top; }
        th { background: #0a3356; color: #ffffff; }
    </style>
</head>
<body>
    <h1>طلبات تسجيل الحرفيين — ركيزة</h1>
    <p>الحالة: {{ $statusLabel }} — تاريخ التصدير: {{ $exportedAt }} — العدد: {{ $craftsmen->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الجوال</th>
                <th>المنطقة</th>
                <th>التخصص</th>
                <th>الخبرة</th>
                <th>معدات</th>
                <th>نبذة</th>
                <th>الحالة</th>
                <th>تاريخ الطلب</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($craftsmen as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td dir="ltr">{{ $item->phone }}</td>
                    <td>{{ $item->city }}</td>
                    <td>{{ $item->specialty }}</td>
                    <td>{{ $item->experience_years }} سنة</td>
                    <td>{{ $item->has_tools ? 'نعم' : 'لا' }}</td>
                    <td>{{ $item->bio }}</td>
                    <td>{{ $item->status->label() }}</td>
                    <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">لا توجد طلبات حرفيين في هذا التصدير.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
