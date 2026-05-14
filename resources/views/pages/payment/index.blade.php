@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.payment') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('payment.export', ['format' => 'xlsx']) }}" class="btn btn-success text-white fw-medium">
                {{ __('button.export_xlsx') }}
            </a>
            <a href="{{ route('payment.export', ['format' => 'pdf']) }}" class="btn btn-danger text-white fw-medium">
                {{ __('button.export_pdf') }}
            </a>
            <a href="{{ route('payment.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('label.new') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive pb-3 text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.name') }}</th>
                    <th>{{ __('field.period') }}</th>
                    <th>{{ __('field.amount') }}</th>
                    <th>{{ __('field.status') }}</th>
                    <th>{{ __('field.due_date') }}</th>
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
                {data: 'student_name', name: 'student_name'},
                {data: 'period', name: 'period'},
                {data: 'amount', name: 'amount', searchable: false},
                {data: 'status', name: 'status'},
                {data: 'due_date', name: 'due_date'},
                {data: 'paid_at', name: 'paid_at', searchable: false},
                {data: 'action', orderable: false, searchable: false},
            ],
            order: [[4, 'desc']],
            dom: '<"row px-4 my-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row px-4 my-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
            language: {url: `/assets/vendor/libs/datatables/language/${locale}.json`}
        });
    </script>
@endpush
