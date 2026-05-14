@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('attendance.index') }}">{{ __('menu.attendance') }} /</a>
            {{ __('label.detail') }}
        </h4>
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('field.date') }}</dt>
                <dd class="col-sm-9">{{ $attendance->date->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">{{ __('field.name') }}</dt>
                <dd class="col-sm-9">{{ optional($attendance->attendable)->name ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.status') }}</dt>
                <dd class="col-sm-9">{{ __('label.'.$attendance->status->value) }}</dd>

                <dt class="col-sm-3">{{ __('field.notes') }}</dt>
                <dd class="col-sm-9">{{ $attendance->notes ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.recorded_by') }}</dt>
                <dd class="col-sm-9">{{ optional($attendance->recordedBy)->name ?? '-' }}</dd>
            </dl>
        </div>
    </div>
@endsection
