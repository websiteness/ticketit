<table class="ticketit-table table table-striped table-bordered dt-responsive nowrap" style="width:100%">
    <thead>
        <tr>
            <td>{{ trans('ticketit::lang.table-id') }}</td>
            <td>{{ trans('ticketit::lang.table-subject') }}</td>
            <td>{{ trans('ticketit::lang.table-status') }}</td>
          @if( $u->isAgent() || $u->isAdmin() )
            <td>Last Reply</td>
          @endif
            <td>{{ trans('ticketit::lang.table-last-updated') }}</td>
          @if( $u->isAgent() || $u->isAdmin() )
            <td>{{ trans('ticketit::lang.table-agent') }}</td>
            <td>{{ trans('ticketit::lang.table-priority') }}</td>
            <td>{{ trans('ticketit::lang.table-owner') }}</td>
            <td>{{ trans('ticketit::lang.table-category') }}</td>
          @endif
            <td>Actions</td>
        </tr>
    </thead>
</table>