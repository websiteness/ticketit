@extends($master)

@section('page')
    {{ trans('ticketit::lang.index-title') }}
@stop

@section('header_styles')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
	<style>
		.filters-panel .form-control {
			height: 32px !important;
		}
	</style>
@stop

@section('content')
    @include('ticketit::shared.header')
    @include('ticketit::tickets.index')
@stop

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script src="//cdn.datatables.net/v/bs/dt-{{ Kordy\Ticketit\Helpers\Cdn::DataTables }}/r-{{ Kordy\Ticketit\Helpers\Cdn::DataTablesResponsive }}/datatables.min.js"></script>
	<script>
		$(document).ready(function() {
			$('.select2').select2();
		});

		initDatatable();

		function initDatatable(filter = null) {
			let url = '{!! route($setting->grab('main_route').'.data', $complete) !!}';

			if(filter) {
				url = url + filter;
			}

            // check if closed tickets are hidden
            if($('#filter_hide_closed_tickets:checked').length) {
                if(filter) {
                    url = url + '&filter_hide_closed_tickets=1'
                } else {
                    url = url + '?filter_hide_closed_tickets=1'
                }
            }

			$('.table').DataTable({
				processing: false,
				serverSide: true,
				responsive: true,
				destroy: true,
				pageLength: {{ $setting->grab('paginate_items') }},
				lengthMenu: {{ json_encode($setting->grab('length_menu')) }},
				ajax: url,
				language: {
					decimal:        "{{ trans('ticketit::lang.table-decimal') }}",
					emptyTable:     "{{ trans('ticketit::lang.table-empty') }}",
					info:           "{{ trans('ticketit::lang.table-info') }}",
					infoEmpty:      "{{ trans('ticketit::lang.table-info-empty') }}",
					infoFiltered:   "{{ trans('ticketit::lang.table-info-filtered') }}",
					infoPostFix:    "{{ trans('ticketit::lang.table-info-postfix') }}",
					thousands:      "{{ trans('ticketit::lang.table-thousands') }}",
					lengthMenu:     "{{ trans('ticketit::lang.table-length-menu') }}",
					loadingRecords: "{{ trans('ticketit::lang.table-loading-results') }}",
					processing:     "{{ trans('ticketit::lang.table-processing') }}",
					search:         "{{ trans('ticketit::lang.table-search') }}",
					zeroRecords:    "{{ trans('ticketit::lang.table-zero-records') }}",
					paginate: {
						first:      "{{ trans('ticketit::lang.table-paginate-first') }}",
						last:       "{{ trans('ticketit::lang.table-paginate-last') }}",
						next:       "{{ trans('ticketit::lang.table-paginate-next') }}",
						previous:   "{{ trans('ticketit::lang.table-paginate-prev') }}"
					},
					aria: {
						sortAscending:  "{{ trans('ticketit::lang.table-aria-sort-asc') }}",
						sortDescending: "{{ trans('ticketit::lang.table-aria-sort-desc') }}"
					},
				},
				columns: [
					{ data: 'id', name: 'ticketit.id' },
					{ data: 'subject', name: 'subject' },
					{ data: 'status', name: 'ticketit_statuses.name' },
					{ data: 'updated_at', name: 'ticketit.updated_at' },
					{ data: 'agent', name: 'users.name' },
					@if( $u->isAgent() || $u->isAdmin() )
						{ data: 'priority', name: 'ticketit_priorities.name' },
						{ data: 'owner', name: 'users.name' },
						{ data: 'category', name: 'ticketit_categories.name' }
					@endif
				]
			});
		}

		function filterTickets() {
			let user = document.getElementById('filter_owner').value;
			let status = document.getElementById('filter_status').value;

			let query_string = `?user=${user}&status=${status}`;

			initDatatable(query_string);
		}

		function clearFilters() {
			$('#filter_owner').val('').trigger('change');
			document.getElementById('filter_status').value = '';
			
			initDatatable();
		}
	</script>
@append
