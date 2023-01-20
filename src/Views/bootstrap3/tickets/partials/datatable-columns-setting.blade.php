<div id="ticket-datatable-columns-setting-container" class="datatable_columns_setting_section_container">
    <div class="datatable_columns_setting_section_inner">
        <div class="datatable_columns_setting_section_header">
            <h2>Columns</h2>
            <span>Select the columns you want to apply on table.</span>
            <button id="ticket-datatable-columns-setting-container-close" class="datatable_columns_setting_section_close"><span class="icon-close"></span></button>
        </div>
        <!-- .datatable_columns_setting_section-header -->
        <div class="datatable_columns_setting_section_body">
            <div class="datatable_columns_setting_section_content">
                <div class="datatable_columns_setting_section_column_search_wrapper">
                    <input type="text" id="txt_search_ticket_columns" name="txt_search_ticket_columns" class="datatable_columns_setting_section_column_search_input" placeholder="Search">
                    <button class="datatable_columns_setting_section_column_search_submit"><span class="icon-search"></span></button>
                </div>
                <div class="datatable_columns_setting_section_columns_container">
                    <div class="datatable_columns_setting_section_columns_show_hide_all_container">
                        <label for="chk_datatable_column_show_hide_all">Show All</label>
                        <div class="datatable-column-visibility-toggle-switcher-wrapper">
                            <input id="chk_datatable_column_show_hide_all" type="checkbox" value="1" class="datatable-column-visibility-toggle-switcher chk_datatable_column_show_hide_all">
                        </div>
                    </div>
                    <ul class="datatable_columns_setting_section_columns_list_container">
                        <li class="datatable_columns_setting_section_column_container" data-search-term="user">
                            <label for="chk_datatable_columns_setting_section_column_item_user">User</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_user" type="checkbox" value="user" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="subject">
                            <label for="chk_datatable_columns_setting_section_column_item_subject">Subject</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_subject" type="checkbox" value="subject" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="ticket status">
                            <label for="chk_datatable_columns_setting_section_column_item_status">Ticket Status</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_status" type="checkbox" value="status" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="priority">
                            <label for="chk_datatable_columns_setting_section_column_item_priority">Priority</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_priority" type="checkbox" value="priority" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="dev status">
                            <label for="chk_datatable_columns_setting_section_column_item_developer_status">Dev Status</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_developer_status" type="checkbox" value="developer_status" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="tags">
                            <label for="chk_datatable_columns_setting_section_column_item_tags">Tags</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_tags" type="checkbox" value="tags" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="last updated">
                            <label for="chk_datatable_columns_setting_section_column_item_updated_at">Last Updated</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_updated_at" type="checkbox" value="updated_at" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="agent">
                            <label for="chk_datatable_columns_setting_section_column_item_agent">Agent</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_agent" type="checkbox" value="agent" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="category">
                            <label for="chk_datatable_columns_setting_section_column_item_category">Category</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_category" type="checkbox" value="category" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="last reply">
                            <label for="chk_datatable_columns_setting_section_column_item_last_reply">Last Reply</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_last_reply" type="checkbox" value="last_reply" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="zone">
                            <label for="chk_datatable_columns_setting_section_column_item_zone">Zone</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_zone" type="checkbox" value="zone" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                        <li class="datatable_columns_setting_section_column_container" data-search-term="actions">
                            <label for="chk_datatable_columns_setting_section_column_item_actions">Actions</label>
                            <div class="datatable-column-visibility-toggle-switcher-wrapper">
                                <input id="chk_datatable_columns_setting_section_column_item_actions" type="checkbox" value="actions" class="datatable-column-visibility-toggle-switcher chk_datatable_columns_setting_section_column_item">
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="datatable_columns_setting_section_no_column_found_msg">No column found.</div>
            </div>
            <!-- .asf__content -->
        </div>
        <!-- .datatable_columns_setting_section_body -->
    </div>
    <!-- .datatable_columns_setting_section_inner -->
</div><!-- .datatable_columns_setting_section -->