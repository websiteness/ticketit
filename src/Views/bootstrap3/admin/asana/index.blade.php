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
        .select2-selection__choice__remove {
            margin-bottom: 0;
        }
        .select2-selection__choice__remove:hover {
            background-color: #1abb9c !important;
        }
    </style>
@stop

@section('content')
    @include('ticketit::shared.header')
    <div class="panel panel-default">
        <div class="panel-body">
            @include('ticketit::admin.asana.shared.nav')
            <form method="POST" action="{{ route($setting->grab('admin_route').'.asana.token.store') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Personal Access Token</label>
                            <input type="text" required name="token" class="form-control" min="0" value="{{ $token }}" required>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>

            @if($token)
            <hr>
            <form id="workspace_form" method="POST" action="{{ route($setting->grab('admin_route').'.asana.workspaces.store') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Select Workspace</label>
                            <select required class="form-control select2" name="workspace" style="width:100%;">
                                <option value="">Select Workspace</option>
                            </select>
                        </div>
                        <button id="save-workspace" type="submit" class="btn btn-success" disabled="disabled">Save</button>
                    </div>
                </div>
            </form>
            @endif
            
            @if($workspace)
            <hr>
            <form id="project_form" method="POST" action="{{ route($setting->grab('admin_route').'.asana.settings.store') }}" style="display: none;">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Select Project</label>
                            <select required class="form-control select2" name="project" style="width:100%;">
                                <option value="">Select Project</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Select Tags</label>
                            <select class="form-control select2" name="tags[]" multiple style="width:100%;">
                                <option value="">Select Tags</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
    <br>
@stop

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
            $('.select2').select2();
            
            // get workspace list
            @if($token)
                $.ajax({
                    method: 'get',
                    url: `{{ route($setting->grab('admin_route').'.asana.workspaces.list') }}`,
                    beforeSend: function(){
                        $('#save-workspace').text('Please Wait...');
                    },
                    success: function(data) {
                        var project = $('select[name="workspace"]');
                        var selected_project = '';

                        $.each(data, function(i, item) {

                            @if(isset($workspace->value))
                                selected_project = `{{ $workspace->value }}`;
                            @endif

                            var $select = '';

                            if(selected_project == item['gid']){
                                $select = "selected=selected";
                            }
                            project.append('<option '+$select+' value='+item['gid']+'>'+ item['name'] +'</option>');
                        });

                        $('#save-workspace').attr('disabled',false);
                        $('#save-workspace').text('Save');
                    }
                });
            @endif

            @if($workspace)
                // get project list
                $.ajax({
                    method: 'get',
                    url: `{{ route($setting->grab('admin_route').'.asana.projects') }}`,
                    beforeSend: function(){
                        // $('#save-project').text('Please Wait...');
                    },
                    success: function(data) {
                        $('#project_form').show();

                        var project = $('select[name="project"]');
                        var selected_project = '';

                        $.each(data, function(i, item) {

                            @if(isset($project->value))
                                selected_project = `{{ $project->value }}`;
                            @endif

                            var $select = '';
                            if(selected_project == item['gid']) {
                                $select = "selected=selected";
                            }
                            project.append('<option '+$select+' value='+item['gid']+'>'+ item['name'] +'</option>');
                        });

                        // $('#save-project').attr('disabled',false);
                        // $('#save-project').text('Save');
                    }
                });

                // get tag list
                $.ajax({
                    method: 'get',
                    url: `{{ route($setting->grab('admin_route').'.asana.tags.list') }}`,
                    beforeSend: function(){
                        // $('#save-project').text('Please Wait...');
                    },
                    success: function(data) {
                        $('#project_form').show();

                        var project = $('select[name="tags[]"]');
                        var selected_project = '';
                        let selected_tags = [];

                        @if(isset($tags))
                            @foreach($tags as $tag)
                                selected_tags.push(`{{ $tag }}`);
                            @endforeach
                        @endif

                        console.log('TAGS', selected_tags);

                        $.each(data, function(i, item) {
                            var $select = '';

                            if(selected_tags.includes(item['gid'])) {
                                $select = "selected=selected";
                            }

                            project.append('<option '+$select+' value='+item['gid']+'>'+ item['name'] +'</option>');
                        });

                        // $('#save-project').attr('disabled',false);
                        // $('#save-project').text('Save');
                    }
                });
            @endif
		});
    </script>
@stop
