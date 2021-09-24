@extends('layouts.app')
{{-- Page title --}}
@section('title')
All Advisories
@parent
@stop
{{-- page level styles --}}
@section('header_styles')
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
    
    .advanced-search-filter__tabs .tab-content-inner{
        overflow-y: hidden;
    }
    
    .column-credit-cntr{
        margin:5px;
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

    .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
        border-top: none !important; 
    }

    .btn-info {
      width: 100px !important;
    }
    .advanced-search-filter__tabs .tab-content-inner{
        overflow-y: hidden;
    }
    
   .column-credit-cntr{
        margin:5px;
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

.select2-results__option {
  padding-right: 20px;
  vertical-align: middle;
}

.select2-results__option:before {
  content: "";
  display: inline-block;
  position: relative;
  height: 20px;
  width: 20px;
  border: 2px solid #e9e9e9;
  border-radius: 4px;
  background-color: #fff;
  margin-right: 20px;
  vertical-align: middle;
}

.select2-results__option[aria-selected=true]:before {
  font-family:fontAwesome;
  content: "\f00c";
  color: #fff;
  background-color: #f77750;
  border: 0;
  display: inline-block;
  padding-left: 3px;
}
.select2-container--default .select2-results__option[aria-selected=true] {
	background-color: #fff;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
	background-color: #eaeaeb;
	color: #272727;
}
.select2-container--default .select2-selection--multiple {
	margin-bottom: 10px;
}
.select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple {
	border-radius: 4px;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
	border-color: #f77750;
	border-width: 2px;
}
.select2-container--default .select2-selection--multiple {
	border-width: 2px;
}


.select-icon .select2-selection__placeholder .badge {
	display: none;
}
.select-icon .placeholder {
	display: none;
}
.select-icon .select2-results__option:before,
.select-icon .select2-results__option[aria-selected=true]:before {
	display: none !important;
}
.select-icon  .select2-search--dropdown {
	display: none;
}
.btn-info {
      width: 100px !important;
}
</style>
@stop

@section('content')
{{-- Page content --}}

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2> Tag: {{ $tag->id }}</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="POST" action="{{ route($setting->grab('main_route_path').'.tags.update',  $tag->id ) }}">
       
                <div class="form-horizontal form-label-left"> 
                {{ csrf_field() }}
                {!! method_field('patch') !!}
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Name<span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="icon" placeholder="Name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="{{ $tag->name ?? '' }}">
                        </div>
                    </div>
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <a href="" class="btn btn-primary" type="button">Cancel</a>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

       
@stop
@section('footer_scripts')
<script src="{{asset('libs/sweetalert/js/sweetalert.min.js')}}"></script>
@stop