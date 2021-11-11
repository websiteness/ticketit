@extends($master)

@section('page')
    {{ trans('ticketit::admin.agent-index-title') }}
@stop

@section('header_styles')
    <link href="//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
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
        .btn-save {
            width: 30%;
            float: right;
        }

    </style>
@stop
                    
@section('content')
    @include('ticketit::shared.header')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>Email Reports</h2>
        </div>
        <div class="panel-body">
        <form method="POST" action="{{ route($setting->grab('admin_route').'.settings.email-reports.store') }}">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-sm-6 col-md-6 col-md-offset-3">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email</label>
                        <input type="text" required name="email_report_email" class="form-control" min="0" value="{{ isset($setting->getBySlug('email_report_email')->value) ? $setting->getBySlug('email_report_email')->value : $current_user_email  }}" required>
                    </div>
                    <div class="form-group">
                    <label for="frequency"> Frequency</label>
                        {!! CollectiveForm::select('email_report_frequency', $frequencies , isset($setting->getBySlug('email_report_frequency')->value) ? $setting->getBySlug('email_report_frequency')->value : ' ', ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <label for="time_of_day"> Time of Day</label>
                        <input type="time" id="time" name="email_report_time" value="{{ isset($setting->getBySlug('email_report_time')->value) ? $setting->getBySlug('email_report_time')->value : ''  }}" class="form-control" required>
                    </div>
          
       
                   
                    <div class="form-group">
                        <br>
                        <br>
                        <br>
                        <br>
                    <button type="submit" class="btn btn-success btn-save">Save</button>
                    </div>
                   
                </div>
            </div>
        </form>
        </div>
    </div>
@stop

@section('footer')
<script src="//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script type="text/javascript">

</script>     
@stop
                                                                                                                                                                