@push('header_styles')
<style>
    .ticket-comment__message-content {
        word-wrap: break-word;
    }
    .comment-comment__actions {
        margin-top: 20px;
    }
    .comment-comment__actions button {
        padding: 0 5px;
    }
</style>
@endpush
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
                                    <span class="ticket-comment__time-delivered">
                                        <span class="ticket-comment__date">
                                            {{ $ticket->created_at->format('m/d/Y') }}
                                        </span>
                                        {{'(' . $ticket->created_at->diffForHumans() . ')' }}
                                    </span>
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

                                        @if($u->isAdmin())
                                        <div class="comment-comment__actions">
                                            <button class="btn btn-sm pull-left" data-toggle="modal" data-target="#editCommentModal" onclick="editComment('{{ $comment->id }}', '{{ $comment->html }}')" ><i class="fa fa-pencil"></i></button>
                                            
                                            <form method="POST" action="{{ route($setting->grab('main_route').'-comment.destroy', $comment->id) }}" onsubmit="return confirm('Delete this comment?')">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                                <button class="btn btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                        @endif
                                    </div><!-- .ticket-comment__message-content -->
                                    <span class="ticket-comment__time-delivered"><span class="ticket-comment__date">{{ $ticket->created_at->format('m/d/Y') }}</span> {{'(' . $ticket->created_at->diffForHumans() . ')' }}</span>
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

                                        @if($u->isAdmin())
                                        <div class="comment-comment__actions">
                                            <button class="btn btn-sm pull-left" data-toggle="modal" data-target="#editCommentModal" onclick="editComment('{{ $comment->id }}', '{{ $comment->html }}')" ><i class="fa fa-pencil"></i></button>
                                            
                                            <form method="POST" action="{{ route($setting->grab('main_route').'-comment.destroy', $comment->id) }}" onsubmit="return confirm('Delete this comment?')">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                                <button class="btn btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                        @endif
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

<!-- Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form method="POST" id="edit_comment_form" class="form-horizontal">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Comment</h5>
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button> -->
            </div>
            <div class="modal-body">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <fieldset>
                    <div class="form-group">
                        <div class="col-lg-12">
                            {!! CollectiveForm::textarea('content', null, ['class' => 'form-control edit-comment-summernote-editor', 'rows' => "3"]) !!}
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
  </div>
</div>

@push('footer_scripts')
<script>
        $('#editCommentModal').on('shown.bs.modal', function() {
            $('.edit-comment-summernote-editor').summernote();
        });

    function editComment(comment_id, content) {
        console.log('comment', comment_id);
        console.log('content', content);

        setTimeout(function() {
            $('.edit-comment-summernote-editor').summernote('destroy');
            $('.edit-comment-summernote-editor').summernote('code', content);
        }, 300);

        let form_url = `{{ route($setting->grab('main_route').'-comment.update', 'comment_id') }}`;
        let final_url = form_url.replace('comment_id', comment_id);

        document.getElementById('edit_comment_form').setAttribute('action', final_url);
    }
</script>
@endpush
