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
            @include('ticketit::admin.asana.shared.nav')
            <form method="POST" action="{{ route($setting->grab('admin_route').'.asana.users.map') }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Agents</td>
                            <td>Users</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                        <tr>
                                <td>{{ $agent->first_name .' '. $agent->last_name .' - '. $agent->email }}</td>
                                <td width="40%">
                                    <select class="select2 form-control" name="users[{{ $agent->id }}]" style="width:100%;">
                                        <option value="">Select Agent</option>
                                        @if($users)
                                            @foreach($users as $user)
                                                <option value="{{ $user['gid'] }}" @if($agent->ticketit_asana_gid == $user['gid']) selected @endif>{{ $user['name'] }}</option>
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

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
            $('.select2').select2();

             //User Dropdown
             $.get(`{{ route($setting->grab('admin_route').'.asana.projects') }}`, function(data, status){
                $.each(data, function(i, item) {
                    var users = $('select[name="users"]');
                    users.append('<option value='+item['gid']+'>'+ item['name'] +'</option>');
                });
            });
		});
    </script>
@stop
