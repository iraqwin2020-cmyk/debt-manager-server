<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>كشف حساب — {{ $debtor->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Cairo', sans-serif; padding: 24px; color: #1a1625; background: #fff; max-width: 720px; margin: 0 auto; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #e8600c; padding-bottom: 16px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        td, th { border: 1px solid #ddd; padding: 8px 12px; text-align: start; font-size: 14px; }
        .section-title { font-weight: 800; margin-top: 20px; margin-bottom: 8px; }
        .toolbar { display: flex; gap: 8px; margin-bottom: 16px; }
        .toolbar a, .toolbar button { font-family: inherit; font-size: 14px; font-weight: 700; padding: 8px 16px; border-radius: 999px; border: 2px solid #e8600c; background: #fff; color: #e8600c; cursor: pointer; text-decoration: none; }
        bdi { direction: ltr; unicode-bidi: isolate; display: inline-block; }
        bdi.date-rtl { direction: rtl; }
        .total-row { font-weight: 800; background: #fff6ec; }

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
        <a href="{{ route('app.debtors.show', $debtor->id) }}">← رجوع لبطاقة العميل</a>
        <button onclick="window.print()">طباعة</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $tenant->name }}</h1>
            <p><bdi>{{ $tenant->phone }}</bdi></p>
        </div>
        <div style="text-align:left">
            <p>كشف حساب</p>
            <p><bdi class="date-rtl" dir="rtl">{{ now()->format('d m Y') }}</bdi></p>
        </div>
    </div>

    <div class="section-title">بيانات العميل</div>
    <table>
        <tr><th>الاسم</th><td>{{ $debtor->name }}</td></tr>
        <tr><th>الهاتف</th><td><bdi>{{ $debtor->phone }}</bdi></td></tr>
        <tr><th>العنوان</th><td>{{ $debtor->address ?? '—' }}</td></tr>
    </table>

    <div class="section-title">الديون والدفعات</div>
    @foreach($debtor->debts as $debt)
        <table>
            <tr>
                <th colspan="4">
                    وصل دين <bdi>#{{ $debt->receipt_number }}</bdi> —
                    <span style="display:inline-flex;align-items:center;gap:4px" dir="ltr"><span>{{ $debt->currency === 'USD' ? '$' : 'د.ع' }}</span><bdi>{{ number_format($debt->amount) }}</bdi></span>
                    (<bdi class="date-rtl" dir="rtl">{{ $debt->created_at->format('Y-m-d') }}</bdi>)
                </th>
            </tr>
            <tr><th>رقم الوصل</th><th>تاريخ الدفعة</th><th>المبلغ</th><th></th></tr>
            @forelse($debt->payments as $payment)
                <tr>
                    <td><bdi>#{{ $payment->receipt_number }}</bdi></td>
                    <td><bdi class="date-rtl" dir="rtl">{{ $payment->paid_at->format('Y-m-d') }}</bdi></td>
                    <td><bdi>{{ number_format($payment->amount) }}</bdi></td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center">لا دفعات على هذا الدين بعد</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2">المتبقي</td>
                <td colspan="2"><span style="display:inline-flex;align-items:center;gap:4px" dir="ltr"><span>{{ $debt->currency === 'USD' ? '$' : 'د.ع' }}</span><bdi>{{ number_format($debt->amount - $debt->paid_amount) }}</bdi></span></td>
            </tr>
        </table>
    @endforeach
    @if($debtor->debts->isEmpty())
        <p>لا توجد ديون مسجَّلة لهذا العميل.</p>
    @endif
</body>
</html>
