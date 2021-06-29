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
            <form method="POST" action="{{ route($setting->grab('admin_route').'.infinity.users.store') }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Users</td>
                            <td>Value</td>
                        </tr>
                    </thead>
                    <tbody>            
                    <hr>       
                    @foreach ($agents as $agent)                       
                        <tr>                     
                            <td>{{ $agent->name }} - {{ $agent->email }} - {{ $agent->infinity_user_id }}</td>
                            <td width="30%">
                                <select class="select2 form-control" name="users[{{ $agent->id }}]" style="width:100%;">
                                    <option value="">Select User</option>
                                    @if ($workspace_users)
                                        @foreach ($workspace_users as $user)
                                            @if(isset($agent->infinity_user_id))
                                                <option value="{{ $user['id'] }}" {{ $agent->infinity_user_id == $user['id'] ? 'selected' : '' }}>{{ $user['name'] }}</option>                                
                                            @else
                                                <option value="{{ $user['id'] }}" >{{ $user['name'] }}</option>
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
