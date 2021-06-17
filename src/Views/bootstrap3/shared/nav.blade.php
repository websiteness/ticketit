<div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
    <div class="ticket-system__tabs-action">
      <div class="ticket-system__tab-icon">
        <img src="{{asset('images/ticket-system/icon-leadgenerated.png')}}" alt="">
      </div><!-- .ticket-system__tab-icon -->
      <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
        <li role="presentation" @if(Route::currentRouteName() == 'tickets.create' || Route::currentRouteName() == 'tickets.crmticket.create') class="active" @endif>
          <a href="{{ route($setting->grab('main_route').'.create') }}">Create New Ticket</a>
        </li>
        <li role="presentation" @if(Route::currentRouteName() == 'tickets.index') class="active" @endif>
          <a href="{{ action('\Kordy\Ticketit\Controllers\TicketsController@index') }}">Active Tickets</a>
          <span class="ticket-count">
                <?php 
                    if ($u->isAdmin()) {
                        echo Kordy\Ticketit\Models\Ticket::active()->count();
                        // echo 1;
                    } elseif ($u->isAgent()) {
                        // echo Kordy\Ticketit\Models\Ticket::active()->agentUserTickets($u->id)->count();
                        echo Kordy\Ticketit\Models\Ticket::active()->count();
                    } else {
                        echo Kordy\Ticketit\Models\Ticket::userTickets($u->id)->active()->count();
                    }
                ?>
          </span>
        </li>
        <li role="presentation" @if(Route::currentRouteName() == 'tickets-complete') class="active" @endif>
          <a href="{{ action('\Kordy\Ticketit\Controllers\TicketsController@indexComplete') }}">Completed Tickets</a>
          <span class="ticket-count">
                <?php 
                    if ($u->isAdmin()) {
                        echo Kordy\Ticketit\Models\Ticket::complete()->count();
                    } elseif ($u->isAgent()) {
                        // echo Kordy\Ticketit\Models\Ticket::complete()->agentUserTickets($u->id)->count();
                        echo Kordy\Ticketit\Models\Ticket::complete()->count();
                    } else {
                        echo Kordy\Ticketit\Models\Ticket::userTickets($u->id)->complete()->count();
                    }
                ?>
          </span>
        </li>
        @if($u->isAdmin())
                <li role="presentation" class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\DashboardController@index')) || Request::is($setting->grab('admin_route').'/indicator*') ? "active" : "" !!}">
                    <a href="{{ action('\Kordy\Ticketit\Controllers\DashboardController@index') }}">{{ trans('ticketit::admin.nav-dashboard') }}</a>
                </li>

                <li role="presentation" class="dropdown {!!
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\StatusesController@index').'*') ||
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\PrioritiesController@index').'*') ||
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\AgentsController@index').'*') ||
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\CategoriesController@index').'*') ||
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\ConfigurationsController@index').'*') ||
                    $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\AdministratorsController@index').'*')
                    ? "active" : "" !!}">

                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        {{ trans('ticketit::admin.nav-settings') }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\AgentsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\AgentsController@index') }}">{{ trans('ticketit::admin.nav-agents') }}</a>
                        </li>
                        <li role="presentation" class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\Integrations\AsanaController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\Integrations\AsanaController@index') }}">Asana</a>
                        </li>
                        <li role="presentation" class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\Integrations\InfinityController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\Integrations\InfinityController@index') }}">Infinity</a>
                        </li>
                        <li role="presentation" class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\CategoriesController@viewCategoryOwners').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\CategoriesController@viewCategoryOwners') }}">Category Owners</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\SettingsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\SettingsController@index') }}">Ticket Settings</a>
                        </li>
                        {{-- <li role="presentation" class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\StatusesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\StatusesController@index') }}">{{ trans('ticketit::admin.nav-statuses') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\PrioritiesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\PrioritiesController@index') }}">{{ trans('ticketit::admin.nav-priorities') }}</a>
                        </li> --}}
                       {{--  <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\CategoriesController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\CategoriesController@index') }}">{{ trans('ticketit::admin.nav-categories') }}</a>
                        </li> --}}
                        {{-- <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\ConfigurationsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\ConfigurationsController@index') }}">{{ trans('ticketit::admin.nav-configuration') }}</a>
                        </li> --}}
                        {{-- <li role="presentation"  class="{!! $tools->fullUrlIs(action('\Kordy\Ticketit\Controllers\AdministratorsController@index').'*') ? "active" : "" !!}">
                            <a href="{{ action('\Kordy\Ticketit\Controllers\AdministratorsController@index')}}">{{ trans('ticketit::admin.nav-administrator') }}</a>
                        </li> --}}
                    </ul>
                </li>
            @endif
      </ul>
    </div><!-- .ticket-system__tabs-action -->
</div>