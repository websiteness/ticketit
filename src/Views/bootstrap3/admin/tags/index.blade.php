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
</style>
@stop

@section('content')
    @include('ticketit::shared.header')
    <div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>All Tags</h2>
        <div class="clearfix"></div>

      </div>
      <div class="x_content">
        @include('/layouts/_notification')
        <div class="pull-right">
          <a href="{{ route($setting->grab('main_route_path').'.tags.create') }}" class="btn btn-info btn-create"> Create </a>
        </div>
      <table id="tags-table" class="data_table_draw table table-striped table-bordered">
          <thead>
            <th width="5%">Id</th>
            <th width="60%">Name</th>
            <th width="10%">Actions</th>
          </thead>
          <tbody>
            @if ($tags)
                @foreach ($tags as $tag)
                <tr>
                <td>{{ $tag->id }}</td>
                    <td>{{ $tag->name }}</td>
                    <td> 
                        <a href="{{ route($setting->grab('main_route_path').'.tags.show', ['id' =>  $tag->id  ]) }}" class="btn btn-xs btn-info"> <span> <i class="fa fa-eye" aria-hidden="true"></i> </span> </a>
                        <button  class="btn btn-xs btn-danger" onclick='deleteTag("{{ $tag->id }}")'> <span> <i class="fa fa-trash" aria-hidden="true"></i> </span> </button>
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
<script type="text/javascript">
    $( document ).ready(function() {
        $('#tags-table').DataTable();
    });

    let tags_delete_url = `{{ route($setting->grab('main_route_path').'.tags.delete',  'id' ) }}`;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    function deleteTag(id) {
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
  </script>
@stop
