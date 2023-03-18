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
	{!! loadCSSFile('/css/ticket-listing.css') !!}
	{!! loadCSSFile('/css/datatable-columns-setting.css') !!}
@stop
                              
@section('content')
	<div class="ticket-system">
    	@include('ticketit::shared.header')
    	@include('ticketit::tickets.index')
		@include('ticketit::tickets.partials.advanced-search-filter.index')
		@include('ticketit::tickets.partials.datatable-columns-setting')
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
	<!-- Select2 Multiple Checkboxes -->
	<script src="{{asset('libs/select2-multi-checkboxes/select2.multi-checkboxes.js')}}"></script>
	<script src="{{asset('libs/toggle-switcher/js/jquery.switcher.js')}}"></script>
	<script>
		let ticket_main_route = `{!! url('/').'/'.$setting->grab('main_route')!!}`;
		let get_tags_url = `{!! route($setting->grab('main_route').'.get-all-tags') !!}`;
		let search_users_url = `{!! route($setting->grab('main_route').'.search-users') !!}`;
		let get_selected_user_detail_url = `{!! route($setting->grab('main_route').'.get-selected-user-detail') !!}`;
		let get_all_ticket_priorities_url = `{!! route($setting->grab('main_route').'.get-all-ticket-priorities') !!}`;
		let get_all_ticket_statuses_url = `{!! route($setting->grab('main_route').'.get-all-ticket-statuses') !!}`;
		let save_datatable_columns_visibility_setting_url = `{!! route($setting->grab('main_route').'.save-datatable-columns-visibility-setting') !!}`;
		let datatable_visible_column_arr = [];
		@if(is_array($datatable_visible_column_arr) && count($datatable_visible_column_arr))
			datatable_visible_column_arr = @json($datatable_visible_column_arr,JSON_PRETTY_PRINT);
		@endif
		var ticket_agent_or_admin=0;
		@if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin)
			ticket_agent_or_admin=1;
		@endif
		var is_completed_tickets_section=0;
		@if($complete)
			is_completed_tickets_section=1;
		@endif
	</script>
	{!! loadJSFile('/js/ticket-listing.js') !!}
	{!! loadJSFile('/js/ticket-tag-create-and-select-in-datatable.js') !!}
	{!! loadJSFile('/js/ticket-listing-columns-setting.js') !!}
	<script>

		var DoActionAfterTimeout = (function (options){
			'use strict';

			var _defaults = {
				input_jquery_object:'',
				callback:''
			};

			options = $.extend(_defaults, options);

			var _timer;                	//timer identifier
			var _interval = 800;  		//time in ms (5 seconds)
			var _old_value='';
			var _current_value='';

			function start(val)
			{
				clearTimeout(_timer);
				if (options.input_jquery_object) {

					_current_value = getValue();

					if (_current_value != _old_value) {
						_timer = setTimeout(done, _interval);
					}
				}
			}

			function getValue(){

				if(options.input_jquery_object.length>1) {
					_current_value = [];
					options.input_jquery_object.each(function(){

						if($(this).is(':checked')) {

							_current_value.push($(this).val());
						}
					});

				}
				else
					_current_value = options.input_jquery_object.val();

				if(!_current_value)
					_current_value='';

				if (_current_value.constructor === Array || _current_value.constructor === Object) {
					_current_value=JSON.stringify(_current_value);
				}

				return _current_value;

			}

			function done () {

				if(options.input_jquery_object) {
					_old_value =  getValue();
				}

				if(options.callback) {
					options.callback();
				}

			}

			return {
				start: start
			};

		});

		function submitDatatableColumnsVisibilitySetting() {

			var token = jQuery("meta[name='csrf-token']").attr("content");

			var columns;

			//columns =jQuery('#toggle-columns-select').val();

			columns = [];
			jQuery('.chk_datatable_columns_setting_section_column_item').each(function(){
				if($(this).is(':checked')) {
					columns.push($(this).val());
				}
			});

			if(!columns)
				columns='';

			setTimeout(function() {
				jQuery.ajax({
					url: save_datatable_columns_visibility_setting_url,
					type: 'POST',
					data: {
						"is_completed_tickets_section": is_completed_tickets_section,
						"columns": columns,
						"_token": token
					},
					success: function (response) {

					}
				});
			},3000);
		}

		var do_action_after_timeout = new DoActionAfterTimeout({
			input_jquery_object:jQuery('.chk_datatable_columns_setting_section_column_item'),
			callback:submitDatatableColumnsVisibilitySetting
		});

		function toggleDirectionsClasses(ele, prevX, prevY, currX, currY, first_scroll_left, first_scroll_top) {

			if(first_scroll_left!=ele.scrollLeft()) {
				if (prevX < currX) {
					ele.addClass('scroll-draggable_move_left').removeClass('scroll-draggable_move_right');
				} else {
					if (prevX === currX) {
						ele.removeClass('scroll-draggable_move_right scroll-draggable_move_left');
					} else {
						ele.addClass('scroll-draggable_move_right').removeClass('scroll-draggable_move_left');
					}
				}
			}

			if(first_scroll_top!=ele.scrollTop()) {
				if (prevY < currY) {
					ele.addClass('scroll-draggable_move_top').removeClass('scroll-draggable_move_bottom');
				} else {
					if (prevY === currY) {
						ele.removeClass('scroll-draggable_move_bottom scroll-draggable_move_top');
					} else {
						ele.addClass('scroll-draggable_move_bottom').removeClass('scroll-draggable_move_top');
					}
				}
			}

		}

		function setDragScrollOnDatatable(){

			const ele = $('#ticket-system-tbl_wrapper').find('.row').eq(1);

 			//ele.css('cursor', 'grab');
			ele.addClass('datatable_not_dragging_scrolling');

			let pos = { top: 0, left: 0, x: 0, y: 0 };

			let old_e_clientX=0;
			let old_e_clientY=0;

			ele.on('mousedown', function(e){

				if(ele.hasClass('datatable_scrollable')) {

	  			//ele.css('cursor', 'grabbing');
				//ele.css('user-select','none');

				ele.removeClass('datatable_not_dragging_scrolling');
				ele.addClass('datatable_dragging_scrolling');

				pos = {
					left: ele.scrollLeft(),
					top: ele.scrollTop(),
					// Get the current mouse position
					x: e.clientX,
					y: e.clientY,
				};

				old_e_clientX=e.clientX;
				old_e_clientY=e.clientY;

				jQuery(document).on('mousemove', function(e){

					// How far the mouse has been moved
					const dx = e.clientX - pos.x;
					const dy = e.clientY - pos.y;

					// Scroll the element
					ele.scrollTop(pos.top - dy);
					ele.scrollLeft(pos.left - dx);

					//toggleDirectionsClasses(ele, old_e_clientX, old_e_clientY, e.clientX, e.clientY, pos.left, pos.top);

					old_e_clientX=e.clientX;
					old_e_clientY=e.clientY;

				});

				jQuery(document).on('mouseup', function(e){

					//ele.css('cursor', 'grab');
					//ele.css('user-select','unset');

					ele.addClass('datatable_not_dragging_scrolling');
					ele.removeClass('datatable_dragging_scrolling');

					jQuery(document).off('mousemove');
					jQuery(document).off('mouseup');

					//ele.removeClass('scroll-draggable_move_left scroll-draggable_move_right scroll-draggable_move_top scroll-draggable_move_bottom');

				});

				/*
				ele.on('mousewheel', function (event, delta) {
					event.preventDefault();
					//this.scrollLeft -= (delta * 30);
					ele.scrollLeft(ele.scrollLeft() -(delta * 30));
				});
				*/

				/*
				jQuery(document).on('mouseleave', function(e){
					elee.css('cursor', 'grab');
				 	//elee.css('user-select','unset');

				 	jQuery(document).off('mousemove');
				 	jQuery(document).off('mouseup');
				});
				*/
				}
			});
		}

        var ticket_tag_create_and_select_in_datatable= new TicketTagCreateAndSelectInDatatable();
		var ticket_datatable_obj;

		$(document).ready(function() {
			//$('.select2').select2();
			initDatatable();

			jQuery('body').on('click', function (e) {
				//did not click a popover toggle or popover
				if (jQuery(e.target).data('toggle') !== 'popover' && jQuery(e.target).parents('.popover.in').length === 0 && jQuery(e.target).parents('.create-tag-modal').length === 0 && !jQuery(e.target).hasClass('create-tag-modal')) {
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
		var datatable_columns_arr;
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

			 ticket_datatable_obj = $('.ticket-system__tbl').DataTable({
				 autoWidth: false,
				processing: false,
				serverSide: true,
				responsive: false,
				//"scrollX": true,
                destroy: true, 
				//dom: 'Blfrtip',
				dom: "<'row'<'col-sm-5' <'column-button-container'B> l ><'col-sm-3 table-top-info'i><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
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
						$('.ticket-system__tbl > tbody').html(
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
					{ data: 'user', name: 'users.name', responsivePriority: 2, width:'200px' },
					@endif			
					{ data: 'subject', name: 'subject', responsivePriority: 2 },
					{ data: 'status', name: 'ticketit_statuses.name', responsivePriority: 2 },
					@if( $u->isAgent() || $u->isAdmin() )
					{ data: 'priority', name: 'ticketit_priorities.name', responsivePriority: 2 },
					@endif
					@if( $u->isAgent() || $u->isAdmin() )
                    { data: 'developer_status', name: 'tickets_developer_status.name', responsivePriority: 2 },
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
                    { data: 'actions', name: 'actions' ,responsivePriority: 1, orderable:false, 'searchable': false},
					@endif
				],
				createdRow: function (row, data, index) {

					if(data.no_follow_up_in_one_day){
						$(row).addClass("ticket-system__no_follow_up_in_one_day");
					}

					if(data.no_resolution_in_three_days){
						$(row).addClass("ticket-system__no_resolution_in_three_days");
					}

					if(data.no_resolution_in_seven_days){
						$(row).addClass("ticket-system__no_resolution_in_seven_days");
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
				"drawCallback": function( settings ) {

					var api = this.api();

					//console.warn(api.rows({page: 'current'}).data());

					var ele = $('#ticket-system-tbl_wrapper').find('.row').eq(1);
	 				var has_horizontal_scrollbar = ele[0].scrollWidth > ele[0].clientWidth;

					if(has_horizontal_scrollbar)
						ele.addClass('datatable_scrollable');

				}
            });

			setDragScrollOnDatatable();

			if(ticket_agent_or_admin) {
				jQuery('#ticket-system-tbl_length').prepend('<button class="custom-btn columns-btn table-columns-form-popup-open"  title="Columns" href="javascript:void(0)">Columns</button>');
			}

			datatable_columns_arr = ticket_datatable_obj.settings().init().columns;

			if (datatable_visible_column_arr.length) {

				var datatable_column_index_arr=[];

				var total_options=0;

				jQuery('#toggle-columns-select').find("option").each(function (i, selected) {

					total_options++;
				});

				if(total_options==(datatable_visible_column_arr.length)){
					datatable_visible_column_arr.push('show_all');
				}

				ticket_datatable_obj.columns().every(function(index) {

					if(jQuery.inArray(datatable_columns_arr[index].data, datatable_visible_column_arr) !== -1){
						datatable_column_index_arr.push(index);
					}
				});

				ticket_datatable_obj.columns().visible(false);
				ticket_datatable_obj.columns(datatable_column_index_arr).visible(true);

				setTimeout(function(){
					jQuery('#toggle-columns-select').val(datatable_visible_column_arr).trigger('change');
				},5000);

			}
			else {
				var datatable_column_index_arr=[];
				datatable_visible_column_arr.push('show_all');
				ticket_datatable_obj.columns().every(function(index) {

					datatable_column_index_arr.push(index);
					datatable_visible_column_arr.push(datatable_columns_arr[index].data);

				});

				ticket_datatable_obj.columns().visible(false);
				ticket_datatable_obj.columns(datatable_column_index_arr).visible(true);

				setTimeout(function(){
					jQuery('#toggle-columns-select').val(datatable_visible_column_arr).trigger('change');
				},5000);

			}

			var total_columns = 0;
			var total_visible_columns = 0;
			jQuery('.chk_datatable_columns_setting_section_column_item').each(function(){
				total_columns++;
				if(jQuery.inArray($(this).val(), datatable_visible_column_arr) !== -1){
					total_visible_columns++;
					$(this).prop('checked', true);
					$(this).parent().find('.ui-switcher').attr('aria-checked',true);

				}
			});

			if(total_columns==total_visible_columns) {
				var chk_datatable_column_show_hide_all = $('.chk_datatable_column_show_hide_all');
				if (!chk_datatable_column_show_hide_all.is(':checked')) {

					chk_datatable_column_show_hide_all.prop('checked', true);
					chk_datatable_column_show_hide_all.parent().find('.ui-switcher').attr('aria-checked', true);

				}
			}

/*
			if(localStorage.getItem('ticket_column_visible')) {

				let ticket_column_visible = localStorage.getItem('ticket_column_visible');
				let tickets_table = $('.table').DataTable();
				tickets_table.columns().visible(false)
				tickets_table.columns(ticket_column_visible).visible(true);

                // convert to array
                let column_arr = ticket_column_visible.split(',');

                // set active columns to checked
                /*column_arr.forEach((item) => {
                    $(`input[data-column='${item}']`).prop('checked', true);
                });*//*

				jQuery('#toggle-columns-select').val(column_arr).trigger('change');

			}
			else {
				ticket_datatable_obj.columns().visible(true);
				let result = ticket_datatable_obj.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result);

				// convert to array
				let column_arr = result;

				jQuery('#toggle-columns-select').val(column_arr).trigger('change');

			}
*/
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

		jQuery('#toggle-columns-select').on('change', function(e) {
			do_action_after_timeout1.start();
		});

		jQuery('#toggle-columns-select').on('select2:selecting', function(e) {

			var cur = e.params.args.data.id;

			if(cur=='show_all'){
				var datatable_column_index_arr=[];
				datatable_visible_column_arr.push('show_all');
				ticket_datatable_obj.columns().every(function(index) {

					datatable_column_index_arr.push(index);
					datatable_visible_column_arr.push(datatable_columns_arr[index].data);

				});

				ticket_datatable_obj.columns().visible(false);
				ticket_datatable_obj.columns(datatable_column_index_arr).visible(true);

				//setTimeout(function(){
					jQuery('#toggle-columns-select').val(datatable_visible_column_arr).trigger('change');
					jQuery('#select2-toggle-columns-select-results li').each(function(){
						$(this).attr('aria-selected',true);
					});
				//},5000);
			}
			else {

				ticket_datatable_obj.columns().every(function(index) {

					if(datatable_columns_arr[index].data==cur){
						let column = ticket_datatable_obj.column(index);
						column.visible( ! column.visible() );
					}
				});

				var total_options=0;
				var selected_option=0;
				$(e.currentTarget).find("option").each(function (i, selected) {
					if($(this).is(':selected')){
						selected_option++;
					}
					total_options++;
				});

				if(total_options==(selected_option+2)){

					var va=[];
					$(e.currentTarget).find("option:selected").each(function(i, selected){
						va[i] = $(selected).val();
					});
					va.push('show_all');

					$(e.target).val(va).trigger('change');
					jQuery('#select2-toggle-columns-select-results li').eq(0).attr('aria-selected',true);
				}

			}


			let data = cur;
			/*let tickets_table = $('.table').DataTable();

			if(data == "*") {
				tickets_table.columns().visible(true);
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)
			} else {
				let column = tickets_table.column(data);
				column.visible( ! column.visible() );
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)
			}*/




			//console.warn(old);
			//$(e.target).val(old).trigger('change');
			$(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'true');
			//select2-results__option--highlighted

			//setTimeout(function(){
				//console.warn('run');
				//ticket_datatable_obj.responsive.rebuild();
				//ticket_datatable_obj.responsive.recalc();
				//ticket_datatable_obj.columns.adjust().responsive.recalc();

				//$($.fn.dataTable.tables( true ) ).css('width', '100%');
				//$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
			//},3000);
			ticket_datatable_obj.columns.adjust().draw();
		});

		jQuery('#toggle-columns-select').on('select2:unselecting', function(e) {

			var cur = e.params.args.data.id;

			if(cur=='show_all'){

				var va = [];
				$(e.currentTarget).find("option").each(function (i, selected) {
					va[i] = $(selected).val();
				});

				var datatable_column_index_arr=[];
				//datatable_visible_column_arr.push('show_all');
				ticket_datatable_obj.columns().every(function(index) {

					if(jQuery.inArray(datatable_columns_arr[index].data, va) !== -1){
						datatable_column_index_arr.push(index);
					}

					//datatable_column_index_arr.push(index);
					//datatable_visible_column_arr.push(datatable_columns_arr[index].data);

				});

				//ticket_datatable_obj.columns().visible(false);
				ticket_datatable_obj.columns(datatable_column_index_arr).visible(false);

				//setTimeout(function(){
					jQuery('#toggle-columns-select').val([]).trigger('change');
					jQuery('#select2-toggle-columns-select-results li').each(function(){
						$(this).attr('aria-selected',false);
					});
				//},5000);
			}
			else {

				ticket_datatable_obj.columns().every(function(index) {

					if(datatable_columns_arr[index].data==cur){

						let column = ticket_datatable_obj.column(index);
						column.visible( ! column.visible() );
					}
				});

				var va = [];
				$(e.currentTarget).find("option:selected").each(function (i, selected) {
					va[i] = $(selected).val();
				});

				va = jQuery.grep(va, function (value) {
					return value != 'show_all';
				});

				$(e.target).val(va).trigger('change');
				jQuery('#select2-toggle-columns-select-results li').eq(0).attr('aria-selected',false);


			}


			/*let tickets_table = $('.table').DataTable();

			if(data == "*") {
				tickets_table.columns().visible(true);
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)
			} else {
				let column = tickets_table.column(data);
				column.visible( ! column.visible() );
				let result = tickets_table.columns().visible().reduce((a, v, i) => v ? [...a, i] : a, [])
				localStorage.setItem('ticket_column_visible', result)
			}*/




			//$(e.target).val(old).trigger('change');
			$(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'false');
			//select2-results__option--highlighted

			//setTimeout(function(){
				//console.warn('run');
				//ticket_datatable_obj.responsive.rebuild();
				//ticket_datatable_obj.responsive.recalc();
				//ticket_datatable_obj.columns.adjust().responsive.recalc();

				//$($.fn.dataTable.tables( true ) ).css('width', '100%');
				//$($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
			//},3000);
			ticket_datatable_obj.columns.adjust().draw();
		});
                                                           
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
