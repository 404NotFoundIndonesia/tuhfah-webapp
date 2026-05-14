@extends('layouts.dashboard')

@section('content')
<h4 class="py-3 mb-4">{{ __('menu.dashboard') }}</h4>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">

    {{-- All roles: total active students --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-success">
                        <i class="bx bxs-graduation fs-4"></i>
                    </span>
                </div>
                <div>
                    <small class="text-muted d-block">{{ __('label.total_active_students') }}</small>
                    <h4 class="mb-0">{{ $stats['total_active_students'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->isRole(\App\Enum\Role::OWNER) || auth()->user()->isRole(\App\Enum\Role::HEADMASTER) || auth()->user()->isRole(\App\Enum\Role::ADMINISTRATOR))

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-calendar-check fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.attendance_rate_today') }}</small>
                        <h4 class="mb-0">
                            @if ($stats['attendance_rate_today'] !== null)
                                {{ $stats['attendance_rate_today'] }}%
                                <small class="text-muted fs-6">({{ $stats['total_recorded_today'] }} {{ __('label.recorded') }})</small>
                            @else
                                <span class="text-muted fs-6">{{ __('label.no_records_today') }}</span>
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-money fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.total_unpaid_payments') }}</small>
                        <h4 class="mb-0">{{ $stats['total_unpaid_payments'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="bx bx-error-circle fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.total_overdue_payments') }}</small>
                        <h4 class="mb-0">{{ $stats['total_overdue_payments'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

    @elseif (auth()->user()->isRole(\App\Enum\Role::TEACHER))

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-book-open fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.progress_students_this_month') }}</small>
                        <h4 class="mb-0">{{ $stats['progress_students_this_month'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

    @elseif (auth()->user()->isRole(\App\Enum\Role::STUDENT_GUARDIAN))

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-calendar-check fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.child_attendance_rate_this_month') }}</small>
                        <h4 class="mb-0">
                            @if ($stats['child_attendance_rate_this_month'] !== null)
                                {{ $stats['child_attendance_rate_this_month'] }}%
                            @else
                                <span class="text-muted fs-6">{{ __('label.no_records') }}</span>
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded {{ $stats['outstanding_payment_count'] > 0 ? 'bg-label-danger' : 'bg-label-success' }}">
                            <i class="bx bx-money fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">{{ __('label.outstanding_payment_count') }}</small>
                        <h4 class="mb-0">{{ $stats['outstanding_payment_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>

{{-- Latest Announcements --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">{{ __('label.latest_announcements') }}</h5>
                <a href="{{ route('announcement.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('label.view_all') }}
                </a>
            </div>
            <div class="card-body">
                @forelse($announcements as $announcement)
                    <div class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="{{ route('announcement.show', $announcement) }}" class="fw-medium text-body">
                                    {{ $announcement->title }}
                                </a>
                                <small class="text-muted ms-2 text-nowrap">
                                    {{ $announcement->published_at?->format('Y-m-d') }}
                                </small>
                            </div>
                            <p class="text-muted small mb-0">{{ Str::limit(strip_tags($announcement->body), 150) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('label.no_announcements') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
