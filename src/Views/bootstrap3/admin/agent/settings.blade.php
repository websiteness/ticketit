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
    </style>
@stop

@section('content')
    @include('ticketit::shared.header')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>
                Receive Emails Notification
            </h2>
        </div>
        <div class="panel-body">
            <h3>{{ $agent->first_name . ' ' . $agent->last_name }}</h3>
            <form method="post" action="{{ route($setting->grab('admin_route').'.agent.notifications.settings.store', $agent->id) }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                        <th width="200px"></th>
                        <th width="40px">All</th>
                        <th width="30px">I Own</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                        <tr>
                            <td>{{ $event['value'] }}</td>
                            <td align="center"><input name="events[{{ $event['key'] }}][all]" type="checkbox" @if($agent_settings[$event['key']]['all'] ?? false) checked @endif></td>
                            <td align="center"><input name="events[{{ $event['key'] }}][own]" type="checkbox" @if($agent_settings[$event['key']]['own'] ?? false) checked @endif></td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="3"><b>Categories</b></td>
                        </tr>
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td align="center"><input name="categories[{{ $category->id }}][all]" type="checkbox" @if($agent_settings[$category->id]['all'] ?? false) checked @endif></td>
                            <td align="center"><input name="categories[{{ $category->id }}][own]" type="checkbox" @if($agent_settings[$category->id]['own'] ?? false) checked @endif></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-success pull-right">Save</button>
            </form>
        </div>
    </div>
@stop

@section('footer')
@stop
