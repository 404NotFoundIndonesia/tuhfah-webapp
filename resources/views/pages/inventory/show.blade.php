@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('inventory.index') }}">{{ __('menu.inventory') }} /</a>
            {{ $inventory->name }}
        </h4>
        <div class="d-flex gap-2">
            @if (auth()->user()->isRole(\App\Enum\Role::ADMINISTRATOR))
                <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning text-white fw-medium">
                    {{ __('label.edit') }}
                </a>
            @endif
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary text-white fw-medium">
                {{ __('button.back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>{{ __('field.name') }}</th>
                            <td>{{ $inventory->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('field.quantity') }}</th>
                            <td>{{ $inventory->quantity }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('field.condition') }}</th>
                            <td>{{ __('label.'.$inventory->condition->value) }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('field.acquisition_date') }}</th>
                            <td>{{ $inventory->acquisition_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('field.notes') }}</th>
                            <td>{{ $inventory->notes ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        @if (auth()->user()->isRole(\App\Enum\Role::ADMINISTRATOR))
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('label.log_usage') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('inventory.log', $inventory) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="type" class="form-label">{{ __('field.type') }} <abbr title="{{ __('label.required') }}" class="initialism text-danger">*</abbr></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="usage" @selected(old('type') === 'usage')>{{ __('label.usage') }}</option>
                                    <option value="disposal" @selected(old('type') === 'disposal')>{{ __('label.disposal') }}</option>
                                </select>
                                <span class="error invalid-feedback">{{ $errors->first('type') }}</span>
                            </div>
                            <div class="mb-3">
                                <label for="quantity_changed" class="form-label">{{ __('field.quantity_changed') }} <abbr title="{{ __('label.required') }}" class="initialism text-danger">*</abbr></label>
                                <input type="number" name="quantity_changed" id="quantity_changed"
                                    class="form-control @error('quantity_changed') is-invalid @enderror"
                                    value="{{ old('quantity_changed', 1) }}" min="1" max="{{ $inventory->quantity }}">
                                <span class="error invalid-feedback">{{ $errors->first('quantity_changed') }}</span>
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="form-label">{{ __('field.reason') }} <abbr title="{{ __('label.required') }}" class="initialism text-danger">*</abbr></label>
                                <input type="text" name="reason" id="reason"
                                    class="form-control @error('reason') is-invalid @enderror"
                                    value="{{ old('reason') }}">
                                <span class="error invalid-feedback">{{ $errors->first('reason') }}</span>
                            </div>
                            <button type="submit" class="btn btn-danger">{{ __('button.submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('label.usage_history') }}</h5>
        </div>
        <div class="table-responsive pb-3">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('field.date') }}</th>
                        <th>{{ __('field.type') }}</th>
                        <th>{{ __('field.quantity_changed') }}</th>
                        <th>{{ __('field.reason') }}</th>
                        <th>{{ __('field.recorded_by') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventory->logs->sortByDesc('created_at') as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>{{ __('label.'.$log->type) }}</td>
                            <td>{{ $log->quantity_changed }}</td>
                            <td>{{ $log->reason }}</td>
                            <td>{{ $log->recordedBy?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('label.no_inventory_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
