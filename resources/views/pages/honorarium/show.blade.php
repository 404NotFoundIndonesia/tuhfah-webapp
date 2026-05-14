@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('honorarium.index') }}">{{ __('menu.honorarium') }} /</a>
            {{ __('label.detail') }}
        </h4>
        <a href="{{ route('honorarium.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('field.teacher_id') }}</dt>
                <dd class="col-sm-9">{{ optional($honorarium->teacher)->name ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.period') }}</dt>
                <dd class="col-sm-9">{{ $honorarium->period }}</dd>

                <dt class="col-sm-3">{{ __('field.amount') }}</dt>
                <dd class="col-sm-9">Rp {{ number_format((float) $honorarium->amount, 0, '.', ',') }}</dd>

                <dt class="col-sm-3">{{ __('field.status') }}</dt>
                <dd class="col-sm-9">
                    @php
                        $badgeClass = match($honorarium->status->value) {
                            'paid' => 'bg-success',
                            'overdue' => 'bg-danger',
                            default => 'bg-warning',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ __('label.'.$honorarium->status->value) }}</span>
                </dd>

                <dt class="col-sm-3">{{ __('field.paid_at') }}</dt>
                <dd class="col-sm-9">{{ $honorarium->paid_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.recorded_by') }}</dt>
                <dd class="col-sm-9">{{ optional($honorarium->recordedBy)->name ?? '-' }}</dd>
            </dl>
        </div>

        @if($honorarium->status->value !== 'paid')
            <div class="card-footer">
                <form action="{{ route('honorarium.mark-paid', $honorarium) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">{{ __('button.mark_paid') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection
