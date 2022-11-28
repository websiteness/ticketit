@if($u->isAgent() || $u->isAdmin())
    @include('ticketit::tickets.partials.stats')
@endif
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="ticket-system__box">
            <div class="ticket-system__box-header">
                <h2><span class="icon-gradient icon-ticket-1"></span> {{ trans('ticketit::lang.index-my-tickets') }}</h2>
                <ul class="ticket-system__box-header-actions">
                    @if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin)
                        <li>
                            <button id="filter-ticket" class="ticket-system__box-btn">Filters</button>
                        </li>
                    @endif
                    <li>
                        <button onclick="location.href='{{route($setting->grab('main_route').'.create')}}'" id="create-new-ticket" class="ticket-system__box-btn">Create New Ticket</button>
                    </li>
                </ul>
            </div>
            <!-- .ticket-system__box-header -->
            <div class="table-responsive">
                @include('ticketit::tickets.partials.datatable')
            </div>
            <!-- .table-responsive -->
        </div>
        <!-- .ticket-system__box -->
    </div>
    <!-- .col-md-12 col-sm-12 col-xs-12 -->
</div><!-- .row -->