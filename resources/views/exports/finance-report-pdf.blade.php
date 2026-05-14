<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('label.report_finance') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 60%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h2>{{ __('label.report_finance') }}</h2>
    <p>{{ __('field.period') }}: {{ $period }}</p>
    <table>
        <tbody>
            <tr><th>{{ __('label.total_collected') }}</th><td>{{ number_format($summary['total_collected'], 0, '.', ',') }}</td></tr>
            <tr><th>{{ __('label.total_outstanding') }}</th><td>{{ number_format($summary['total_outstanding'], 0, '.', ',') }}</td></tr>
            <tr><th>{{ __('label.total_overdue') }}</th><td>{{ number_format($summary['total_overdue'], 0, '.', ',') }}</td></tr>
            <tr><th>{{ __('label.total_honorariums_paid') }}</th><td>{{ number_format($summary['total_honorariums_paid'], 0, '.', ',') }}</td></tr>
            <tr><th>{{ __('label.net_income') }}</th><td>{{ number_format($summary['net_income'], 0, '.', ',') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
