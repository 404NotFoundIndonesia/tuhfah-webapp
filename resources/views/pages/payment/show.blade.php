@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('payment.index') }}">{{ __('menu.payment') }} /</a>
            {{ __('label.detail') }}
        </h4>
        <a href="{{ route('payment.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">{{ __('field.name') }}</dt>
                <dd class="col-sm-9">{{ optional($payment->student)->name ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.period') }}</dt>
                <dd class="col-sm-9">{{ $payment->period }}</dd>

                <dt class="col-sm-3">{{ __('field.amount') }}</dt>
                <dd class="col-sm-9">Rp {{ number_format((float) $payment->amount, 0, '.', ',') }}</dd>

                <dt class="col-sm-3">{{ __('field.status') }}</dt>
                <dd class="col-sm-9">
                    @php
                        $badgeClass = match($payment->status->value) {
                            'paid' => 'bg-success',
                            'overdue' => 'bg-danger',
                            default => 'bg-warning',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ __('label.'.$payment->status->value) }}</span>
                </dd>

                <dt class="col-sm-3">{{ __('field.due_date') }}</dt>
                <dd class="col-sm-9">{{ $payment->due_date->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">{{ __('field.paid_at') }}</dt>
                <dd class="col-sm-9">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '-' }}</dd>

                <dt class="col-sm-3">{{ __('field.recorded_by') }}</dt>
                <dd class="col-sm-9">{{ optional($payment->recordedBy)->name ?? '-' }}</dd>
            </dl>
        </div>

        @if($payment->status->value !== 'paid')
            <div class="card-footer">
                <form action="{{ route('payment.mark-paid', $payment) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">{{ __('button.mark_paid') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection
