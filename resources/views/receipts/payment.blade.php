<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>وصل تسديد رقم {{ $payment->receipt_number }}</title>
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
        <a href="{{ route('app.debtors.show', $payment->debtor->id) }}">← رجوع لبطاقة العميل</a>
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
            <p>وصل تسديد رقم <bdi>#{{ $payment->receipt_number }}</bdi></p>
        </div>
    </div>

    <table>
        <tr><th>المبلغ المسدَّد</th><td><bdi>{{ number_format($payment->amount) }}</bdi> {{ $payment->debt->currency === 'USD' ? '$' : 'د.ع' }}</td></tr>
        <tr><th>تاريخ الدفعة</th><td><bdi class="date-rtl" dir="rtl">{{ $payment->paid_at->format('d m Y') }}</bdi></td></tr>
        <tr><th>الدين المرتبط</th><td>وصل دين رقم <bdi>#{{ $payment->debt->receipt_number }}</bdi>@if($payment->installment) — قسط رقم {{ $payment->installment->seq_number }}@endif</td></tr>
        <tr><th>المتبقي على الدين</th><td><bdi>{{ number_format($payment->debt->amount - $payment->debt->paid_amount) }}</bdi> {{ $payment->debt->currency === 'USD' ? '$' : 'د.ع' }}</td></tr>
        <tr><th>العميل</th><td>{{ $payment->debtor->name }} — <bdi>{{ $payment->debtor->phone }}</bdi></td></tr>
        @if($payment->note)
            <tr><th>ملاحظات</th><td>{{ $payment->note }}</td></tr>
        @endif
    </table>
</body>
</html>
