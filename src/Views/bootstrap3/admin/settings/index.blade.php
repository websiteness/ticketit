@extends($master)

@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop

@section('header_styles')
	<link href="//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .api-token {
            display: flex;
        }

        .btn-generate-token {
            margin-left: 10px;
        }
        .input-api-token {
            margin-top: 5px;
        }

        .form-group .btn {
            margin-top: 5px !important;
            margin-bottom: 0px !important;
        }
    </style>
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
                                    <input type="number" name="overdue_hours" class="form-control" min="0" value="{{ isset($setting->getBySlug('overdue_hours')->value) ? $setting->getBySlug('overdue_hours')->value : ''  }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="status_id">Ticket status to check for closing </label>
                                    {!! CollectiveForm::select('t_setting_ticket_status_to_check', $statuses , isset($status_id->value) ? $status_id->value : null , ['class' => 'form-control']) !!}
                                </div>
                                <div class="form-group">
                                    <label for="time">Automatically closed in (days) </label>
                                    <input type="number" name="closed_days" class="form-control" min="0" value="{{ isset($setting->getBySlug('closed_days')->value) ? $setting->getBySlug('closed_days')->value : '' }}" required>
                                </div>
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">API Token</div>
                <div class="panel-body">          
                    <div class="col-md-5">
                        <div class="form-group api-token">
                            <input type="text" class="form-control input-api-token" id="input-api-token" min="0" value="{{ isset($setting->getBySlug('api_token')->value) ? $setting->getBySlug('api_token')->value : '' }}" >
                            <button type="submit" class="btn btn-success btn-generate-token">Generate token</button>
                        </div>
                    </div>                   
                </div>
            </div>
        </div>
    </div>

    
@stop

@section('footer')
    <script src="{{asset('libs/sweetalert/js/sweetalert.min.js')}}"></script>
	<script src="//cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

		    $('.select2').select2();
            let input_api_token = $('#input-api-token').val();

            // if(input_api_token != "" && input_api_token) {
            //     $('#input-api-token').prop('disabled', true)
            //     $('.btn-generate-token').prop('disabled', true)
            // } else {
            //     $('#input-api-token').prop('disabled', false)
            //     $('.btn-generate-token').prop('disabled', false)
            // }

            $('.btn-generate-token').click((e) => {
                let url = `{{ route($setting->grab('admin_route').'.settings.store-token' ) }}`;    
                swal({
                    title: "Are you sure?",
                    text: "Token will be generated.",
                    showCancelButton: true,
                    confirmButtonColor: "#26B99A",
                    confirmButtonText: "Confirm",
                    closeOnConfirm: false
                    }, function(isConfirm) {
                    if (isConfirm) {
                        let token = randomString();
                        $('#input-api-token').val(token);
        
                        $.ajax({
                            url: url,
                            type: 'POST', // replaced from put
                            data: {
                                api_token : token
                            },
                            success: function (response)
                            {
                                swal("Success!", "Token successfully generated!", "success");
                                
                                setTimeout(function(){ window.location.reload(); }, 1500);
                         
                            },
                        });
                
                    }
                });
            });
		});

        const randomString = _ => {
            var result = '';
            let length = 20;
            let chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
            return result;
        }

        
    </script>
@stop
                        