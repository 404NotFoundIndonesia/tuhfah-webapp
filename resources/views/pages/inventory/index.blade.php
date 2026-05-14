@extends('layouts.dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="py-3 mb-0">{{ __('menu.inventory') }}</h4>
        @if (auth()->user()->isRole(\App\Enum\Role::ADMINISTRATOR))
            <a href="{{ route('inventory.create') }}" class="btn btn-primary text-white fw-medium">
                + {{ __('label.new') }}
            </a>
        @endif
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <label class="form-label mb-0">{{ __('field.condition') }}:</label>
            <select id="condition-filter" class="form-select form-select-sm" style="width:180px">
                <option value="">{{ __('label.all') ?? 'All' }}</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition->value }}">{{ __('label.'.$condition->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="table-responsive pb-3 text-nowrap">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('field.name') }}</th>
                    <th>{{ __('field.quantity') }}</th>
                    <th>{{ __('field.condition') }}</th>
                    <th>{{ __('field.acquisition_date') }}</th>
                    <th>{{ __('field.notes') }}</th>
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
                {data: 'name', name: 'name'},
                {data: 'quantity', name: 'quantity'},
                {data: 'condition', name: 'condition'},
                {data: 'acquisition_date', name: 'acquisition_date'},
                {data: 'notes', name: 'notes', orderable: false},
                {data: 'action', orderable: false, searchable: false},
            ],
            order: [[0, 'asc']],
            dom: '<"row px-4 my-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row px-4 my-3"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
            language: {url: `/assets/vendor/libs/datatables/language/${locale}.json`}
        });

        document.getElementById('condition-filter').addEventListener('change', function () {
            table.column(2).search(this.value).draw();
        });
    </script>
@endpush
