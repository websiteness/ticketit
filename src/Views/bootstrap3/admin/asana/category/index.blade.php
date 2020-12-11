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
            <form method="POST" action="{{ route($setting->grab('admin_route').'.asana.categories.map') }}">
            {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Categories</td>
                            <td>Sections</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                <select class="select2 form-control" name="sections[{{ $category->id }}]" style="width:100%;">
                                    <option value="">Select Section</option>
                                    @if($sections)
                                        @foreach ($sections as $section)
                                            <option value="{{ $section['gid'] }}" @if($category->asana_section_gid == $section['gid']) selected @endif>{{ $section['name'] }}</option>
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
		});
    </script>
@stop
