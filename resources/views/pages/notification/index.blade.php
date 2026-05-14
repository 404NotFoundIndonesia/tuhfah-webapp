@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            {{ __('menu.notifications') }}
        </h4>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notification.read-all') }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    {{ __('label.mark_all_read') }}
                </button>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="d-flex align-items-start p-3 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-circle bg-{{ $notification->read_at ? 'secondary' : 'primary' }}">
                                <i class="bx bx-bell"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <div>
                                @php $data = $notification->data; @endphp
                                @if(isset($data['student_name']) && isset($data['status']))
                                    <p class="mb-1">
                                        <strong>{{ $data['student_name'] }}</strong>
                                        {{ __('label.was_marked_as') }}
                                        <span class="badge bg-warning">{{ $data['status'] }}</span>
                                        {{ __('label.on') }} {{ $data['date'] }}
                                    </p>
                                    <small class="text-muted">{{ __('label.recorded_by') }}: {{ $data['recorded_by'] }}</small>
                                @elseif(isset($data['student_name']) && isset($data['period']))
                                    <p class="mb-1">
                                        {{ __('label.payment_overdue_for') }}
                                        <strong>{{ $data['student_name'] }}</strong>
                                        ({{ $data['period'] }})
                                    </p>
                                    <small class="text-muted">{{ __('label.amount') }}: {{ $data['amount'] }} · {{ __('field.due_date') }}: {{ $data['due_date'] }}</small>
                                @elseif(isset($data['student_name']) && isset($data['milestone']))
                                    <p class="mb-1">
                                        {{ __('label.new_progress_for') }}
                                        <strong>{{ $data['student_name'] }}</strong>:
                                        {{ $data['subject'] }} — {{ $data['milestone'] }}
                                    </p>
                                    <small class="text-muted">{{ __('label.teacher') }}: {{ $data['teacher_name'] }} · {{ $data['date'] }}</small>
                                @else
                                    <p class="mb-0">{{ __('label.notification') }}</p>
                                @endif
                            </div>
                            <div class="text-end ms-2">
                                <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                                @if(!$notification->read_at)
                                    <form action="{{ route('notification.read', $notification->id) }}" method="POST" class="mt-1">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            {{ __('label.mark_read') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bx bx-bell-off bx-lg text-muted"></i>
                    <p class="text-muted mt-2">{{ __('label.no_notifications') }}</p>
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
