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
            @include('ticketit::admin.infinity.shared.nav')
            <form method="POST" action="{{ route($setting->grab('admin_route').'.infinity.fields.map') }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Key</td>
                            <td>Value</td>
                        </tr>
                    </thead>
                    <tbody>                                     
                    @foreach($fields as $key => $field)
                        @if(in_array($key, $selected_fields_slugs))
                        <tr>                  
                            <td>{{ $field }}</td>
                            <td width="30%">
                                <select class="select2 form-control" name="{{ $key }}" style="width:100%;">
                                    <option value="">Select Field</option>             
                                    @if($attributes)         
                                        @foreach ($attributes as $attribute)  
                                            @foreach ($selected_fields as $selected_field)
                                       
                                                @if($selected_field['slug'] == $key)
                                                    <option value="{{ $attribute['id'] }}" @if ($selected_field['value'] == $attribute['id']) selected @endif >{{ $attribute['name'] }}</option>                                        
                                                @endif                                                       
                                            @endforeach     
                                        @endforeach    

                                        @endif
                                                        
                                </select>
                            </td>
                        </tr> 
                        @else
                            <tr>                  
                                <td>{{ $field }}</td>
                                <td width="30%">
                                    <select class="select2 form-control" name="{{ $key }}" style="width:100%;">
                                        <option value="">Select Field</option>         
                                        @if($attributes)                               
                                            @foreach ($attributes as $attribute)  
                                            <option value="{{ $attribute['id'] }}"  >{{ $attribute['name'] }}</option>                       
                                            @endforeach
                                            @endif
                                    </select>
                                </td>
                            </tr> 
                        @endif
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

    </script>
@stop
