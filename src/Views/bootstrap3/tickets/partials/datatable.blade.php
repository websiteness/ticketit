<table id="ticket-system-tbl" class="ticket-system__tbl table table-striped table-bordered dt-responsive nowrap" style="width:100%">
    <thead>
    <tr>
        <th>Number</th>
        @if( $u->isAgent() || $u->isAdmin() )
        <th>User</th>
        @endif
        <th>{{ trans('ticketit::lang.table-subject') }}</th>
        <th>Ticket Status</th>
        @if( $u->isAgent() || $u->isAdmin() )
        <th>{{ trans('ticketit::lang.table-priority') }}</th>
        @endif
        @if( $u->isAgent() || $u->isAdmin() )
        <th>Dev Status</th>
        <th>Tags</th>
        @endif
        <th>{{ trans('ticketit::lang.table-last-updated') }}</th>
        @if( $u->isAgent() || $u->isAdmin() )
        <th>{{ trans('ticketit::lang.table-agent') }}</th>
        <th>{{ trans('ticketit::lang.table-category') }}</th>
        @endif
        @if( $u->isAgent() || $u->isAdmin() )
        <th>Last Reply</th>
        @endif
        <th>Zone</th>
        @if( !$complete)
        <th>Actions</th>
        @endif
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>