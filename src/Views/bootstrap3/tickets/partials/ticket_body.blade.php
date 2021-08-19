@push('header_styles')
<style>
.active-tickets__text a {
  text-decoration: underline;
  font-size: 12px;
}
.active-tickets__text {
  position: relative;
}
.active-tickets__text label {
  cursor: pointer;
  position: absolute;
  right: 0;
  padding: 5px;
}
.mt {
  margin-top: 2vh;
}
</style>
@endpush
        
<div class="ticket-system">
    <div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <h2><img src="{{asset('images/ticket-system/reputation-management-issue.png')}}" alt="" /> {{ $ticket->subject }}</h2>
              </div><!-- .x_title -->
              <div class="x_content ticket-body">
                {!! CollectiveForm::model($ticket, [
                    'route' => [$setting->grab('main_route').'.update', $ticket->id],
                    'method' => 'PATCH',
                    'class' => 'form-horizontal'
                ]) !!}
                <table class="active-tickets__tbl">
                  <tbody>
                    <tr>
                        <td width="10%"><h5 class="active-tickets__heading">Owner:</h5></td>
                        <td width="40%"><span class="active-tickets__text">
                          {{ $ticket->user_id == $u->id ? $u->name : $ticket->user->name }} 
                          @if($u->isAgent() || $u->isAdmin())
                          <a href="{{ route('accounts.show', $ticket->user_id) }}">{{ $ticket->user->email }}</a>
                          <label class="label label-default" onclick="copyEmail('{{ $ticket->user->email }}')"><i class="fa fa-copy"></i></label>
                          @endif
                          </span>
                        </td>
                        @if($u->isAgent() || $u->isAdmin())
                        <td width="12%"><h5 class="active-tickets__heading">Created:</h5></td>
                        <td width="40%">
                     
                       <span class="active-tickets__text">{{ $ticket->created_at->format('m/d/Y') . ' (' . $ticket->created_at->diffForHumans() . ')' }}</span>
                        </td>
                        @else
                        <td width="10%"><h5 class="active-tickets__heading">Category:</h5></td>
                        <td width="40%"><span class="active-tickets__text">{{ isset($ticket->category->parent_category->name) ? $ticket->category->parent_category->name : $ticket->category->name }}</span></td>
                        @endif
                    </tr>
                    <tr>
                      <td width="10%"><h5 class="active-tickets__heading">Status:</h5></td>
                      <td width="40%">
                        <div class="active-tickets__editable">
                            @if($u->isAgent() || $u->isAdmin())
                                <span class="active-tickets__text">
                                {!! CollectiveForm::select('status_id', $status_lists, $ticket->status_id, ['class' => 'form-control']) !!}
                                </span>
                                <a class="editable-ticket" href="#"></a>
                            @else
                                @if($ticket->status_id == 2)
                                    Waiting on feedback from {{ $ticket->user->name }}
                                @else
                                    {{ $ticket->status->name }}
                                @endif
                            @endif
                        </div>
                      </td>
                      @if($u->isAgent() || $u->isAdmin())
                      <td width="10%"><h5 class="active-tickets__heading">Category:</h5></td>
                      <td width="40%"><span class="active-tickets__text">{{ isset($ticket->category->parent_category->name) ? $ticket->category->parent_category->name : $ticket->category->name }}</span></td>
                        @else
                        <td width="10%"><h5 class="active-tickets__heading">Module:</h5></td>
                        <td width="40%"><span class="active-tickets__text">
                            @if($ticket->category->parent_category)
                                {{ $ticket->category->name }}
                            @endif
                            </span></td>
                        @endif
                    </tr>
                    <tr>
                      <td width="10%"><h5 class="active-tickets__heading">Priority:</h5></td>
                      <td width="40%" class="priority-status priority-status__moderate">
                        <div class="active-tickets__editable">
                            @if($u->isAgent() || $u->isAdmin())
                                <span class="active-tickets__text">
                                {!! CollectiveForm::select('priority_id', $priority_lists, $ticket->priority_id, ['class' => 'form-control']) !!}
                                </span>
                                <a class="editable-ticket" href="#"></a>
                            @else
                                <span class="active-tickets__text">
                                {!! CollectiveForm::select('priority_id', $priority_lists, $ticket->priority_id, ['class' => 'form-control']) !!}
                                </span>
                                <a class="editable-ticket" href="#"></a>
                            @endif
                        </div>
                      </td>
                      @if($u->isAgent() || $u->isAdmin())
                      <td width="10%"><h5 class="active-tickets__heading">Module:</h5></td>
                      <td width="40%"><span class="active-tickets__text">
                        @if($ticket->category->parent_category)
                            {{ $ticket->category->name }}
                        @endif
                        </span></td>
                        @else
                        <td width="10%"><h5 class="active-tickets__heading">Last Update:</h5></td>
                        <td width="40%"><span class="active-tickets__text">{{ $ticket->updated_at->diffForHumans() }}</span></td>
                        @endif
                    </tr>
                    <tr>
                        <td width="10%"><h5 class="active-tickets__heading">Ticket #:</h5></td>
                        <td width="40%"><span class="active-tickets__text">{{ $ticket->id }}</span></td>
                        @if($u->isAgent() || $u->isAdmin())
                        <td width="10%"><h5 class="active-tickets__heading">Last Update:</h5></td>
                        <td width="40%"><span class="active-tickets__text">{{ $ticket->updated_at->diffForHumans() }}</span></td>
                        @else
                      <td width="10%"><h5 class="active-tickets__heading">Created:</h5></td>
                      <td width="40%"><span class="active-tickets__text">{{ $ticket->created_at->format('m/d/Y') . ' (' . $ticket->created_at->diffForHumans() . ')' }}</span></td>
                        @endif
                    </tr>

                  </tbody>
                </table>
                @if($u->isAdmin())
                <div class="x_panel mt">
                      <div class="x_content">
                        <h5>Internal inputs: </h5>
                      </div>
                      <div class="row">
                        <div class="col-md-3"> {{ CollectiveForm::label('Developer') }} {!! CollectiveForm::select('agent_id',$agent_lists,$ticket->agent_id,['class' => 'form-control']) !!} </div>
                        <div class="col-md-4"> {{ CollectiveForm::label('Estimated Completion Date') }} {!! CollectiveForm::date('completion_date', $ticket->completion_date, ['class' => 'form-control']) !!} </div>  
                        <div class="col-md-2"> {{ CollectiveForm::label('# of hours') }} {!! CollectiveForm::number('dev_hours',$ticket->dev_hours,['class' => 'form-control', 'placeholder' => 'Estimated hours']) !!}  </div>           
                        <div class="col-md-3"> {{ CollectiveForm::label('Developer status') }} {!! CollectiveForm::select('dev_status_id',$dev_statuses,$ticket->dev_status_id,['class' => 'form-control']) !!} </div>                    
                        
                        <div class="form-group">
                          <div class="col-lg-12 mt">
                              {{ CollectiveForm::label('Developer Notes') }}
                              {!! CollectiveForm::textarea('dev_notes', null, ['class' => 'form-control add-notes-summernote', 'rows' => "3"]) !!}
                          </div>
                      </div>
                      </div>              
                  </div>
                  @endif
                  </div><!-- x_content -->
              </div><!-- .x_panel -->
                {!! CollectiveForm::submit('Update', ['class' => 'btn btn-success ticket-update-btn']) !!}
                {!! CollectiveForm::close() !!}
          </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
        </div><!-- .col-md-12 col-sm-12 col-xs-12 -->

 
    </div>
</div>

@push('footer_scripts')
<script>
  function copyEmail(email)
  {
    console.log('email', email);
    let input = document.createElement('input');
    input.value = email;
    
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
  }
</script>
@endpush