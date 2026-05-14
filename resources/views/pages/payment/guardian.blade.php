@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('label.child_payment_history') }}</h4>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <form method="GET" action="{{ route('payment.guardian') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <div>
                    <label class="form-label mb-0">{{ __('label.select_period') }}</label>
                    <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('label.all_periods') }}</option>
                        @foreach($periods as $period)
                            <option value="{{ $period }}" @selected(request('period') === $period)>{{ $period }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="table-responsive pb-3">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.period') }}</th>
                    <th>{{ __('field.amount') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.due_date') }}</th>
                    <th>{{ __('field.paid_at') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($records as $record)
                    @php
                        $badgeClass = match($record->status->value) {
                            'paid' => 'bg-success',
                            'overdue' => 'bg-danger',
                            default => 'bg-warning',
                        };
                        $isOutstanding = in_array($record->status->value, ['unpaid', 'overdue']);
                    @endphp
                    <tr @class(['table-warning' => $record->status->value === 'overdue'])>
                        <td>{{ $record->period }}</td>
                        <td>Rp {{ number_format((float) $record->amount, 0, '.', ',') }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ __('label.'.$record->status->value) }}</span></td>
                        <td>{{ $record->due_date->format('Y-m-d') }}</td>
                        <td>{{ $record->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>
                            @if($isOutstanding)
                                <button class="btn btn-sm btn-primary pay-now-btn"
                                    data-payment-id="{{ $record->id }}"
                                    data-checkout-url="{{ route('payment.checkout', $record) }}">
                                    {{ __('label.pay_now') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">{{ __('label.no_payment_records') }}</td>
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

@push('script')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        document.querySelectorAll('.pay-now-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.dataset.checkoutUrl;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).then(r => r.json()).then(data => {
                    snap.pay(data.snap_token, {
                        onSuccess: () => location.reload(),
                        onPending: () => location.reload(),
                    });
                });
            });
        });
    </script>
@endpush
