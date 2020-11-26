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
                Asana
            </h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route($setting->grab('admin_route').'.asana.token.store') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Personal Access Token</label>
                            <input type="text" name="token" class="form-control" min="0" value="{{ $setting->getBySlug('asana_token')->value ?? '' }}" required>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <br>
@stop

@section('footer')
@stop
