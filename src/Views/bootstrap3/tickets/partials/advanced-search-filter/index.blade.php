<div id="ticket-advanced-search-filter-container" class="asf__modal asf__ticket">
    <div class="asf__modal-inner">
        <div class="asf__modal-header">
            <h2>Advanced Search</h2>
            <span>Select the filter you want to apply on table.</span>
            <button id="ticket-advanced-search-filter-container-close" class="asf__modal-close"><span class="icon-close"></span></button>
        </div>
        <!-- .asf__modal-header -->
        <div class="asf__modal-body">
            <div class="asf__content">
                @include('ticketit::tickets.partials.advanced-search-filter.active_filters')
                @include('ticketit::tickets.partials.advanced-search-filter.tags')
                @include('ticketit::tickets.partials.advanced-search-filter.user')
                @include('ticketit::tickets.partials.advanced-search-filter.ticket_number')
                @include('ticketit::tickets.partials.advanced-search-filter.ticket_subject')
                @include('ticketit::tickets.partials.advanced-search-filter.ticket_status')
                @include('ticketit::tickets.partials.advanced-search-filter.ticket_priority')
                @include('ticketit::tickets.partials.advanced-search-filter.ticket_age')
            </div>
            <!-- .asf__content -->
        </div>
        <!-- .asf__modal-body -->
    </div>
    <!-- .asf__modal-inner -->
</div><!-- .asf__modal -->
<form id="frm_ticket_asf">
    <div id="ticket_asf_selected_tags">
        @if(is_array(session('ticket_filter_tag_ids')) && count(session('ticket_filter_tag_ids')))
            @foreach(session('ticket_filter_tag_ids') as $ticket_filter_tag_id)
                <input type="hidden" name="hdn_ticket_filter_tag_ids[]" value="{{$ticket_filter_tag_id}}">
            @endforeach
        @endif
    </div>
    <input type="hidden" id="hdn_ticket_filter_user_id" name="hdn_ticket_filter_user_id" value="{{ session('ticket_filter_user_id')? session('ticket_filter_user_id'):0 }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_number" name="hdn_ticket_filter_ticket_number" value="{{ session('ticket_filter_ticket_number')? session('ticket_filter_ticket_number'):'' }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_subject" name="hdn_ticket_filter_ticket_subject" value="{{ session('ticket_filter_ticket_subject')? session('ticket_filter_ticket_subject'):'' }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_status_id" name="hdn_ticket_filter_ticket_status_id" value="{{ session('ticket_filter_ticket_status_id')? session('ticket_filter_ticket_status_id'):0 }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_priority_id" name="hdn_ticket_filter_ticket_priority_id" value="{{ session('ticket_filter_ticket_priority_id')? session('ticket_filter_ticket_priority_id'):0 }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_date_range_type" name="hdn_ticket_filter_ticket_date_range_type" value="{{ session('ticket_filter_ticket_date_range_type')? session('ticket_filter_ticket_date_range_type'):'' }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_date_range_start" name="hdn_ticket_filter_ticket_date_range_start" value="{{ session('ticket_filter_ticket_date_range_start')? session('ticket_filter_ticket_date_range_start'):'' }}">
    <input type="hidden" id="hdn_ticket_filter_ticket_date_range_end" name="hdn_ticket_filter_ticket_date_range_end" value="{{ session('ticket_filter_ticket_date_range_end')? session('ticket_filter_ticket_date_range_end'):'' }}">
</form>