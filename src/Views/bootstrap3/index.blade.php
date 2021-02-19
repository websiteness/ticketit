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
        .ticket-subject a {
            color: #28b999 !important;
            text-decoration: underline;
            font-weight: bold;
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

            let btn_search_filter = document.getElementById('btn_search_filter');
            
            if(btn_search_filter)
            {
                document.getElementById('btn_search_filter').innerText = "Searching...";
            }

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
                buttons: [
                    'colvis'
                ],
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
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'last_reply', name: 'ticketit.last_reply' },
					@endif
					{ data: 'updated_at', name: 'ticketit.updated_at' },
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'agent', name: 'users.name' },
					{ data: 'priority', name: 'ticketit_priorities.name' },
					{ data: 'owner', name: 'users.name' },
					{ data: 'category', name: 'ticketit_categories.name' },
					@endif
					{ data: 'resolved', name: 'resolved' },
				]
            });
			
			if(localStorage.getItem('ticket_column_visible')) {
				let ticket_column_visible = localStorage.getItem('ticket_column_visible');
				let tickets_table = $('.table').DataTable();
				tickets_table.columns().visible(false)
				tickets_table.columns(ticket_column_visible).visible(true);
			}

            if(btn_search_filter)
            {
                closeNav();
                document.getElementById('btn_search_filter').innerText = "Search";
            }
		}

		// $('select#tickets_show').change( function (e) {
		// 	let data = $(this).children(":selected").attr('data-column');
		// 	console.log(data)
		// 	let tickets_table = $('.table').DataTable();
		// 	if(data == "all") {
		// 		tickets_table.columns().visible(true);
		// 		let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
		// 		localStorage.setItem('ticket_column_visible', result)
		// 	} else {
		// 		let column = tickets_table.column(data);
		// 		column.visible( ! column.visible() );
		// 		let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
		// 		localStorage.setItem('ticket_column_visible', result)		
		// 	}
		// });
		
		// let dropdownvalue = '';
		
		// $('select#tickets_show').change(function (e) {
		// 	let data = $(this).children(":selected").attr('data-column');	
		// 	let tickets_table = $('.table').DataTable();
			
		// 	if(data == "all") {
		// 		tickets_table.columns().visible(true);
		// 		let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
		// 		localStorage.setItem('ticket_column_visible', result)
		// 	} else {	
		// 		let column = tickets_table.column(data);
		// 		column.visible( ! column.visible() );
		// 		let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
		// 		localStorage.setItem('ticket_column_visible', result)		
		// 	}

		// 	dropdownvalue = data;
		// });


		

		$('.ticket_dropdown_option').click(function (e) {
			
			let data = $(this).attr('data-column');
			// console.log(data)
			
			
			let tickets_table = $('.table').DataTable();
			if(data == "*") {
				tickets_table.columns().visible(true);
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)
			} else {
				let column = tickets_table.column(data);
				column.visible( ! column.visible() );
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)			
			}

		});
		


		function filterTickets() {
			let user = document.getElementById('filter_owner').value;
			let status = document.getElementById('filter_status').value;
			let message = document.getElementById('filter_message').value;
			let sub_category = document.getElementById('filter_sub_category').value;
			let last_reply = document.getElementById('filter_last_reply').value;

			let query_string = `?user=${user}&status=${status}&message=${message}&sub_category=${sub_category}&last_reply=${last_reply}`;

			initDatatable(query_string);
		}

		function clearFilters() {
			$('#filter_owner').val('').trigger('change');
			document.getElementById('filter_status').value = '';
			document.getElementById('filter_message').value = '';
			
			initDatatable();
		}
	</script>
@append
