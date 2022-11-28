@extends($master)

@section('page')
    {{ trans('ticketit::lang.index-title') }}
@stop

@section('header_styles')
	<!--<link href="//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />-->
	<link href="{{asset('libs/select2/dist/css/select2.min.css')}}" rel="stylesheet">
	<link href="//cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css"> </link>
	<!-- Daterangepicker -->
	<link href="{{asset('libs/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
	<link href="{{asset('css/ticket-listing.css')}}" rel="stylesheet">
@stop
                              
@section('content')
	<div class="ticket-system">
    	@include('ticketit::shared.header')
    	@include('ticketit::tickets.index')
		@include('ticketit::tickets.partials.advanced-search-filter.index')
	</div>
@stop
                                                                                    
@section('footer')
	<!--<script src="//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>-->
	<script src="{{asset('libs/select2/dist/js/select2.full.min.js')}}"></script>
	<script src="//cdn.datatables.net/v/bs/dt-{{ Kordy\Ticketit\Helpers\Cdn::DataTables }}/r-{{ Kordy\Ticketit\Helpers\Cdn::DataTablesResponsive }}/datatables.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="//cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
	<script src="//cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
	<!-- Daterangepicker -->
	<script src="{{asset('libs/bootstrap-daterangepicker/moment.min.js')}}"></script>
	<script src="{{asset('libs/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
	<script>
		let ticket_main_route = `{!! url('/').'/'.$setting->grab('main_route')!!}`;
		let get_tags_url = `{!! route($setting->grab('main_route').'.get-all-tags') !!}`;
		let get_users_url = `{!! route($setting->grab('main_route').'.get-all-users') !!}`;
		let get_all_ticket_priorities_url = `{!! route($setting->grab('main_route').'.get-all-ticket-priorities') !!}`;
		let get_all_ticket_statuses_url = `{!! route($setting->grab('main_route').'.get-all-ticket-statuses') !!}`;
	</script>
	<script src="{{asset('js/ticket-listing.js')}}"></script>
	<script src="{{asset('js/ticket-tag-create-and-select-in-datatable.js')}}"></script>
	<script>

        var ticket_tag_create_and_select_in_datatable= new TicketTagCreateAndSelectInDatatable();
		var ticket_datatable_obj;

		$(document).ready(function() {
			//$('.select2').select2();
			initDatatable();

			jQuery('body').on('click', function (e) {
				//did not click a popover toggle or popover
				if (jQuery(e.target).data('toggle') !== 'popover' && jQuery(e.target).parents('.popover.in').length === 0) {
					jQuery('[data-toggle="popover"]').popover('hide');
				}
			});

			jQuery(document).on('show.bs.popover', function () {
				jQuery('.popover').not(this).popover('hide');
			});

			jQuery('body').on('hidden.bs.popover', function (e) {
				jQuery(e.target).data("bs.popover").inState = {click: false, hover: false, focus: false}
			});

			$( "body" ).on( "click", ".tag-remove", function() {

                let $elem = jQuery(this);
				ticket_tag_create_and_select_in_datatable.updateTag($elem.data('id'), $elem.data('name'), 'remove');

			});
		});

        function initDatatable(filter = null) {

            let btn_search_filter = document.getElementById('btn_search_filter');
            
            if(btn_search_filter)
            {
                document.getElementById('btn_search_filter').innerText = "Searching...";
            }

			let url = `{!! route($setting->grab('main_route').'.data', $complete) !!}`;

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

			 ticket_datatable_obj = $('.table').DataTable({
				processing: false,
				serverSide: true,
				responsive: true,
                destroy: true, 
				dom: 'Blfrtip',
                buttons: [
                    //'colvis',
					//'csvHtml5',
                ],
				pageLength: {{ $setting->grab('paginate_items') }},
				lengthMenu: {{ json_encode($setting->grab('length_menu')) }},
				ajax: {
					url: url,
					beforeSend: function(){
						// Here, manually add the loading message.
						$('.table > tbody').html(
                            '<tr class="odd">' +
                            '<td valign="top" colspan="15" class="dataTables_empty">Loading&hellip;</td>' +
                            '</tr>'
						);
					},
					data: function (d) {

						jQuery('.filter-loader').show();

						d.custom_filters = getFormIndexedData(jQuery('#frm_ticket_asf'));
					},
					dataSrc: function (response) {

						if (typeof response.tags!="undefined" && response.tags !== null) {
							ticket_tag_create_and_select_in_datatable.tags = response.tags;
						}

						return response.data;
					}
				},
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
					{ data: 'id', name: 'ticketit.id' ,responsivePriority: 1},
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'owner_info', name: 'users.name', responsivePriority: 2, width:'200px' },
					@endif			
					{ data: 'subject', name: 'subject', responsivePriority: 2 },
					{ data: 'status', name: 'ticketit_statuses.name', responsivePriority: 2 },
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'priority', name: 'ticketit_priorities.name', responsivePriority: 2 },
					@endif
					@if( $u->isAgent() || $u->isAdmin() )
                    { data: 'dev_status', name: 'tickets_developer_status.name', responsivePriority: 2 },
					@endif
					{ data: 'tags', name: 'tags', width:'300px' ,responsivePriority: 2, orderable:false},
					{ data: 'updated_at', name: 'ticketit.updated_at', responsivePriority: 2 },
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'agent', name: 'users.name', responsivePriority: 2 },
					{ data: 'category', name: 'ticketit_categories.name', responsivePriority: 2 },
					@endif
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'last_reply', name: 'ticketit.last_reply', responsivePriority: 2 },
					@endif
                    { data: 'zone', name: 'zone', responsivePriority: 2 },
					@if( !$complete)
                    { data: 'resolved', name: 'resolved' ,responsivePriority: 1, orderable:false, 'searchable': false},
					@endif
				],
				createdRow: function (row, data, index) {

					if(data.no_follow_up_in_one_day){
						$(row).addClass("ticket-system__yellow");
					}

					if(data.no_resolution_in_three_days){
						$(row).addClass("ticket-system__red");
					}

				},
				@if( $u->isAgent() || $u->isAdmin() )
                columnDefs: [
                    {'searchable': false, 'targets': [5,10,11]}
                ],
				@else
				 columnDefs: [
					 {'searchable': false, 'targets': [5]}
				 ],
				@endif	
            });

            ticket_datatable_obj.columns().visible(true);
            let result = ticket_datatable_obj.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
            localStorage.setItem('ticket_column_visible', result);
                                			
			if(localStorage.getItem('ticket_column_visible')) {

				let ticket_column_visible = localStorage.getItem('ticket_column_visible');
				let tickets_table = $('.table').DataTable();
				tickets_table.columns().visible(false)
				tickets_table.columns(ticket_column_visible).visible(true);

                // convert to array
                let column_arr = ticket_column_visible.split(',');

                // set active columns to checked
                column_arr.forEach((item) => {
                    $(`input[data-column='${item}']`).prop('checked', true);
                });

			}
                                                               
            if(btn_search_filter)
            {
                closeNav();
                document.getElementById('btn_search_filter').innerText = "Search";
            }

			ticket_datatable_obj.on('draw column-visibility responsive-resize column-visibility.dt', function () {

				jQuery('.tag-popup').popover({
					html: true,
					container: 'body',
					content: function () {
						return ticket_tag_create_and_select_in_datatable.getAvailableTags(jQuery(this));
					},
					placement: 'auto right'
				});


			});

			ticket_datatable_obj.on( 'responsive-resize', function ( e, datatable, columns ) {
				var count = columns.reduce( function (a,b) {
					return b === false ? a+1 : a;
				}, 0 );

				//console.warn( count +' column(s) are hidden' );
			} );

			ticket_datatable_obj.on( 'responsive-display', function ( e, datatable, row, showHide, update ) {
				//console.warn( 'Details for row '+row.index()+' '+(showHide ? 'shown' : 'hidden') );

				jQuery('.tag-popup').popover({
					html: true,
					container: 'body',
					content: function () {
						return ticket_tag_create_and_select_in_datatable.getAvailableTags(jQuery(this));
					},
					placement: 'auto right'
				});
			});
		}
                                                           
		$('.ticket_dropdown_option').click(function (e) {
			let data = $(this).attr('data-column');	
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
			let tags = $(".select2-tag").val();

			console.log(tags)
			let filter_hide_closed_tickets = $('#filter_hide_closed_tickets').is(':checked'); 
	
			let query_string = `?user=${user}&status=${status}&message=${message}&sub_category=${sub_category}&last_reply=${last_reply}&tags=${tags}&filter_hide_closed_tickets=${filter_hide_closed_tickets}`;
			
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
