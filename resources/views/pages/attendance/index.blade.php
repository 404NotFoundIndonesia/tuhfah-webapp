@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.attendance') }}</h4>
        <div>
            <a href="{{ route('attendance.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('label.record_attendance') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex gap-2 align-items-center">
            <select id="filter-status" class="form-select form-select-sm" style="width: auto;">
                <option value="">— {{ __('field.status') }} —</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ __('label.'.$status->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="table-responsive pb-3 text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.date') }}</th>
                    <th>{{ __('field.name') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.notes') }}</th>
                    <th>{{ __('field.recorded_by') }}</th>
                    <th width="15px"></th>
                </tr>
                </thead>
                <tbody class="table-border-bottom-0"></tbody>
            </table>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables/datatables.min.css') }}"/>
@endpush

@push('script')
    <script src="{{ asset('assets/vendor/libs/datatables/datatables.min.js') }}"></script>
    <script>
        const locale = document.querySelector('html').getAttribute('lang');

        const table = $('table.table').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.location.href,
            columns: [
                {data: 'date', name: 'date'},
                {data: 'student_name', name: 'student_name', searchable: false, orderable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'notes', name: 'notes', orderable: false},
                {data: 'recorder', name: 'recorder', searchable: false, orderable: false},
                {data: 'action', orderable: false, searchable: false},
            ],
            order: [[0, 'desc']],
            dom: '<"row px-4 my-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row px-4 my-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
            language: {url: `/assets/vendor/libs/datatables/language/${locale}.json`}
        });

        document.getElementById('filter-status').addEventListener('change', function () {
            table.column(2).search(this.value).draw();
        });
    </script>
@endpush
