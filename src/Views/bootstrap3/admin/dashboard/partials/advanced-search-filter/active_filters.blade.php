<div id="filters" class="asf__filter-content" data-filter-content-type="list">
    <div class="asf__header">
        <h3>Filters</h3>
    </div>
    <!-- .asf__header -->
    <div class="asf__item">
        <div class="asf__add-filters_cont" style="display: block;">
            <h4>Add Filter:</h4>

            <div class="asf__search-wrapper">
                <input type="text" id="txt_search_filters" name="txt_search_filters" class="asf__search-input" placeholder="Search">
                <button class="asf__search-submit"><span class="icon-search"></span></button>
            </div>
            <!-- .asf__search-wrapper -->
            <div class="asf__search-list-options-wrapper">
                <ul class="asf__search-list-options">
                    <li class="search_term" data-search-term="ignore users">
                        <button id="asf-tags" class="asf__search-list-option-btn btn_filter_name" data-filter-name="ignore_users"><span class="icon-gear"></span> Ignore Users</button>
                    </li>
                    <li class="search_term" data-search-term="user">
                        <button id="asf-username" class="asf__search-list-option-btn btn_filter_name" data-filter-name="user"><span class="icon-gear"></span> User</button>
                    </li>
                    <li class="search_term" data-search-term="ticket age">
                        <button id="asf-ticket-age" class="asf__search-list-option-btn btn_filter_name" data-filter-name="ticket_age"><span class="icon-gear"></span> Ticket Age</button>
                    </li>
                    <li class="search_term" data-search-term="test user super admin">
                        <button id="asf-ticket-age" class="asf__search-list-option-btn btn_filter_name" data-filter-name="others"><span class="icon-gear"></span> Others</button>
                    </li>
                </ul>
            </div>
            <!-- .asf__search-list-options-wrapper -->
            <div class="no_filter_found_msg">No filter found.</div>
        </div>
        <!-- .asf__add-filter -->
        <div class="asf__added-filters_cont" style="display: block;">
            <div class="asf__search-item-added" data-filter-name="ignore_users">
                <p><a href="javascript:void(0);" class="active_tag_filter anc_active_filter_name" data-filter-name="ignore_users">Ignore Users</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_ticket_dashboard_reset_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div class="asf__search-item-added" data-filter-name="user">
                <p><a href="javascript:void(0);" class="active_user_filter anc_active_filter_name" data-filter-name="user" >User</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_ticket_dashboard_reset_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div class="asf__search-item-added" data-filter-name="ticket_age">
                <p><a href="javascript:void(0);" class="active_ticket_age_filter anc_active_filter_name" data-filter-name="ticket_age">Ticket Age</a> Filter is Active</p>
                <button class="asf__search-item-remove btn_ticket_dashboard_reset_filter"><span class="icon-bin"></span></button>
            </div>
            <!-- .asf__search-item-added -->
            <div class="asf__search-item-added" data-filter-name="ignore_test_accounts">
                <p><a href="javascript:void(0);" class="active_ticket_age_filter anc_active_filter_name" data-filter-name="others">Ignore Test Accounts </a> Filter is Active</p>
                <button class="asf__search-item-remove btn_ticket_dashboard_reset_filter"><span class="icon-bin"></span></button>
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