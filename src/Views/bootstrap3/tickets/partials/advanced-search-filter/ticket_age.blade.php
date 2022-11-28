<div id="ticket_filter_ticket_age_container" class="asf__filter-content" style="display: none;">
    <div class="asf__item">
        <h4>Older Than:</h4>

        <div class="asf__select-wrapper padding-0">

            <span id="ticket_selected_duration_label_cont" class="asf__select-custom-main">
                <span class="ticket_selected_duration_clear">&times;</span>
                <span id="ticket_selected_duration_label">Select Days</span>
            </span>

        </div>
        <!-- .asf__select-wrapper -->
        <div class="asf__select-custom-options">
            <div class="asf__select-custom-date-wrapper">
                <div class="asf__select-custom-date">
                    <input type="text" name="" id="ticket_filter_custom_date" class="asf__custom-input" placeholder="Custom">
                    <span class="icon-calender-1 date-icon"></span>
                </div>
                <!-- .asf__select-custom-date -->
            </div>
            <!-- .asf__select-custom-date-wrapper -->
            <ul class="asf__select-custom-options-list">
                <li>
                    <button id="btn_ticket_today" class="asf__custom-option-btn">Today</button>
                </li>
                <li>
                    <button id="btn_ticket_yesterday" class="asf__custom-option-btn">Yesterday</button>
                </li>
                <li>
                    <button id="btn_ticket_last_7_days" class="asf__custom-option-btn">Last 7 Days</button>
                </li>
                <li>
                    <button id="btn_ticket_last_30_days" class="asf__custom-option-btn">Last 30 Days</button>
                </li>
                <li>
                    <button id="btn_ticket_this_month" class="asf__custom-option-btn">This Month</button>
                </li>
                <li>
                    <button id="btn_ticket_last_month" class="asf__custom-option-btn">Last Month</button>
                </li>
            </ul>
        </div>
        <!-- .asf__select-custom-options -->
        <ul class="asf__btn-actions">
            <li>
                <button class="custom-btn asf__cancel-btn"><span class="icon-close icon-gradient"></span> Cancel</button>
            </li>
            <li>
                <button class="custom-btn asf__save-btn btn_ticket_save_ticket_age"><span class="icon-save icon-gradient"></span> Save</button>
            </li>
        </ul>
    </div>
    <!-- .asf__item -->
</div><!-- .asf__filter-content -->