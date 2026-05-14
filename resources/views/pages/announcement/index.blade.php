@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.announcement') }}</h4>
        @if($isAdmin)
            <a href="{{ route('announcement.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('label.new') }}
            </a>
        @endif
    </div>

    <div class="row">
        @forelse($announcements as $announcement)
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">
                                <a href="{{ route('announcement.show', $announcement) }}" class="text-body">
                                    {{ $announcement->title }}
                                </a>
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-{{ $announcement->scope->value === 'public' ? 'info' : 'secondary' }}">
                                    {{ __('label.scope_'.$announcement->scope->value) }}
                                </span>
                                @if($announcement->published_at)
                                    <span class="badge bg-success">{{ __('label.published') }}</span>
                                @else
                                    <span class="badge bg-warning">{{ __('label.draft') }}</span>
                                @endif
                            </div>
                        </div>

                        <p class="card-text text-muted small mb-2">
                            {{ optional($announcement->author)->name ?? '-' }}
                            @if($announcement->published_at)
                                &middot; {{ $announcement->published_at->format('Y-m-d H:i') }}
                            @endif
                        </p>

                        <p class="card-text">{{ Str::limit(strip_tags($announcement->body), 200) }}</p>

                        <div class="d-flex gap-2">
                            <a href="{{ route('announcement.show', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('label.read_more') }}
                            </a>
                            @if($isAdmin)
                                <a href="{{ route('announcement.edit', $announcement) }}" class="btn btn-sm btn-outline-secondary">
                                    {{ __('label.edit') }}
                                </a>
                                <form action="{{ route('announcement.destroy', $announcement) }}" method="POST"
                                      onsubmit="return confirm('{{ __('label.are_you_sure') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('label.delete') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-muted">{{ __('label.no_announcements') }}</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $announcements->links() }}
    </div>
@endsection
