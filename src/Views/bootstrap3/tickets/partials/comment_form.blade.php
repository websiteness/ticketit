<div class="ticket-system">
    <div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    @if(!$u->isAdmin())
                    <div class="x_content">
                        <div class="ticket-info">
                            <h3><img src="{{asset('images/ticket-system/icon-question.png')}}" alt=""> What would you like to do next?</h3>
                            <div class="ticket-info__actions">
                                <button class="custom-btn reply-btn" id="comment_reply">Reply To Support</button>
                                @if(! $ticket->completed_at && $close_perm == 'yes')
                                        {!! link_to_route($setting->grab('main_route').'.complete', 'Close Ticket (Resolved)', $ticket->id,
                                                            ['class' => 'custom-btn reopen-btn']) !!}
                                @elseif($ticket->completed_at && $reopen_perm == 'yes')
                                        {!! link_to_route($setting->grab('main_route').'.reopen', trans('ticketit::lang.reopen-ticket'), $ticket->id,
                                                            ['class' => 'custom-btn reopen-btn']) !!}
                                @endif
                            </div><!-- .ticket-info__actions -->
                        </div><!-- .ticket-info -->
                    </div><!-- x_content -->
                    @endif
                    <div class="x_content" id="comment_form" {{ $u->isAdmin() ? '' : 'style=display:none;' }}>
                        @if(!$u->isAdmin() && !$u->isAgent())
                            {!! CollectiveForm::open(['method' => 'POST', 'route' => $setting->grab('main_route').'-comment.store', 'class' => 'form-horizontal']) !!}
                        @else
                            {!! CollectiveForm::open(['method' => 'POST', 'route' => $setting->grab('main_route').'-comment.store', 'class' => 'form-horizontal comment-form']) !!}
                        @endif
                        
                        {!! CollectiveForm::hidden('ticket_id', $ticket->id ) !!}
                        {!! CollectiveForm::hidden('status_change', null ,['class' => 'status_change']) !!}

                        <fieldset>
                            <!-- <legend>{!! trans('ticketit::lang.reply') !!}</legend> -->
                            <legend></legend>
                            <div class="form-group">
                                <div class="col-lg-12">
                                    {!! CollectiveForm::textarea('content', null, ['class' => 'form-control summernote-editor', 'rows' => "3"]) !!}
                                </div>
                            </div>

                            <div class="col-md-12 ticket-reply__actions">
                                @if(!$u->isAdmin() && !$u->isAgent())
                                        <!-- {!! CollectiveForm::submit( trans('ticketit::lang.btn-submit'), ['class' => 'reply-submit custom-btn submit-btn']) !!} -->
                                        <button type="button" class="custom-btn cancel-btn pull-left" id="cancel_reply">Cancel</button>
                                        <button class="reply-submit custom-btn submit-btn pull-left">Send Reply</button>
                                @else
                                        <!-- {!! CollectiveForm::submit( trans('ticketit::lang.btn-submit'), ['class' => 'custom-btn submit-btn']) !!} -->
                                        @if($u->isAdmin())
                                        <button class="custom-btn submit-btn pull-left">Reply to user</button>
                                        @else
                                        <button type="button" class="custom-btn cancel-btn pull-left" id="cancel_reply">Cancel</button>
                                        <button class="custom-btn submit-btn pull-left">Send Reply</button>
                                        @endif
                                    <!-- </div> -->
                                @endif

                                @if($u->isAdmin())
                                <div class="ticket-info__actions pull-right">
                                    @if(! $ticket->completed_at && $close_perm == 'yes')
                                            {!! link_to_route($setting->grab('main_route').'.complete', 'Close Ticket', $ticket->id,
                                                                ['class' => 'custom-btn reopen-btn']) !!}
                                    @elseif($ticket->completed_at && $reopen_perm == 'yes')
                                            {!! link_to_route($setting->grab('main_route').'.reopen', trans('ticketit::lang.reopen-ticket'), $ticket->id,
                                                                ['class' => 'custom-btn reopen-btn']) !!}
                                    @endif
                                            </div>
                                @endif
                            </div>

                        </fieldset>
                        {!! CollectiveForm::close() !!}
                    </div><!-- x_content -->
                </div><!-- .x_panel -->
            </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
        </div>
    </div>
</div>
