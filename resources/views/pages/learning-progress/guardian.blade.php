@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.child_progress') }}</h4>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <form method="GET" action="{{ route('learning-progress.guardian') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <div>
                    <label class="form-label mb-0">{{ __('label.select_subject') }}</label>
                    <select name="subject" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('label.all_subjects') }}</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject }}" @selected(request('subject') === $subject)>{{ $subject }}</option>
                        @endforeach
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
                    <th>{{ __('field.subject') }}</th>
                    <th>{{ __('field.milestone') }}</th>
                    <th>{{ __('field.score') }}</th>
                    <th>{{ __('field.notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->date->format('Y-m-d') }}</td>
                        <td>{{ optional($record->student)->name ?? '-' }}</td>
                        <td>{{ $record->subject }}</td>
                        <td>{{ $record->milestone }}</td>
                        <td>{{ $record->score !== null ? number_format($record->score, 1) : '-' }}</td>
                        <td>{{ $record->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">{{ __('label.no_progress_records') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body pt-0">
            {{ $records->links() }}
        </div>
    </div>
@endsection
