@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.self_attendance') }}</h4>
    </div>

    @if($existing)
        <div class="alert alert-info">
            {{ __('label.attendance_already_submitted') }}
            — <strong>{{ __('label.'.$existing->status->value) }}</strong>
        </div>
    @endif

    <div class="card mb-4">
        <form action="{{ route('attendance.self.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <x-forms.input name="date" type="date" required :value="$today"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-select2 name="status" required
                                           :options="collect($statuses)->map(fn($s) => [$s->value, __('label.'.$s->value)])->toArray()"/>
                </div>
                <div class="mb-3">
                    <x-forms.input-textarea name="notes" :value="null"/>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('button.submit') }}</button>
            </div>
        </form>
    </div>

    <h5 class="mb-3">{{ __('label.my_attendance_history') }}</h5>
    <div class="card">
        <div class="table-responsive pb-3">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.date') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse(auth()->user()->attendances()->orderByDesc('date')->take(30)->get() as $record)
                    <tr>
                        <td>{{ $record->date->format('Y-m-d') }}</td>
                        <td>{{ __('label.'.$record->status->value) }}</td>
                        <td>{{ $record->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-muted">{{ __('label.no_attendance_records') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
