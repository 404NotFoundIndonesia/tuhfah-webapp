@auth
<li class="nav-item navbar-dropdown dropdown-user dropdown me-2">
    <a class="nav-link dropdown-toggle hide-arrow" href="{{ route('notification.index') }}" id="notificationBell">
        <div class="position-relative">
            <i class="bx bx-bell bx-sm"></i>
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            @if($unreadCount > 0)
                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle"
                      id="notification-badge"
                      style="font-size: 0.6rem; min-width: 16px; padding: 2px 4px;">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @else
                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle d-none"
                      id="notification-badge"
                      style="font-size: 0.6rem; min-width: 16px; padding: 2px 4px;">
                    0
                </span>
            @endif
        </div>
    </a>
</li>
@endauth
