@extends($master)
@section('page', trans('ticketit::lang.show-ticket-title') . trans('ticketit::lang.colon') . $ticket->subject)

@section('header_styles')
<style>
    .ticket-comments a {
        color: #28b999;
        text-decoration: underline;
    }       
    .ticket-sidenavbar-mt-20 {
        margin-top: 20px;
    }
    .fw-600 {
        font-weight: 600;
    }
    .thumbnail {
        height: auto;
    }
    .th-show {
        text-align: left;
    }
   .btn-view-ticket{
        min-width: 10px;
        width: 20px;
        padding: 3px;
        display: inline;
    }
    @media (min-width: 500px) and (max-width: 620px) {
        .ticket-sidenav {
            width: 100%;
        }            
    }
    @media (min-width: 500px) and (max-width: 620px) {
        .row {
            width: 100%;
        }        
    }
    .text-white {
        color: white;
    }
    .caption-bot {
        height: 70vh;
        overflow: auto;
    }
    .ticket-profile-pic {
        padding-top: 10px;
        text-align: center;
    }
    .contact-info{
        padding-left: 10px;
        padding-right: 10px;
        padding-top: 10px;
        padding-bottom: -20px;
    }

    .contact-info h4{
        margin-bottom: -10px;
    }

    .contact-info-details{
        display: flex;
        align-items: center;
    }
    .contact-info-span-main{
        display: block;
    }

    .contact-info-span{
        margin-top: 5px;
        display: block;
        margin-left: 3px;
    }
    .contact-info h3 {
        display: inline;
        vertical-align: middle;
    }
    .contact-info hr {
        margin-top: 10px; 
        margin-bottom: 0px;
    }

    .subscription-span-mg {
        text-align: initial;
    }

    .contact-info-dec-mg{
        margin-top: -17px;
    }
    
    .mt-5{
        margin-top: 5px;
    }
</style>
@stop
@section('content')
@include('ticketit::shared.header')
<div class="container">
    <div class="row">

        @if(Sentinel::inRole('super-admin'))
            <div class="col-lg-8 col-md-8 col-sm-8">
        @elseif(Sentinel::inRole('ticket-agent'))
            <div class="col-lg-8 col-md-8 col-sm-8">
        @else
            <div class="col-lg-12 col-md-12 col-sm-12">
        @endif
                @include('ticketit::tickets.partials.ticket_body')
                <br>
                <!-- <h2>{{ trans('ticketit::lang.comments') }}</h2> -->
                @include('ticketit::tickets.partials.comments')
                {{-- pagination --}}
                {!! $comments->render() !!}
                @include('ticketit::tickets.partials.comment_form')
            </div>

            @if(Sentinel::inRole('super-admin') || Sentinel::inRole('ticket-agent'))
            <div class="col-sm-4 ticket-sidenavbar-mt-20 h-100 ticket-sidenav">
                <div class="thumbnail">
                    <div class="caption">
                        <div class="row ticket-profile-pic">
                            <img src="{{asset('images/user-big.png')}}" class="img-circle" alt="" width="100" height="88">                
                        </div>
                        <div class="row contact-info">
                            <span><img src="{{asset('images/icon-user.png')}}" alt="" width="20px" height="20px"><h3>Contact Info</h3></span>
                            <hr>
                        </div>
                        <div class="text-light">
                            <h4>  {{ $ticket->user->name }}</h4>
                            <p class="fw-600 contact-info-details align-center"> <span class="contact-info-span-main"> <img src="{{asset('images/email-result.png')}}" alt="" width="20px" height="20px"> </span> <span class="contact-info-span" > Email: {{ $ticket->user->email }} </span> </p>  
                            <p class="fw-600 contact-info-details align-center"> <span class="contact-info-span-main"> <img src="{{asset('images/pricing/phone-system.png')}}" alt="" width="20px" height="20px"> </span> <span class="contact-info-span" > Phone: </span> </p>  
                            <p class="fw-600 contact-info-details align-center"> <span class="contact-info-span-main"> <img src="{{asset('images/company-creation-mapping/facebook-hover.png')}}" alt="" width="20px" height="20px"> </span> <span class="contact-info-span" > Facebook: </span> </p>  
                            <p class="fw-600 contact-info-details align-center contact-info-dec-mg mt-5"> <span class="contact-info-span-main"> <img src="{{asset('images/pricing-icon/plan.png')}}" alt="" width="20px" height="20px"> </span> <span class="contact-info-span subscription-span-mg">Subscription Plan: {{ implode(', ', $ticket->user->account->get_plan_names()) }} 
                            </span> </p>         
                        </div>
                        <form action="{{ route('developer.process.login.as.user.submit')}}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="user" value="{{$ticket->user->id }}">
                        <p><button class="btn btn-success btn-block text-sm" role="button" type="submit">Login as user</button> </p>
                        </form>
                        <!-- <p><a class="btn btn-success btn-block text-sm btn-login-as-user" role="button" id="{{$ticket->user->id }}">Login as user</a> </p> -->
                    </div>
                    <div class="caption-bot col-sm">
                        <table class="table table-bordered ">
                            <thead>
                                <tr>
                                    <th >Tickets</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ticket->ticket_user->userTotalTickets as $ticket)
                                <tr>
                                    <th > <small>{{ str_limit($ticket->subject,22) }}</small></th>
                                    <th class="text-center" width="15px">
                                        <a class="btn btn-sm btn-success btn-view-ticket btn-ticket-view" role="button" id="{{ $ticket->id }}"><i class="fa fa-eye"></i></a>
                                    </th>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer')
<script>
    $(document).ready(function() {
        $(".btn-ticket-view").click(function(event) {
            event.preventDefault();
            var id = $(this).attr('id');        
            if (id != undefined && id != null) {
                window.location = '/tickets/' + id;
            }
        });
        $(".deleteit").click(function(event) {
            event.preventDefault();
            if (confirm("{!! trans('ticketit::lang.show-ticket-js-delete') !!}" + $(this).attr("node") + " ?")) {
                var form = $(this).attr("form");
                $("#" + form).submit();
            }
        });
        $('#category_id').change(function() {
            var loadpage = "{!! route($setting->grab('main_route').'agentselectlist') !!}/" + $(this).val() + "/{{ $ticket->id }}";
            $('#agent_id').load(loadpage);
        });
        $('#confirmDelete').on('show.bs.modal', function(e) {
            $message = $(e.relatedTarget).attr('data-message');
            $(this).find('.modal-body p').text($message);
            $title = $(e.relatedTarget).attr('data-title');
            $(this).find('.modal-title').text($title);
            // Pass form reference to modal for submission on yes/ok
            var form = $(e.relatedTarget).closest('form');
            $(this).find('.modal-footer #confirm').data('form', form);
        });
        $('#confirmDelete').find('.modal-footer #confirm').on('click', function() {
            $(this).data('form').submit();
        });
        $('#comment_reply').click(function() {
            $('#comment_form').css('display', 'block');
            $('#comment_reply').css('display', 'none');
        });
        $('#cancel_reply').click(function() {
            $('#comment_form').css('display', 'none');
            $('#comment_reply').css('display', 'block');
        });
    });
</script>
@include('ticketit::tickets.partials.summernote')
{{-- {!! json_encode($status_lists) !!} --}}
<script>
    $('document').ready(function() {

        var subcategories = {!! json_encode($subcategories) !!};
        let val = $('.cat option:selected').val();

        if (typeof(subcategories[val]) !== 'undefined' && subcategories[val] !== '' && subcategories[val] !== null) {
            $('.subcat').html(generateDropdown(val, subcategories));
        } else {
            $('.subcat').html('');
        }

        // Form Submit Handling
        $(".comment-form").submit(function(event) {
            event.preventDefault();
            let sel_el = generateStatusesSelectBox();
            Swal.fire({
                title: 'Do you want to change the status of this ticket?',
                html: sel_el +
                    "<br><br><div class='row col-lg-12' style='margin-top: 20px;'>" +
                    '<button type="button" role="button" tabindex="0" class="btn btn-danger skip-pop">' + 'Skip' + '</button>' +
                    '<button type="button" role="button" tabindex="0" class="btn btn-primary submit-pop">' + 'Submit' + '</button></div>',
                showCancelButton: false,
                showConfirmButton: false
            });
        });
        $(document).on('click', '.submit-pop', function(e) {
            e.preventDefault();
            let value = $('.selected_status option:selected').val();
            console.log(value);
            $('.status_change').val(value);
            console.log('Submit clicked!!!!!!');
            $(".comment-form").off().submit();
            Swal.close();
        });
        $(document).on('click', '.skip-pop', function(e) {
            e.preventDefault();
            $('.status_change').val(null);
            console.log('Submit clicked!!!!!!');
            $(".comment-form").off().submit();
            Swal.close();
        });

    });

    function selectCategory(ev) {
        var subcategories = {!!json_encode($subcategories)!!};
        if (typeof(subcategories[ev]) !== 'undefined' && subcategories[ev] !== '' && subcategories[ev] !== null) {
            $('.subcat').html(generateDropdown(ev, subcategories));
        } else {
            $('.subcat').html('');
        }
    }

    // generate Dropdown Element HTML
    function generateDropdown(id, subcategories) {
        var seleted_ = "{{ $selected_subcategory }}"
        var options = ''
        subcategories[id].forEach(function(item, index) {
            if (seleted_ == item.id) {
                options += '<option selected="selected" value="' + item.id + '">' + item.name + '</option>'
            } else {
                options += '<option value="' + item.id + '">' + item.name + '</option>'
            }
        });
        let el = '<label for="subcategory" class="col-lg-6 control-label">Sub Category: </label>'
        el += '<div class="col-lg-6">';
        el += '<select class="form-control" required="required" name="subcategory_id">';
        el += '<option selected="selected" value="">Please Select</option>';
        el += options;

        el += '</select>';
        el += '</div>';
        return el;
    }

    function generateStatusesSelectBox() {
        var statuses = {!!json_encode($status_lists)!!};
        var options = ''
        Object.keys(statuses).forEach(key => {
            options += '<option value="' + key + '">' + statuses[key] + '</option>'
        });
        let el = '<label for="status-popup" class="col-lg-12 control-label">Select Status: </label>'
        el += '<div class="col-lg-12">';
        el += '<select class="form-control selected_status" name="selected_status">';
        el += '<option selected="selected" value="">Please Select</option>';
        el += options;
        el += '</select>';
        el += '</div>';
        return el;
    }
    // Reply Submit if user is agent or Super Admin
</script>
@append