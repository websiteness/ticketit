@extends($master)

@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop

@section('header_styles')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<link href="{{asset('libs/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('libs/datatables.net-buttons-bs/css/buttons.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('libs/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('libs/datatables.net-responsive-bs/css/responsive.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('libs/datatables.net-scroller-bs/css/scroller.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('libs/sweetalert/css/sweetalert.css')}}" rel="stylesheet">
    <style type="text/css">
  .column-button-container {
    float: left;
  }

  .dataTables_length {
    margin-left: 20px;
  }

  .dataTables_filter {
    width: 100%;
  }

  .advanced-search-filter__tabs .tab-content-inner {
    overflow-y: hidden;
  }

  .column-credit-cntr {
    margin: 5px;
    font-weight: bold;
    display: inline-block;
  }

  .btn-create {
    width: 200px;
  }

  .table .btn {
    width: 30px;
    min-width: 0px !important;
  }

  textarea
  {
    border:1px solid #999999;
    width:100%;
    margin:5px 0;
    padding:3px;
  }
</style>
@stop

@section('content')
    @include('ticketit::shared.header')
    <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>All Scripts</h2>
        <div class="clearfix"></div>

      </div>
      <div class="x_content">
        @include('/layouts/_notification')
        <div class="pull-right">
          <a class="btn btn-info btn-create" data-toggle="modal" data-target="#createScriptModal"> Create </a>
        </div>
      <table id="scripts-table" class="data_table_draw table table-striped table-bordered">
          <thead>
            <th width="5%">Id</th>
            <th width="60%">Title</th>
            <th width="60%">Content</th>
            <th width="10%">Actions</th>
          </thead>
          <tbody>
            @if($scripts)
                @foreach ($scripts as $script)
                <tr>
                <td>{{ $script->id }}</td>
                    <td>{{ $script->title }}</td>
                    <td>{{ substr($script->content, 0, 100) }}</td>
                    <td> 
                        <a onclick="editScript('{{ $script->id }}', '{{ $script->title }}', '{{ $script->content }}')" class="btn btn-xs btn-info"> <span> <i class="fa fa-eye" aria-hidden="true"></i> </span> </a>
                        <button  class="btn btn-xs btn-danger" onclick='deleteScript("{{ $script->id }}")'> <span> <i class="fa fa-trash" aria-hidden="true"></i> </span> </button>
                    </td>
                </tr>
                @endforeach
            @endif
          </tbody>
        </table>
  
      </div>
    </div>
  </div>
</div>

  <!-- createScriptModal -->
  <div class="modal fade" id="createScriptModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Script</h4>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="scriptForm" class="form-horizontal form-label-left" data-parsley-validate method="POST" action="{{ route($setting->grab('main_route').'.scripts.store') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Title<span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="title" placeholder="Title" name="title" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Content<span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        {{ Form::textarea('content', null, array('class' => 'form-control', 'id'=>'summernote', 'name'=>'content'))}}
                        </div>
                        <input type="hidden" name="id" id="id" value="">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-submit">Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end createScriptModal -->
@stop

@section('footer')
<script src="{{asset('libs/sweetalert/js/sweetalert.min.js')}}"></script>
<script src="{{asset('libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-buttons-bs/js/buttons.bootstrap.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('libs/datatables.net-responsive-bs/js/responsive.bootstrap.js')}}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.40.0/codemirror.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.40.0/mode/xml/xml.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.js"></script>
<script type="text/javascript">
    $( document ).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
      var options = $.extend(true, {lang: '' , codemirror: {theme: 'monokai', mode: 'text/html', htmlMode: true, lineWrapping: true} } , {
        "height": 150,
        "toolbar": [
          ["font", ["bold", "underline", "italic"]],
          ["para", ["ul", "ol", "paragraph"]],
          ["table", ["table"]],
          ["insert", ["link", "picture", "video"]],
          ["view", ["fullscreen", "codeview", "help"]]
	    ]});


        $("#summernote").summernote(options);

        $('#scripts-table').DataTable();
        
        $('#scriptForm').submit((e) => {
            e.preventDefault();
 
            $('.btn-submit-file').prop('disabled', true);
            if (!$('#title').val()) {
                swal("Error", "Please fill up the title field.", "error");
                $('.btn-submit').prop('disabled', false);
            } else if (!$('#summernote').val()) {
                swal("Error", "Please fill up the content field.", "error");
                $('.btn-submit').prop('disabled', false);
            } else {
                var formData = new FormData($('#scriptForm')[0]);
                $.ajax({
                    type: 'POST',
                    url: $("#scriptForm").attr("action"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: () => {
                        $('#btn-submit').prop('disabled', true);
                    },
                    success: function(response) {
                        $('#scriptForm')[0].reset();
                        $('#uploadFileModal').modal('toggle');
                        swal(response.success ? "Success" : "Error", response.message, response.success ? "success" : "error");
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                });
            }
        })


    });

    let tags_delete_url = `{{ route($setting->grab('main_route_path').'.scripts.delete',  'id' ) }}`;

    const deleteScript = (id) => {
        swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this record.",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Confirm",
        closeOnConfirm: false
        }, function(isConfirm) {
        if (isConfirm) {
            $(".confirm").attr('disabled', 'disabled');
            let url = tags_delete_url.replace('id', id);            
            $.ajax({
                url: url,
                type: 'DELETE', // replaced from put
                success: function (response)
                {
                    if(response.success) {                       
                        window.location.reload();
                    } else {
                        swal("Error!", response.message, "error"); 
                    }
                },
            });
        }
        });
    }

    const editScript = (id, title, content) => {
      $('<input>').attr({
          type: 'hidden',
          id: 'method',
          name: '_method',
          value: 'PATCH'
      }).appendTo('#scriptForm');
      $('.modal-title').text('Edit Script');
      $('#title').val(title);
      $('#summernote').summernote('code', content);
      $('#id').val(id);
      $("#scriptForm").attr("action", `{{ route($setting->grab('main_route').'.scripts.update') }}`)

      $('#createScriptModal').modal('show');
    }

  </script>
@stop
                                              