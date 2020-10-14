<div class="panel panel-default filters-panel">
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li role="presentation">
                <div class="form-group">
                    <label>Owner</label>
                    <div>
                    <select class="form-control select2" id="filter_owner">
                        <option value="">Select Owner</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
            </li>
            <li role="presentation">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" id="filter_status">
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </li>
            <li role="presentation">
                <div class="form-group">
                    <label>Message</label>
                    <input type="text" class="form-control" id="filter_message" placeholder="Search messages" style="width:350px;"/>
                </div>
            </li>
            <li role="presentation">
                <button type="button" class="btn btn-success" onclick="filterTickets()" style="margin-top:23px;margin-right:0;">Search</button>
            </li>
            <li role="presentation">
                <button type="button" class="btn btn-danger" onclick="clearFilters()" style="margin-top:23px;">Clear</button>
            </li>
            <li role="presentation">
                <div class="form-group">
                    <label for="filter_hide_closed_tickets">
                    <input type="checkbox" id="filter_hide_closed_tickets" onclick="filterTickets()" style="margin-top:30px;" checked/> Hide closed tickets
                    </label>
                </div>
            </li>
        </ul>
    </div>
</div>
