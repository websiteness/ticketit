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
        @if($token)
        @include('ticketit::admin.infinity.shared.nav')
        <form method="POST" action="">
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
        @endif
        @if($token)
        <hr>
        <form id="workspace_form" method="POST" action="{{ route($setting->grab('admin_route').'.infinity.workspaces.store') }}">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Workspace</label>
                        <select required id="selected-workspace" class="form-control" name="workspace" style="width:100%;">
                            <option value=''>Select Workspace</option>
                            @if(count($workspaces) > 0 )
                                @foreach($workspaces as $workspace)
                                    @if(isset($selected_workspace)  )
                                        <option value="{{ $workspace['id'] }}" {{ $workspace['id'] == $selected_workspace->value ? 'selected' : '' }}>{{ $workspace['name'] }}</option>                                
                                    @else
                                        <option value="{{ $workspace['id'] }}" >{{ $workspace['name'] }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <button id="save-workspace" type="submit" class="btn btn-success" disabled="disabled">Save</button>
                </div>
            </div>
        </form>
        @endif
        @if($workspaces)
        <hr>
        <form id="boards_form" method="POST" action="{{ route($setting->grab('admin_route').'.infinity.boards.store') }}" style="display: none;">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Board</label>
                        <select required  id="selected-board" class="form-control select2" name="board" style="width:100%;">
                            <option value="">Select Board</option>
                        </select>
                    </div>
                    <button id="save-board" type="submit" class="btn btn-success" disabled="disabled">Save</button>
                </div>
            </div>
        </form>
        @endif
        <hr>
        <form id="folders_form" method="POST" action="{{ route($setting->grab('admin_route').'.infinity.folders.store') }}" style="display: none;">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Select Folder</label>
                        <select required  id="selected-folder" class="form-control select2" name="folder" style="width:100%;">
                            <option value="">Select Folder</option>
                        </select>
                    </div>
                    <button id="save-folder" type="submit" class="btn btn-success" disabled="disabled">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<br>
@stop

@section('footer')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        loadBoards();
        $("#selected-workspace").change(function() {
            if($(this).val() != '') {
                $('#selected-board').find('option').remove().end().append('<option value="">Select Board</option>').val('');
                loadBoards();
                $('#save-workspace').attr('disabled', false);
            } else {
                $('#save-workspace').attr('disabled', true);
            }          
        });
        $("#selected-board").change(function() {
            if($(this).val() != '') {
                $('#selected-folder').find('option').remove().end().append('<option value="">Select Folder</option>').val('');
                loadFolders();
                $('#save-board').attr('disabled', false);
            } else {
                $('#save-board').attr('disabled', true);
            }          
        });    
    });
    function loadBoards() {
        let selected_workspace_id = $('#selected-workspace').find(":selected").val();
        let id = parseInt(selected_workspace_id);
        //get boards by workspace
        if(!isNaN(id) && typeof id === 'number') {
            $.ajax({
                method: 'get',
                url: `{{ route($setting->grab('admin_route').'.infinity.boards') }}`,
                beforeSend: function() {
                    $('#save-board').text('Please Wait...');
                },
                success: function(data) {
                    let ws_boards = [];
                    data.map((x) => {
                        if(x.team_id == id) {
                            ws_boards.push(x)
                        }
                    });                
                    $('#boards_form').show();
                        var boards = $('select[name="board"]');
                        var selected_board = '';                      
                        $.each(ws_boards, function(i, item) {                           
                            var $select = '';
                            @if(isset($selected_board->value))
                                selected_board = `{{ $selected_board->value }}`;
                            @endif
                        if(selected_board == item['id']){
                            $select = "selected=selected";
                        }
                        boards.append('<option '+$select+' value='+item['id']+'>'+ item['name'] +'</option>');
                    });             
                    $('#save-board').attr('disabled', false);
                    $('#save-board').text('Save');
                    loadFolders();
                }
            });     
        }
    }
    function loadFolders() {
        let selected_workspace_id = $('#selected-workspace').find(":selected").val();
        let ws_id = parseInt(selected_workspace_id);
        let selected_board_id = $('#selected-board').find(":selected").val();
        console.log(selected_board_id)
        console.log(ws_id)
        //get folder by workspace and board
        if( typeof selected_board_id === 'string') {
            let route = "{{ route($setting->grab('admin_route').'.infinity.folders', ['workspace' => 'ws_id', 'board' => 'board_id']) }}";         
            let new_route = route.replace('ws_id', ws_id).replace('board_id', selected_board_id);
            $.ajax({
                method: 'get',
                url: new_route,
                beforeSend: function() {
                    $('#save-folder').text('Please Wait...');
                },
                success: function(res) {
                    $('#folders_form').show();
                    var folder = $('select[name="folder"]');
                    let selected_folder = '';
                    $.each(res, function(i, item) {                           
                    var $select = '';
                        @if(isset($selected_folder->value))
                            selected_folder = `{{ $selected_folder->value }}`;
                        @endif
                        if(selected_folder == item['id']){
                            $select = "selected=selected";
                        }
                        folder.append('<option '+$select+' value='+item['id']+'>'+ item['name'] +'</option>');
                    });  
                    $('#save-folder').attr('disabled', false);
                    $('#save-folder').text('Save');
                }
            });
        }
    }
</script>
@stop