@extends($master)
@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop
@section('header_styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .table .btn {
            width: auto;
        }
        .panel {
            margin-bottom: 0;
        }
    </style>
@stop
@section('content')
    @include('ticketit::shared.header')
    <div class="panel panel-default">
        <div class="panel-body">
            @include('ticketit::admin.infinity.shared.nav')
            <form method="POST" action="{{ route($setting->grab('admin_route').'.infinity.status.store') }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Status</td>
                            <td>Value</td>
                        </tr>
                    </thead>
                    <tbody>            
                    <hr>                                          
                        @foreach ( $ticket_statuses as $t_status )
                        <tr>               
                            <td>{{ $t_status->name }}</td>
                            <td width="30%">
                                <select class="select2 form-control" name="statuses[{{ $t_status->id }}]" style="width:100%;">
                                    <option value="">Select Status</option>     
                                    @if($infinity_statuses)
                                        @foreach ($infinity_statuses as $infinity_status)
                                            @if(isset($t_status->infinity_status_id))
                                                <option value="{{ $infinity_status['id'] }}" {{$t_status->infinity_status_id == $infinity_status['id'] ? 'selected' : '' }}>{{ $infinity_status['name'] }}</option>                                
                                            @else
                                                <option value="{{ $infinity_status['id'] }}" >{{  $infinity_status['name'] }}</option>
                                            @endif
                                        @endforeach     
                                    @endif                         
                                </select>
                            </td>                 
                        </tr> 
                        @endforeach                
                    </tbody>
                </table>
                <button class="btn btn-success pull-right">Save</button>
            </form>
        </div>
    </div>
    <br>
@stop
