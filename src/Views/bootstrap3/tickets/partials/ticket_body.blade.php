<div class="ticket-system">
    <div class="ticket-system__tabs" role="tabpanel" data-example-id="togglable-tabs">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <h2><img src="{{asset('images/ticket-system/reputation-management-issue.png')}}" alt="" /> Reputation Management Issue.</h2>
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
                        <td width="40%"><span class="active-tickets__text">{{ $ticket->user_id == $u->id ? $u->name : $ticket->user->name }}</span></td>
                        @if($u->isAgent() || $u->isAdmin())
                        <td width="12%"><h5 class="active-tickets__heading">Responsible:</h5></td>
                        <td width="40%">
                            @if($u->isAdmin())
                                {!! CollectiveForm::select(
                                    'agent_id',
                                    $agent_lists,
                                    $ticket->agent_id,
                                    ['class' => 'form-control']) !!}
                            @else
                                {{ $ticket->agent_id == $u->id ? $u->name : $ticket->agent->name }}
                                {!! CollectiveForm::hidden('agent_id', $ticket->agent_id ) !!}
                            @endif
                            </span>
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
                        <td width="10%"><h5 class="active-tickets__heading">Sub Category:</h5></td>
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
                                {{ $ticket->priority->name }}
                            @endif
                        </div>
                      </td>
                      @if($u->isAgent() || $u->isAdmin())
                      <td width="10%"><h5 class="active-tickets__heading">Sub Category:</h5></td>
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
                    @if($u->isAgent() || $u->isAdmin())
                    <tr>
                      <td width="10%"><h5 class="active-tickets__heading">Created:</h5></td>
                      <td width="40%"><span class="active-tickets__text">{{ $ticket->created_at->format('m/d/Y') . ' (' . $ticket->created_at->diffForHumans() . ')' }}</span></td>
                      <td width="10%"></td>
                      <td width="40%"></td>
                    </tr>
                    @endif
                  </tbody>
                </table>
                @if($u->isAgent() || $u->isAdmin())
                    {!! CollectiveForm::submit('Update', ['class' => 'btn btn-success ticket-update-btn']) !!}
                @endif
                {!! CollectiveForm::close() !!}
              </div><!-- x_content -->
            </div><!-- .x_panel -->
          </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
        </div><!-- .col-md-12 col-sm-12 col-xs-12 -->
    </div>
</div>