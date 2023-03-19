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
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top">
                            <h3 class="counter__value ticket_first_response_time_average">{{$ticket_first_response_time_average}}</h3>

                            <div class="counter__icon">
                                <img src="{{asset('images/ticket-system/new/overdue.png')}}" alt="">
                            </div>
                        </div>
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average First Response Time</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top">
                            <h3 class="counter__value ticket_response_time_average">{{$ticket_response_time_average}}</h3>

                            <div class="counter__icon">
                                <img src="{{asset('images/ticket-system/new/overdue.png')}}" alt="">
                            </div>
                        </div>
                        
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average Response Time</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top counter__top2">
                            <h3 class="counter__value average_tickets_per_day">{{$average_tickets_per_day}}</h3>

                            <div class="counter__icon">
                                <span class="icon-gradient icon-ticket-1"></span>
                            </div>
                        </div>
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average Tickets Per Day</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top counter__top2">
                            <h3 class="counter__value average_tickets_per_week">{{$average_tickets_per_week}}</h3>
                            <div class="counter__icon">
                                <span class="icon-gradient icon-ticket-1"></span>
                            </div>
                        </div>
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average Tickets Per Week</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top">
                            <h3 class="counter__value average_no_of_interactions">{{$average_no_of_interactions}}</h3>

                            <div class="counter__icon">
                                <img src="{{asset('images/ticket-system/new/waiting-on-support.png')}}" alt="">
                            </div>
                        </div>
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average Number of Interactions</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="counter__box">
                        <div class="counter__top">
                            <h3 class="counter__value average_resolution_time">{{$average_resolution_time}}</h3>

                            <div class="counter__icon">
                                <img src="{{asset('images/ticket-system/new/overdue.png')}}" alt="">
                            </div>
                        </div>
                        <div class="counter__bottom">
                            <h4 class="counter__title">Average Resolution Time</h4>
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
