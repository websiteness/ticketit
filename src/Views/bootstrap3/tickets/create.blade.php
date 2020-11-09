@extends($master)
@section('page', trans('ticketit::lang.create-ticket-title'))


@section('header_styles')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('content')
@include('ticketit::shared.header')

<div class="ticket-system">
    <div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                    <h2><img src="{{asset('images/ticket-system/new-ticket.png')}}" alt="" /> Create New Ticket</h2>
                    </div><!-- .x_title -->
                    <div class="x_content">
                        {!! CollectiveForm::open([
                            'route'=>$setting->grab('main_route').'.store',
                            'method' => 'POST',
                            'class' => 'form-horizontal',
                            'id' => 'create_form'
                        ]) !!}
                        <div class="new-ticket__form">
                            @if($user->ticketit_admin || $user->ticketit_agent)
                            <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/ticket-description.png')}}" alt="" /> User:</label>
                                <select class="form-control select2" name="user_id" required>
                                    <option value="">Select Owner</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name . ' - ' . $user->email . ' (' . $user->roles->first()->name . ')' }}</option>
                                    @endforeach
                                </select>
                            </div><!-- .new-ticket__form-group -->
                            @endif
                            <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/ticket-priority.png')}}" alt="" /> Ticket Priority:</label>
                                {!! CollectiveForm::select('priority_id', $priorities, null, ['class' => 'new-ticket__form-select', 'placeholder' => 'Please Select', 'required' => 'required']) !!}
                            </div><!-- .new-ticket__form-group -->
                            <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/category-subcategory.png')}}" alt="" /> Category:</label>
                                {!! CollectiveForm::select('category_id', $categories, null, ['class' => 'new-ticket__form-select cat', 'placeholder' => 'Please Select','required' => 'required', 'onchange' => 'selectCategory(this.value)']) !!}
                            </div><!-- .new-ticket__form-group -->
                            <div class="new-ticket__form-group subcat">
                            </div>
                            {!! CollectiveForm::hidden('agent_id', 'auto') !!}
                            @if (Request::is($setting->grab('main_route_path')."/crm-ticket"))
                                {!! CollectiveForm::hidden('ticket_for', 'superadmin') !!}
                            @endif
                            <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/ticket-subject.png')}}" alt="" /> Subject:</label>
                                {!! CollectiveForm::text('subject', null, ['class' => 'new-ticket__form-input', 'placeholder' => 'Subject of the question or issue...', 'required' => 'required']) !!}
                                <!-- <span class="maximum-characters maximum-characters--right">Maximum 250 Characters (250 Remaining)</span> -->
                            </div><!-- .new-ticket__form-group -->
                            <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/ticket-description.png')}}" alt="" /> Description:</label>
                                <!-- <textarea name="" class="new-ticket__form-textarea" placeholder="Write full description of your issue or question here."></textarea> -->
                                {!! CollectiveForm::textarea('content', null, ['class' => 'new-ticket__form-textarea summernote-editor', 'rows' => '5', 'required' => 'required']) !!}
                                <!-- <span class="maximum-characters maximum-characters--right">Maximum 250 Characters (250 Remaining)</span> -->
                            </div><!-- .new-ticket__form-group -->
                            {{-- <div class="new-ticket__form-group">
                                <label><img src="{{asset('images/ticket-system/ticket-attachment.png')}}" alt="" /> Attachment:</label>
                                <input type="file" name="" class="new-ticket__form-input" placeholder="Upload files...">
                                <span class="maximum-characters">Up To 5 Attachments. Each Less Than 3MB</span>
                            </div><!-- .new-ticket__form-group --> --}}
                            <div class="new-ticket__form-group new-ticket__form-group-btn ticket-info__actions">
                                <!-- <button class="custom-btn cancel-btn">Cancel</button>
                                <button class="custom-btn reset-btn">Reset</button>
                                <button class="custom-btn submit-btn">Submit</button> -->
                                {!! link_to_route($setting->grab('main_route').'.index', 'Back', null, ['class' => 'custom-btn cancel-btn']) !!}
                                <!-- <button class="custom-btn reset-btn">Reset</button> -->
                                <button type="button" class="custom-btn reset-btn" onclick="resetForm()">Reset</button>
                                <button class="custom-btn submit-btn">Create Ticket</button>
                            </div><!-- .class="new-ticket__form-group -->
                        </div><!-- .new-ticket__form -->
                    {!! CollectiveForm::close() !!}
                    </div><!-- x_content -->
                </div><!-- .x_panel -->
            </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
        </div><!-- .ticket-system__tabs -->
    </div><!-- .ticket-system__tabs -->
</div><!-- .ticket-system -->
@endsection

@section('footer')
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    @include('ticketit::tickets.partials.summernote')

    <script>
        let default_subcategory = `<label><img src="{{asset('images/ticket-system/category-subcategory.png')}}" alt="" /> Sub Category:</label>
                        <select class="new-ticket__form-select" name="subcategory_id" required disabled>
                            <option selected="selected" value="">Please Select</option>
                        </select>`;

        $('document').ready(function(){
            
			$('.select2').select2();

            var subcategories = {!! json_encode($subcategories) !!};
            let val = $('.cat option:selected').val();
            if(typeof(subcategories[val]) !== 'undefined' && subcategories[val] !== '' && subcategories[val] !== null){
                                    
                $('.subcat').html(generateDropdown(val,subcategories));
            }else{
                $('.subcat').html(default_subcategory);
            }
        });

        function selectCategory(ev){
            var subcategories = {!! json_encode($subcategories) !!};
            if(typeof(subcategories[ev]) !== 'undefined' && subcategories[ev] !== '' && subcategories[ev] !== null){
                $('.subcat').html(generateDropdown(ev,subcategories));
            }else{
                $('.subcat').html(default_subcategory);
            }
        }
        // generate Dropdown Element HTML
        function generateDropdown(id,subcategories)
        {
            var options = ''
            subcategories[id].forEach(function(item, index){
                options += '<option value="'+item.id+'">'+item.name+'</option>'
            });

            let el = `<label><img src="{{asset('images/ticket-system/category-subcategory.png')}}" alt="" /> Sub Category:</label>
                        <select class="new-ticket__form-select" name="subcategory_id" required>
                            <option selected="selected" value="">Please Select</option>
                            ${options}
                        </select>`;
            return el;
        }

        function resetForm() {
            document.getElementById("create_form").reset();
            $("textarea.summernote-editor").summernote('code', '');
        }
    </script>
@append