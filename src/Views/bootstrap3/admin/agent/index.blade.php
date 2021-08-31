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
            <h2>{{ trans('ticketit::admin.agent-index-title') }}
                {{-- {!! link_to_route(
                                    $setting->grab('admin_route').'.agent.create',
                                    trans('ticketit::admin.btn-create-new-agent'), null,
                                    ['class' => 'btn btn-primary pull-right'])
                !!} --}}
            </h2>
        </div>
        <div class="panel panel-default filters-panel">
            <div class="panel-body">
            {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.agent.store', 'method' => 'POST']) !!}
                <ul class="nav nav-pills">
                    <li role="presentation">
                        <div class="form-group">
                            <select class="form-control select2" id="agent" name="agent" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name . ' - ' . $user->email . ' (' . $user->roles()->first()->name . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li role="presentation">
                        <button type="submit" class="btn btn-success">Add</button>
                    </li>
                </ul>
            {!! CollectiveForm::close() !!}
            </div>
        </div>
        @if ($agents->isEmpty())
            <h3 class="text-center">{{ trans('ticketit::admin.agent-index-no-agents') }}
                {!! link_to_route($setting->grab('admin_route').'.agent.create', trans('ticketit::admin.agent-index-create-new')) !!}
            </h3>
        @else
            <div id="message"></div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <td>{{ trans('ticketit::admin.table-name') }}</td>
                        <td> Slack Webhook URL</td>
                        <td>Actions</td>
                    </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <td>
                
                            {{ $agent->name . ' - ' . $agent->email . ' (' . $agent->roles()->first()->name . ')' }}
                        </td>
                        <td> 
                        {{ $agent->agentSlackUrl->data }}
                        </td>
                        <td>
                        <a onclick='getWebhookUrl("{{ $agent->id }}", "{{ $agent->name."-".$agent->email}}", "{{ $agent->agentSlackUrl->id }}", "{{ $agent->agentSlackUrl->data }}")' class="btn btn-success">Edit</a>
                            {!! CollectiveForm::open([
                            'method' => 'DELETE',
                            'route' => [
                                        $setting->grab('admin_route').'.agent.destroy',
                                        $agent->id
                                        ],
                            'id' => "delete-$agent->id",
                            'class' => 'pull-left'
                            ]) !!}
                            {!! CollectiveForm::submit(trans('ticketit::admin.btn-remove'), ['class' => 'btn btn-danger']) !!}
                            {!! CollectiveForm::close() !!}

                            <a href="{{ route($setting->grab('admin_route').'.agent.notifications.settings', $agent->id) }}" class="btn btn-primary">Notifications</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
    <!-- Modal -->
<div class="modal fade" id="webhookUrlModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Agent: </h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Slack Webhook Url</label>
            <input type="text" class="form-control" id="agent-slack-webhook-url" placeholder="URL">
            <input type="hidden" id="agent-settings-id">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-save" onclick="saveWebhookUrl()">Save changes</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
			$('.select2').select2();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
		});
        
        function getWebhookUrl(id, agent_name, agent_settings_id, url) {
            $('#myModalLabel').text("Agent: " + agent_name);
            $('#agent-slack-webhook-url').val(url);
            $('#agent-settings-id').val(agent_settings_id);
            $('#webhookUrlModal').modal('show')    
        }

        function saveWebhookUrl() {
            let url = $('#agent-slack-webhook-url').val();       
            let id = $('#agent-settings-id').val();     
            let route = "{{ route($setting->grab('admin_route').'.agent.update.settings') }}";         
            let new_route = route.replace('id', id);   
            $('.btn-save').text('Saving...'); 
            $.post(new_route, { url: url, id: id }).done(function( data ) {
                if(data.success) {
                    $('.btn-save').text('Saved'); 
                    window.location.reload()
                } else {
                    alert(data.message);
                    window.location.reload()
                }
            });
        }
    </script>
@stop
