@extends($master)

@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop

@section('header_styles')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
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
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}</option>
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
                        {{-- <td>{{ trans('ticketit::admin.table-id') }}</td> --}}
                        <td>{{ trans('ticketit::admin.table-name') }}</td>
                        {{-- <td>{{ trans('ticketit::admin.table-categories') }}</td>
                        <td>{{ trans('ticketit::admin.table-join-category') }}</td> --}}
                        <td>{{ trans('ticketit::admin.table-remove-agent') }}</td>
                    </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        {{-- <td>
                            {{ $agent->id }}
                        </td> --}}
                        <td>
                            {{ $agent->name }}
                        </td>
                        {{-- <td>
                            @foreach($agent->categories as $category)
                                <span style="color: {{ $category->color }}">
                                    {{  $category->name }}
                                </span>
                            @endforeach
                        </td>
                        <td>
                            {!! CollectiveForm::open([
                                            'method' => 'PATCH',
                                            'route' => [
                                                        $setting->grab('admin_route').'.agent.update',
                                                        $agent->id
                                                        ],
                                            ]) !!}
                            @foreach(\Kordy\Ticketit\Models\Category::all() as $agent_cat)
                                <input name="agent_cats[]"
                                       type="checkbox"
                                       value="{{ $agent_cat->id }}"
                                       {!! ($agent_cat->agents()->where("id", $agent->id)->count() > 0) ? "checked" : ""  !!}
                                       > {{ $agent_cat->name }}
                            @endforeach
                            {!! CollectiveForm::submit(trans('ticketit::admin.btn-join'), ['class' => 'btn btn-info btn-sm']) !!}
                            {!! CollectiveForm::close() !!}
                        </td> --}}
                        <td>
                            {!! CollectiveForm::open([
                            'method' => 'DELETE',
                            'route' => [
                                        $setting->grab('admin_route').'.agent.destroy',
                                        $agent->id
                                        ],
                            'id' => "delete-$agent->id"
                            ]) !!}
                            {!! CollectiveForm::submit(trans('ticketit::admin.btn-remove'), ['class' => 'btn btn-danger']) !!}
                            {!! CollectiveForm::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endif
    </div>
@stop

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
			$('.select2').select2();
		});
    </script>
@stop
