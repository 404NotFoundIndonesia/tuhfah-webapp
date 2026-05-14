@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('announcement.index') }}">{{ __('menu.announcement') }} /</a>
            {{ __('label.detail') }}
        </h4>
        <div class="d-flex gap-2">
            @php
                $isAdmin = auth()->check() && (
                    auth()->user()->isRole(\App\Enum\Role::OWNER)
                    || auth()->user()->isRole(\App\Enum\Role::HEADMASTER)
                    || auth()->user()->isRole(\App\Enum\Role::ADMINISTRATOR)
                );
            @endphp
            @if($isAdmin && !$announcement->published_at)
                <form action="{{ route('announcement.publish', $announcement) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success text-white fw-medium">
                        {{ __('label.publish_now') }}
                    </button>
                </form>
            @endif
            @if($isAdmin)
                <a href="{{ route('announcement.edit', $announcement) }}" class="btn btn-secondary text-white fw-medium">
                    {{ __('label.edit') }}
                </a>
            @endif
            <a href="{{ route('announcement.index') }}" class="btn btn-outline-secondary fw-medium">
                {{ __('button.back') }}
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-{{ $announcement->scope->value === 'public' ? 'info' : 'secondary' }} me-2">
                    {{ __('label.scope_'.$announcement->scope->value) }}
                </span>
                @if($announcement->published_at)
                    <span class="badge bg-success">{{ __('label.published') }}</span>
                @else
                    <span class="badge bg-warning">{{ __('label.draft') }}</span>
                @endif
            </div>
            <small class="text-muted">
                {{ optional($announcement->author)->name ?? '-' }}
                @if($announcement->published_at)
                    &middot; {{ $announcement->published_at->format('Y-m-d H:i') }}
                @endif
            </small>
        </div>
        <div class="card-body">
            <h3 class="mb-4">{{ $announcement->title }}</h3>
            <div class="announcement-body">
                {!! nl2br(e($announcement->body)) !!}
            </div>
        </div>
    </div>
@endsection
