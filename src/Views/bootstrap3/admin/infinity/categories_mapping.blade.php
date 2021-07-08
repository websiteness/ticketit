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
            <form method="POST" action="{{ route($setting->grab('admin_route').'.infinity.categories.store') }}">
                {{ csrf_field() }}
                <table class="notification-settings__tbl table table-bordered">
                    <thead>
                        <tr>
                            <td>Category</td>
                            <td>Value</td>
                        </tr>
                    </thead>
                    <tbody>            
                    <hr>           
                    @foreach ($categories as $category)                                                      
                        <tr>               
                            <td>{{ $category->name}}</td>
                            <td width="30%">
                                <select class="select2 form-control" name="categories[{{ $category->id }}]" style="width:100%;">
                                    <option value="">Select Status</option>
                                    @if($infinity_categories)
                                        @foreach ($infinity_categories as $infinity_category)
                                            @if(isset($category->infinity_category_id))
                                                <option value="{{ $infinity_category['id'] }}" {{$category->infinity_category_id == $infinity_category['id'] ? 'selected' : '' }}>{{ $infinity_category['name'] }}</option>                                
                                            @else
                                                <option value="{{ $infinity_category['id'] }}" >{{  $infinity_category['name'] }}</option>
                                            @endif
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
