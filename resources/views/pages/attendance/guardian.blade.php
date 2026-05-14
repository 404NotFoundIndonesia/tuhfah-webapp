@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.child_attendance') }}</h4>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <form method="GET" action="{{ route('attendance.guardian') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <div>
                    <label class="form-label mb-0">{{ __('field.month') }}</label>
                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === $month)>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label mb-0">{{ __('field.year') }}</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
        <div class="table-responsive pb-3">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.date') }}</th>
                    <th>{{ __('field.name') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($attendances as $record)
                    <tr>
                        <td>{{ $record->date->format('Y-m-d') }}</td>
                        <td>{{ optional($record->attendable)->name ?? '-' }}</td>
                        <td>{{ __('label.'.$record->status->value) }}</td>
                        <td>{{ $record->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">{{ __('label.no_attendance_records') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
