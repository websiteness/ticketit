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
            <h2>Ticket Settings
            </h2>
        </div>

        <div class="panel-body">
            <div class="panel panel-default">
                <div class="panel-heading">Overdue</div>
                <div class="panel-body">
                    <form method="POST" action="{{ route($setting->grab('admin_route').'.settings.overdue.save') }}">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Hours until ticket is overdue</label>
                                    <input type="number" name="overdue_hours" class="form-control" min="0" value="{{ $setting->getBySlug('overdue_hours')->value ?? '' }}" required>
                                </div>
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>
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
		});
    </script>
@stop
