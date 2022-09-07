@extends($master)

@section('page')
    {{ trans('ticketit::admin.index-title') }}
@stop

@section('header_styles')
  
    <style>
        .mt-response-time {
            margin-top: 50px;
        }
    </style>
@stop

@section('content')
    @include('ticketit::shared.header')
    @if($tickets_count)

        <div class="row">
            <div class="row mt-response-time">
                <div class="col-lg-6 col-md-6 ">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-9">
                                    <h1 class="">{{$ticket_first_response_time_average}}</h1>
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
                                    <h1 class="">{{$ticket_response_time_average}}</h1>
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
                                    <h1 class="">{{$average_tickets_per_day}}</h1>
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
                                    <h1 class="">{{$average_tickets_per_week}}</h1>
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
                                    <h1 class="">{{$average_no_of_interactions}}</h1>
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
                                    <h1 class="">{{$average_resolution_time}}</h1>
                                    <span>Average Resolution Time</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="well text-center">
            {{ trans('ticketit::admin.index-empty-records') }}
        </div>
    @endif
@stop
@section('footer')

@append
