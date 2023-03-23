<div id="ticket-advanced-search-filter-container" class="asf__modal">
    <div class="asf__modal-inner">
        <div class="asf__modal-header">
            <h2>Advanced Search</h2>
            <span>Select the filter you want to apply on table.</span>
            <button class="asf__modal-close"><span class="icon-close"></span></button>
        </div>
        <!-- .asf__modal-header -->
        <div class="asf__modal-body">
            <div class="asf__content">
                @include('ticketit::admin.dashboard.partials.advanced-search-filter.active_filters')
                @include('ticketit::admin.dashboard.partials.advanced-search-filter.ignore_users')
                @include('ticketit::admin.dashboard.partials.advanced-search-filter.user')
                @include('ticketit::admin.dashboard.partials.advanced-search-filter.ticket_age')
                @include('ticketit::admin.dashboard.partials.advanced-search-filter.others')
            </div>
            <!-- .asf__content -->
        </div>
        <!-- .asf__modal-body -->
    </div>
    <!-- .asf__modal-inner -->
</div><!-- .asf__modal -->
<form id="frm_ticket_asf">
    <div id="ticket_dashboard_asf_selected_tags">
        @if(is_array(session('ticket_dashboard_filter_ignore_user_ids')) && count(session('ticket_dashboard_filter_ignore_user_ids')))
            @foreach(session('ticket_dashboard_filter_ignore_user_ids') as $ticket_dashboard_filter_ignore_user_id)
                <input type="hidden" name="hdn_ticket_dashboard_filter_ignore_user_ids[]" value="{{$ticket_dashboard_filter_ignore_user_id}}">
            @endforeach
        @endif
    </div>
    <input type="hidden" id="hdn_ticket_dashboard_filter_user_id" name="hdn_ticket_dashboard_filter_user_id" value="{{ session('ticket_dashboard_filter_user_id')? session('ticket_dashboard_filter_user_id'):0 }}">
    <input type="hidden" id="hdn_ticket_dashboard_filter_ticket_date_range_type" name="hdn_ticket_dashboard_filter_ticket_date_range_type" value="{{ session('ticket_dashboard_filter_ticket_date_range_type')? session('ticket_dashboard_filter_ticket_date_range_type'):'' }}">
    <input type="hidden" id="hdn_ticket_dashboard_filter_ticket_date_range_start" name="hdn_ticket_dashboard_filter_ticket_date_range_start" value="{{ session('ticket_dashboard_filter_ticket_date_range_start')? session('ticket_dashboard_filter_ticket_date_range_start'):'' }}">
    <input type="hidden" id="hdn_ticket_dashboard_filter_ticket_date_range_end" name="hdn_ticket_dashboard_filter_ticket_date_range_end" value="{{ session('ticket_dashboard_filter_ticket_date_range_end')? session('ticket_dashboard_filter_ticket_date_range_end'):'' }}">
    <input type="hidden" id="hdn_ticket_dashboard_filter_ignore_test_accounts" name="hdn_ticket_dashboard_filter_ignore_test_accounts" value="{{ session('ticket_dashboard_filter_ignore_test_accounts')? session('ticket_dashboard_filter_ignore_test_accounts'):0 }}">

</form>