<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('label.report_attendance') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin-bottom: 4px; }
        .badge-success { color: green; }
        .badge-warning { color: orange; }
        .badge-danger { color: red; }
    </style>
</head>
<body>
    <h2>{{ __('label.report_attendance') }}</h2>
    <p>{{ __('label.period_type') }}: {{ $period === 'weekly' ? __('label.weekly') : __('label.monthly') }} | {{ __('field.date') }}: {{ $date }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('field.name') }}</th>
                <th>{{ __('label.total_present') }}</th>
                <th>{{ __('label.total_absent') }}</th>
                <th>{{ __('label.total_sick') }}</th>
                <th>{{ __('label.total_permitted') }}</th>
                <th>Total</th>
                <th>{{ __('label.attendance_percentage') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['student']->name }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['absent'] }}</td>
                    <td>{{ $row['sick'] }}</td>
                    <td>{{ $row['permitted'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td class="{{ $row['percentage'] >= 75 ? 'badge-success' : ($row['percentage'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $row['percentage'] }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
