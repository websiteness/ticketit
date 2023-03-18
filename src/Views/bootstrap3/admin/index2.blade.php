@extends($master)

@section('page')
    {{ trans('ticketit::admin.index-title') }}
@stop

@section('header_styles')
    <!-- Toggle Switcher -->
    <link href="{{asset('libs/toggle-switcher/css/switcher.css')}}" rel="stylesheet">
    <link href="{{asset('libs/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{asset('libs/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
    <style>
        .mt-response-time {
            margin-top: 50px;
        }
    </style>
    {!! loadCSSFile('/css/ticket-dashboard.css') !!}
@stop

@section('content')
    @include('ticketit::shared.header')
    @if($tickets_count)
    <div class="x_panel">
        <div class="x_title">
            <h2>Dashboard</h2>
            <button id="filter-ticket" class="ticket-system__box-btn pull-right">Filters</button>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <div class="row">
                <div class="row mt-response-time">
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="ticket_first_response_time_average">{{$ticket_first_response_time_average}}</h1>
                                        <span>Average First Response Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="ticket_response_time_average">{{$ticket_response_time_average}}</h1>
                                        <span>Average Response Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="average_tickets_per_day">{{$average_tickets_per_day}}</h1>
                                        <span> Average Tickets Per Day</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="average_tickets_per_week">{{$average_tickets_per_week}}</h1>
                                        <span> Average Tickets Per Week</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="average_no_of_interactions">{{$average_no_of_interactions}}</h1>
                                        <span>Average Number of Interactions</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 ">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9">
                                        <h1 class="average_resolution_time">{{$average_resolution_time}}</h1>
                                        <span>Average Resolution Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('ticketit::admin.dashboard.partials.advanced-search-filter.index')
    @else
    <div class="well text-center">
        {{ trans('ticketit::admin.index-empty-records') }}
    </div>
    @endif
@stop
@section('footer')
    <script src="{{asset('libs/toggle-switcher/js/jquery.switcher.js')}}"></script>
    <script src="{{asset('libs/select2/dist/js/select2.full.min.js')}}"></script>
    <!-- Daterangepicker -->
    <script src="{{asset('libs/bootstrap-daterangepicker/moment.min.js')}}"></script>
    <script src="{{asset('libs/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <script>
        let search_users_url = `{!! route($setting->grab('main_route').'.search-users') !!}`;
        let get_selected_user_detail_url = `{!! route($setting->grab('main_route').'.get-selected-user-detail') !!}`;
        let get_selected_users_detail_url = `{!! route($setting->grab('main_route').'.get-selected-users-detail') !!}`;
        let get_ticket_dashboard_data_url = `{!! route('tickets-dashboard-data') !!}`;
    </script>
    {!! loadJSFile('/js/ticket-dashboard.js') !!}
@append
