<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>وصل دين رقم {{ $debt->receipt_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Cairo', sans-serif; padding: 24px; color: #1a1625; background: #fff; max-width: 720px; margin: 0 auto; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #7c3aed; padding-bottom: 16px; margin-bottom: 24px; }
        .header img { height: 56px; width: 56px; border-radius: 50%; object-fit: cover; }
        .header h1 { font-size: 18px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        td, th { border: 1px solid #ddd; padding: 8px 12px; text-align: start; font-size: 14px; }
        .section-title { font-weight: 800; margin-top: 20px; margin-bottom: 8px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; gap: 24px; }
        .signature-box { flex: 1; text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 6px; }
        .toolbar { display: flex; gap: 8px; margin-bottom: 16px; }
        .toolbar a, .toolbar button { font-family: inherit; font-size: 14px; font-weight: 700; padding: 8px 16px; border-radius: 999px; border: 2px solid #7c3aed; background: #fff; color: #7c3aed; cursor: pointer; text-decoration: none; }
        bdi { direction: ltr; unicode-bidi: isolate; display: inline-block; }
        bdi.date-rtl { direction: rtl; }

        @media print {
            .toolbar { display: none; }
            body { padding: 0; max-width: 100%; }
            table { page-break-inside: avoid; }
            @page { size: A4; margin: 16mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('app.debtors.show', $debt->debtor->id) }}">← رجوع لبطاقة العميل</a>
        <button onclick="window.print()">طباعة</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $tenant->name }}</h1>
            <p><bdi>{{ $tenant->phone }}</bdi></p>
        </div>
        <div style="text-align: left">
            @if($tenant->logo)
                <img src="{{ asset('storage/'.$tenant->logo) }}" alt="">
            @endif
            <p>وصل دين رقم <bdi>#{{ $debt->receipt_number }}</bdi></p>
        </div>
    </div>

    <div class="section-title">بيانات الدين</div>
    <table>
        <tr><th>المبلغ</th><td><bdi>{{ number_format($debt->amount) }}</bdi> {{ $debt->currency === 'USD' ? '$' : 'د.ع' }}</td></tr>
        <tr><th>التاريخ</th><td><bdi class="date-rtl" dir="rtl">{{ $debt->created_at->format('d m Y') }}</bdi></td></tr>
        @if($debt->payment_type === 'lump_sum')
            <tr><th>تاريخ الاستحقاق</th><td><bdi class="date-rtl" dir="rtl">{{ optional($debt->due_date)->format('d m Y') ?? '—' }}</bdi></td></tr>
        @endif
        <tr><th>الوصف</th><td>{{ $debt->description ?? '—' }}</td></tr>
    </table>

    @if($debt->payment_type === 'installments')
        <div class="section-title">جدول الأقساط</div>
        <table>
            <tr><th>#</th><th>المبلغ</th><th>الاستحقاق</th></tr>
            @foreach($debt->installments as $installment)
                <tr>
                    <td>{{ $installment->seq_number }}</td>
                    <td><bdi>{{ number_format($installment->amount) }}</bdi></td>
                    <td><bdi class="date-rtl" dir="rtl">{{ $installment->due_date->format('d m Y') }}</bdi></td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="section-title">بيانات العميل</div>
    <table>
        <tr><th>الاسم</th><td>{{ $debt->debtor->name }}</td></tr>
        <tr><th>الهاتف</th><td><bdi>{{ $debt->debtor->phone }}</bdi></td></tr>
        <tr><th>العنوان</th><td>{{ $debt->debtor->address ?? '—' }}</td></tr>
    </table>

    @if($debt->guarantors->isNotEmpty())
        <div class="section-title">بيانات الكفيل</div>
        <table>
            <tr><th>الاسم</th><th>الهاتف</th><th>العنوان</th></tr>
            @foreach($debt->guarantors as $guarantor)
                <tr>
                    <td>{{ $guarantor->name }}</td>
                    <td><bdi>{{ $guarantor->phone }}</bdi></td>
                    <td>{{ $guarantor->address ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">توقيع العميل: {{ $debt->debtor->name }}</div>
        </div>
        @foreach($debt->guarantors as $guarantor)
            <div class="signature-box">
                <div class="signature-line">توقيع الكفيل: {{ $guarantor->name }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
