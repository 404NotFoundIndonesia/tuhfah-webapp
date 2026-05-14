@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">
            <a class="text-muted fw-light" href="{{ route('attendance.index') }}">{{ __('menu.attendance') }} /</a>
            {{ __('label.record_attendance') }}
        </h4>
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary text-white fw-medium">
            {{ __('button.back') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-3">
            <form method="GET" action="{{ route('attendance.create') }}" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-semibold">{{ __('field.date') }}</label>
                <input type="date" name="date" class="form-control form-control-sm" style="width:160px"
                       value="{{ $date }}" onchange="this.form.submit()">
            </form>
        </div>

        @if($students->isEmpty())
            <div class="card-body text-muted">{{ __('label.no_active_students') }}</div>
        @else
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('field.name') }}</th>
                            @foreach($statuses as $status)
                                <th class="text-center">{{ __('label.'.$status->value) }}</th>
                            @endforeach
                            <th>{{ __('field.notes') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($students as $i => $student)
                            @php $rec = $existing->get($student->id); @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $student->name }}</td>
                                @foreach($statuses as $status)
                                    <td class="text-center">
                                        <input type="radio"
                                               name="records[{{ $student->id }}][status]"
                                               value="{{ $status->value }}"
                                               class="form-check-input"
                                               @checked(old("records.{$student->id}.status", optional($rec)->status?->value) === $status->value)
                                               required>
                                    </td>
                                @endforeach
                                <td>
                                    <input type="text"
                                           name="records[{{ $student->id }}][notes]"
                                           class="form-control form-control-sm"
                                           value="{{ old("records.{$student->id}.notes", optional($rec)->notes) }}"
                                           placeholder="{{ __('field.notes') }}">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-body">
                    <button type="submit" class="btn btn-primary">{{ __('button.submit') }}</button>
                </div>
            </form>
        @endif
    </div>
@endsection
