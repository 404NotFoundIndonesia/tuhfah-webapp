@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('label.report_progress') }}</h4>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.progress') }}" class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">{{ __('field.student_id') }}</label>
                    <select name="student_id" class="form-select">
                        <option value="">— {{ __('field.student_id') }} —</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}" @selected((string)$studentId === (string)$s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">{{ __('label.from_date') }}</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">{{ __('label.to_date') }}</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('label.generate_report') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if ($studentId && $from && $to)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $student?->name }} — {{ $from }} ~ {{ $to }}</h5>
                <a href="{{ route('report.progress.export', ['student_id' => $studentId, 'from' => $from, 'to' => $to, 'format' => 'pdf']) }}"
                   class="btn btn-sm btn-danger">{{ __('label.export_pdf') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table">
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
                        @forelse ($records as $i => $record)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $record->date->format('Y-m-d') }}</td>
                                <td>{{ $record->subject }}</td>
                                <td>{{ $record->milestone }}</td>
                                <td>{{ $record->score ?? '-' }}</td>
                                <td>{{ $record->teacher?->name ?? '-' }}</td>
                                <td>{{ $record->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ __('label.no_report_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
