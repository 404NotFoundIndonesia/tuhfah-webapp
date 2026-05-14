@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.honorarium') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('honorarium.export', ['format' => 'xlsx']) }}" class="btn btn-success text-white fw-medium">
                {{ __('button.export_xlsx') }}
            </a>
            <a href="{{ route('honorarium.export', ['format' => 'pdf']) }}" class="btn btn-danger text-white fw-medium">
                {{ __('button.export_pdf') }}
            </a>
            <a href="{{ route('honorarium.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('label.new') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive pb-3 text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.teacher_id') }}</th>
                    <th>{{ __('field.period') }}</th>
                    <th>{{ __('field.amount') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.paid_at') }}</th>
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

        $('table.table').DataTable({
            processing: true,
            serverSide: true,
            ajax: window.location.href,
            columns: [
                {data: 'teacher_name', name: 'teacher_name'},
                {data: 'period', name: 'period'},
                {data: 'amount', name: 'amount', searchable: false},
                {data: 'status', name: 'status'},
                {data: 'paid_at', name: 'paid_at', searchable: false},
                {data: 'action', orderable: false, searchable: false},
            ],
            order: [[1, 'desc']],
            dom: '<"row px-4 my-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row px-4 my-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
            language: {url: `/assets/vendor/libs/datatables/language/${locale}.json`}
        });
    </script>
@endpush
