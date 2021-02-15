@if($u->isAgent() || $u->isAdmin())
<div class="panel-body">
    @include('ticketit::tickets.partials.stats')
</div>
@endif

<div class="panel panel-default">

    <div class="panel-heading">
        @if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin)
        <div class="pull-left">
            <h2>{{ trans('ticketit::lang.index-my-tickets') }}</h2>
        </div>
        <div class="pull-right" style="margin-top:10px;">
            {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary']) !!}
                <button class="btn btn-default" onclick="openNav()" id="filter_open"><i class="fa fa-filter"></i></button>
        </div>
        @else
            <h2>{{ trans('ticketit::lang.index-my-tickets') }}
            {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary pull-right']) !!}</h2>
        @endif
    </div>
    
    @if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin )
    <div class="panel-body">
        @include('ticketit::shared.filters')
    </div>
    @endif

    <div class="panel-body">
        <div id="message"></div>

        @include('ticketit::tickets.partials.datatable')
    </div>

</div>
