<div class="ticket-system">
    <div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2><img src="{{asset('images/ticket-system/ticket-comment.png')}}" alt="" /> Comments.</h2>
                    </div><!-- .x_title -->
                    <div class="x_content">
                        <div class="ticket-comments">
                            <div class="ticket-comment__item">
                                <div class="ticket-comment__avatar">
                                    <img src="{{asset('images/ticket-system/support-use-thumbnail.png')}}" alt="">
                                </div><!-- .ticket-comment__avatar -->
                                <div class="ticket-comment__message">
                                    <div class="ticket-comment__message-content">
                                        <h5>{{ $ticket->user->name }}:</h5>
                                            {!! $ticket->html !!}
                                    </div><!-- .ticket-comment__message-content -->
                                    <span class="ticket-comment__time-delivered"><span class="ticket-comment__date">{{ $ticket->created_at->format('m/d/Y') . ' (' . $ticket->created_at->diffForHumans() . ')' }}</span>
                                </div><!-- .ticket-comment__message -->
                            </div><!-- .ticket-comment__item -->
                            @if(!$comments->isEmpty())
                            @foreach($comments as $comment)
                            @if(!$comment->user->ticketit_admin && !$comment->user->ticketit_agent)
                            <div class="ticket-comment__item">
                                <div class="ticket-comment__avatar">
                                    <img src="{{asset('images/ticket-system/support-use-thumbnail.png')}}" alt="">
                                </div><!-- .ticket-comment__avatar -->
                                <div class="ticket-comment__message">
                                    <div class="ticket-comment__message-content">
                                        <h5>{{ $comment->user->name }}:</h5>
                                        {!! $comment->html !!}
                                    </div><!-- .ticket-comment__message-content -->
                                    <span class="ticket-comment__time-delivered"><span class="ticket-comment__date">{{ $comment->created_at->format('m/d/Y') . ' (' . $comment->created_at->diffForHumans() . ')' }}</span>
                                </div><!-- .ticket-comment__message -->
                            </div><!-- .ticket-comment__item -->
                            @else
                            <div class="ticket-comment__item ticket-comment__item--replied">
                                <div class="ticket-comment__message">
                                    <div class="ticket-comment__message-content">
                                        <h5>
                                            @if($u->isAgent() || $u->isAdmin())
                                                {{ $comment->user->name }}:
                                            @else
                                                Lead Generated Support:
                                            @endif
                                        </h5>
                                        {!! $comment->html !!}
                                    </div><!-- .ticket-comment__message-content -->
                                    <span class="ticket-comment__time-delivered"><span class="ticket-comment__date">{{ $comment->created_at->format('m/d/Y') . ' (' . $comment->created_at->diffForHumans() . ')' }}</span>
                                </div><!-- .ticket-comment__message -->
                                <div class="ticket-comment__avatar">
                                    <img src="{{asset('images/ticket-system/support-thumbnail.png')}}" alt="">
                                </div><!-- .ticket-comment__avatar -->
                            </div><!-- .ticket-comment__item -->
                            @endif
                            @endforeach
                            @endif
                        </div><!-- .ticket-comments -->
                    </div><!-- x_content -->
                </div><!-- .x_panel -->
            </div>
        </div>
    </div>
</div>