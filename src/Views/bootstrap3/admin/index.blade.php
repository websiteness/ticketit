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

            <div class="col-lg-2 col-md-2 col-md-offset-1">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3" style="font-size: 5em;">
                                <i class="glyphicon glyphicon-wrench"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h1>{{ $open_tickets_count }}</h1>
                                <div>{{ trans('ticketit::admin.index-open-tickets') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3" style="font-size: 5em;">
                                <i class="glyphicon glyphicon-remove"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h1>{{ $no_response_tickets_count }}</h1>
                                <span>No Response</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-2">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3" style="font-size: 5em;">
                                <i class="glyphicon glyphicon-forward"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h1>{{ $in_progress_tickets_count }}</h1>
                                <span>In Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-2">
                <div class="panel panel-warning ">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3" style="font-size: 5em;">
                                <i class="glyphicon glyphicon-list-alt"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h1>{{ $waiting_feedback_tickets_count }}</h1>
                                <span>Waiting Feedback</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3" style="font-size: 5em;">
                                <i class="glyphicon glyphicon-ok"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <h1>{{ $has_response_tickets_count }}</h1>
                                <span>User Last Reponse</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            
        <div class="col-md-4 col-md-offset-4">
            <h2> Average Ticket Response Time</h2>
            </div>
        </div>
        <div class="row">
        <br>
 
        <div class="col-lg-12">
            <div class="pull-right">
                <div class="form-group">
                    <div class="form-inline">
                        <div class="form-group">
                            <label for="date_from">From: </label>
                            <input class="form-control" type="date" name="date_from" id="date_from" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="date_to">To: </label>
                            <input class="form-control" type="date" name="date_to" id="date_to" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="row mt-response-time">
        <div class="col-lg-6 col-md-6 ">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-9">
                                <h1 class="thirty-days-response">  </h1>
                                <span>Last 30 Days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="col-lg-6 col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                 
                            <div class="col-xs-9 ">
                                <h1 class="date-range-response"> </h1>
                                <span>Average By Date Range: </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                 
                            <div class="col-xs-9 ">
                                <h1 class="seven-days-response"> </h1>
                                <span>Last 7 Days</span>
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
    @if($tickets_count)
    {{--@include('ticketit::shared.footer')--}}
    <script type="text/javascript"
            src="https://www.google.com/jsapi?autoload={
            'modules':[{
              'name':'visualization',
              'version':'1',
              'packages':['corechart']
            }]
          }"></script>

    <script type="text/javascript">

    $(() => {
        loadAverageReponseTime();

        let to_date = $('#date_to').val();
        let from_date  = $('#date_from').val();
        getAverageByDate(from_date, to_date);


        $('#date_to').change(function() {
            let date_to = $(this).val();
            let date_from = $('#date_from').val();
            getAverageByDate(date_from, date_to);
        });
    });

    const getAverageByDate = (date_from, date_to) => {
        let url = `{!! route($setting->grab('admin_route').'.average-ticket-response-by-date') !!}`;
        $.ajax({
            method: 'get',
            data : {
                'date_from' :  date_from,
                'date_to' :  date_to,
            },
            url: url,
            success: function(res) {
                $('.date-range-response').text(res.average_response_by_date_rage);
            }
        });
    }

    const loadAverageReponseTime = () => {
        let url = `{!! route($setting->grab('admin_route').'.average-ticket-response') !!}`;
        $.ajax({
            method: 'get',
            url: url,
            success: function(res) {
                $('.thirty-days-response').text(res.thirty_days);
                $('.seven-days-response').text(res.seven_days);
            }
        });
    }
        google.setOnLoadCallback(drawChart);

        // performance line chart
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ["{{ trans('ticketit::admin.index-month') }}", "{!! implode('", "', $monthly_performance['categories']) !!}"],
                @foreach($monthly_performance['interval'] as $month => $records)
                    ["{{ $month }}", {!! implode(',', $records) !!}],
                @endforeach
            ]);

            var options = {
                title: '{!! addslashes(trans('ticketit::admin.index-performance-chart')) !!}',
                curveType: 'function',
                legend: {position: 'right'},
                vAxis: {
                    viewWindowMode:'explicit',
                    format: '#',
                    viewWindow:{
                        min:0
                    }
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            chart.draw(data, options);

            // Categories Pie Chart
            var cat_data = google.visualization.arrayToDataTable([
              ['{{ trans('ticketit::admin.index-category') }}', '{!! addslashes(trans('ticketit::admin.index-tickets')) !!}'],
              @foreach($categories_share as $cat_name => $cat_tickets)
                    ['{!! addslashes($cat_name) !!}', {{ $cat_tickets }}],
              @endforeach
            ]);

            var cat_options = {
              title: '{!! addslashes(trans('ticketit::admin.index-categories-chart')) !!}',
              legend: {position: 'bottom'}
            };

            var cat_chart = new google.visualization.PieChart(document.getElementById('catpiechart'));

            cat_chart.draw(cat_data, cat_options);

            // Agents Pie Chart
            var agent_data = google.visualization.arrayToDataTable([
              ['{{ trans('ticketit::admin.index-agent') }}', '{!! addslashes(trans('ticketit::admin.index-tickets')) !!}'],
              @foreach($agents_share as $agent_name => $agent_tickets)
                    ['{!! addslashes($agent_name) !!}', {{ $agent_tickets }}],
              @endforeach
            ]);

            var agent_options = {
              title: '{!! addslashes(trans('ticketit::admin.index-agents-chart')) !!}',
              legend: {position: 'bottom'}
            };

            var agent_chart = new google.visualization.PieChart(document.getElementById('agentspiechart'));

            agent_chart.draw(agent_data, agent_options);

        }

   
    </script>
    @endif
@append
