@if($u->isAgent() || $u->isAdmin())
<div class="panel-body">
    @include('ticketit::tickets.partials.stats')
</div>
@endif

<div class="panel panel-default">

    <div class="panel-heading">
        <h2>{{ trans('ticketit::lang.index-my-tickets') }}
            {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary pull-right']) !!}
        </h2>
    </div>

    @if(Sentinel::inRole('super-admin'))
    <div class="panel-body">
        @include('ticketit::shared.filters')
    </div>
    @endif

    <div class="panel-body">
        <div id="message"></div>

        @include('ticketit::tickets.partials.datatable')
    </div>

</div>
