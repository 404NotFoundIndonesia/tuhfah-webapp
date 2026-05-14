<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('label.report_progress') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f3f3f3; }
        h2 { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h2>{{ __('label.report_progress') }}</h2>
    <p>{{ __('field.name') }}: {{ $student->name }} | {{ __('label.from_date') }}: {{ $from }} | {{ __('label.to_date') }}: {{ $to }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('field.date') }}</th>
                <th>{{ __('field.subject') }}</th>
                <th>{{ __('field.milestone') }}</th>
                <th>{{ __('field.score') }}</th>
                <th>{{ __('field.teacher_id') }}</th>
                <th>{{ __('field.notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $i => $record)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $record->date->format('Y-m-d') }}</td>
                    <td>{{ $record->subject }}</td>
                    <td>{{ $record->milestone }}</td>
                    <td>{{ $record->score ?? '-' }}</td>
                    <td>{{ $record->teacher?->name ?? '-' }}</td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
