@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('label.report_finance') }}</h4>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.finance') }}" class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">{{ __('field.period') }}</label>
                    <input type="month" name="period" class="form-control" value="{{ $period }}">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100">{{ __('label.generate_report') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if ($summary)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('label.report_finance') }} — {{ $period }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('report.finance.export', ['period' => $period, 'format' => 'xlsx']) }}" class="btn btn-sm btn-success">{{ __('label.export_xlsx') }}</a>
                    <a href="{{ route('report.finance.export', ['period' => $period, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger">{{ __('label.export_pdf') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-4">
                        <div class="card bg-label-success h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ __('label.total_collected') }}</small>
                                <h4 class="mb-0">{{ number_format($summary['total_collected'], 0, '.', ',') }}</h4>
                                <small class="text-muted">{{ $summary['payment_counts']['paid'] }} {{ __('label.payments') ?? 'payments' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card bg-label-warning h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ __('label.total_outstanding') }}</small>
                                <h4 class="mb-0">{{ number_format($summary['total_outstanding'], 0, '.', ',') }}</h4>
                                <small class="text-muted">{{ $summary['payment_counts']['unpaid'] }} {{ __('label.payments') ?? 'payments' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card bg-label-danger h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ __('label.total_overdue') }}</small>
                                <h4 class="mb-0">{{ number_format($summary['total_overdue'], 0, '.', ',') }}</h4>
                                <small class="text-muted">{{ $summary['payment_counts']['overdue'] }} {{ __('label.payments') ?? 'payments' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card bg-label-info h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ __('label.total_honorariums_paid') }}</small>
                                <h4 class="mb-0">{{ number_format($summary['total_honorariums_paid'], 0, '.', ',') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card bg-label-primary h-100">
                            <div class="card-body">
                                <small class="text-muted d-block">{{ __('label.net_income') }}</small>
                                <h4 class="mb-0 {{ $summary['net_income'] < 0 ? 'text-danger' : '' }}">
                                    {{ number_format($summary['net_income'], 0, '.', ',') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (request()->has('period'))
        <div class="alert alert-info">{{ __('label.no_report_data') }}</div>
    @endif
@endsection
