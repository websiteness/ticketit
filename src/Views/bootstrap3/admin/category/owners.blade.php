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
        <div class="panel-heading">
            <h2>
                Category Owners
            </h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route($setting->grab('admin_route').'.categories.owners.store') }}">
            {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                <select class="form-control select2" name="owners[{{ $category->id }}]" style="width:100%;">
                                    <option value="">Select Agent</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" @if($agent->id == $category->agents()->pluck('id')->first()) selected @endif>{{ $agent->first_name . ' ' . $agent->last_name . ' - ' . $agent->email  }}</option>
                                    @endforeach
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
		});
    </script>
@stop
