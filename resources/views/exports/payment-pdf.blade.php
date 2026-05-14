<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('menu.payment') }} {{ $period ?? '' }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h2>{{ __('menu.payment') }}</h2>
    @if($period)
        <p>{{ __('field.period') }}: {{ $period }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>{{ __('field.name') }}</th>
                <th>{{ __('field.period') }}</th>
                <th>{{ __('field.amount') }}</th>
                <th>{{ __('field.status') }}</th>
                <th>{{ __('field.due_date') }}</th>
                <th>{{ __('field.paid_at') }}</th>
                <th>{{ __('field.recorded_by') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
                <tr>
                    <td>{{ optional($p->student)->name ?? '-' }}</td>
                    <td>{{ $p->period }}</td>
                    <td>{{ number_format((float) $p->amount, 0, '.', ',') }}</td>
                    <td>{{ $p->status->value }}</td>
                    <td>{{ $p->due_date->format('Y-m-d') }}</td>
                    <td>{{ $p->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>{{ optional($p->recordedBy)->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
