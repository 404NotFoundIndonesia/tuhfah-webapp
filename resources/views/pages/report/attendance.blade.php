@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('label.report_attendance') }}</h4>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.attendance') }}" class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">{{ __('label.period_type') }}</label>
                    <select name="period" class="form-select">
                        <option value="monthly" @selected($period === 'monthly')>{{ __('label.monthly') }}</option>
                        <option value="weekly" @selected($period === 'weekly')>{{ __('label.weekly') }}</option>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label">{{ __('field.date') }}</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100">{{ __('label.generate_report') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if (request()->hasAny(['period', 'date']))
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('label.report_attendance') }} — {{ $period === 'weekly' ? __('label.weekly') : __('label.monthly') }} ({{ $date }})</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('report.attendance.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}" class="btn btn-sm btn-success">{{ __('label.export_xlsx') }}</a>
                    <a href="{{ route('report.attendance.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-sm btn-danger">{{ __('label.export_pdf') }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('field.name') }}</th>
                            <th>{{ __('label.total_present') }}</th>
                            <th>{{ __('label.total_absent') }}</th>
                            <th>{{ __('label.total_sick') }}</th>
                            <th>{{ __('label.total_permitted') }}</th>
                            <th>{{ __('label.total') }}</th>
                            <th>{{ __('label.attendance_percentage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['student']->name }}</td>
                                <td>{{ $row['present'] }}</td>
                                <td>{{ $row['absent'] }}</td>
                                <td>{{ $row['sick'] }}</td>
                                <td>{{ $row['permitted'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $row['percentage'] >= 75 ? 'success' : ($row['percentage'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $row['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">{{ __('label.no_report_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
