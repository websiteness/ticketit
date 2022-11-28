<div id="filters" class="asf__filter-content">
    <div class="asf__header">
        <h3>Filters</h3>
    </div>
    <!-- .asf__header -->
    <div class="asf__item">
        <div class="asf__add-filter ticket_filter_name_list_cont" style="display: block;">
            <h4>Add Filter:</h4>

            <div class="asf__search-wrapper">
                <input type="text" id="txt_search_filters" name="txt_search_filters" class="asf__search-input" placeholder="Search">
                <button class="asf__search-submit"><span class="icon-search"></span></button>
            </div>
            <!-- .asf__search-wrapper -->
            <div class="asf__search-list-options-wrapper">
                <ul class="asf__search-list-options">
                    <li class="search_term" data-search-term="tags">
                        <button id="asf-tags" class="asf__search-list-option-btn"><span class="icon-gear"></span> Tags</button>
                    </li>
                    <li class="search_term" data-search-term="user name">
                        <button id="asf-username" class="asf__search-list-option-btn"><span class="icon-gear"></span> User Name</button>
                    </li>
                    <li class="search_term" data-search-term="ticket number">
                        <button id="asf-ticket-number" class="asf__search-list-option-btn"><span class="icon-gear"></span> Ticket Number</button>
                    </li>
                    <li class="search_term" data-search-term="subject">
                        <button id="asf-subject" class="asf__search-list-option-btn"><span class="icon-gear"></span> Ticket Subject</button>
                    </li>
                    <li class="search_term" data-search-term="ticket status">
                        <button id="asf-ticket-status" class="asf__search-list-option-btn"><span class="icon-gear"></span> Ticket Status</button>
                    </li>
                    <li class="search_term" data-search-term="priority">
                        <button id="asf-priority" class="asf__search-list-option-btn"><span class="icon-gear"></span> Ticket Priority</button>
                    </li>
                    <li class="search_term" data-search-term="ticket age">
                        <button id="asf-ticket-age" class="asf__search-list-option-btn"><span class="icon-gear"></span> Ticket Age</button>
                    </li>
                </ul>
            </div>
            <!-- .asf__search-list-options-wrapper -->
            <div class="no_filter_found_msg">No filter found.</div>
        </div>
        <!-- .asf__add-filter -->
        <div class="asf__search-item-added-wrapper ticket_active_filters_msg_cont" style="display: block;">
            <div id="asf-tag-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_tag_filter">Tag</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_tag_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-username-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_user_filter">Username</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_user_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-ticket-number-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_ticket_number_filter">Ticket Number</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_ticket_number_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-subject-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_ticket_subject_filter">Ticket Subject</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_ticket_subject_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-ticket-status-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_ticket_status_filter">Ticket Status</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_ticket_status_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-priority-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_ticket_priority_filter">Ticket Priority</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_ticket_priority_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div id="asf-ticket-age-added" class="asf__search-item-added">
                <p><a href="javascript:void(0);" class="active_ticket_age_filter">Ticket Age</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_reset_ticket_age_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
        </div>
        <!-- .asf__search-item-added-wrapper -->
        <ul class="asf__btn-actions">
            <li>
                <button class="custom-btn asf__back-btn btn_ticket_filters_back"><span class="icon-arrow icon-gradient"></span> Back</button>
            </li>
            <li>
                <button class="custom-btn and-btn btn_ticket_filters_and">And</button>
            </li>
        </ul>
    </div>
    <!-- .asf__item -->
</div><!-- .asf__filter-content -->