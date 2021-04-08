<style>
    .ticket_dropdown_filter {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .advanced-search-filter .x_title h2 {
        color: #314456;
        font-size: 24px;
    }

    .advanced-search-filter .btn-group {
        margin-right: 20px;
    }

    .advanced-search-filter__filtering {
        border: 1px solid #e1e1e1;
        border-radius: 3px;
        padding: 5px 30px 30px 30px;
        margin-top: 30px;
        box-shadow: 1px 2px 5px -2px rgba(0, 0, 0, 0.1);
    }

    .advanced-search-filter__header {
        display: flex;
        align-items: center;
        margin-top: -22px;
        margin-bottom: 20px;
    }

    .advanced-search-filter__header-left,
    .advanced-search-filter__header-right {
        width: 50%;
    }

    .advanced-search-filter__header-left-inner {
        background-color: #e6e9ed;
        padding: 10px 20px;
        border-radius: 3px;
        float: left;
    }

    .advanced-search-filter__header-left h4 {
        margin: 0;
        color: #2a3f54;
        font-size: 14px;
        font-weight: 600;
    }

    .advanced-search-filter__header-right-inner {
        background-color: #ffffff;
        border: 1px solid #e1e1e1;
        border-radius: 3px;
        display: flex;
        align-items: center;
        padding: 5px 15px;
        float: right;
    }

    .advanced-search-filter__header-right-inner h5 {
        margin: 0;
        color: rgba(42, 63, 84, 0.6);
        font-weight: 600;
        margin-right: 5px;
    }

    .advanced-search-filter__btn {
        margin: 0 2px;
    }

    .advanced-search-filter__form {
        display: flex;
        flex-wrap: wrap;
    }

    .advanced-search-filter__form-group {
        /* width: 32.22%; */
        margin: 0 10px 10px 0;
    }

    .advanced-search-filter__form-group-inner {
        border: 1px solid #e1e1e1;
        box-shadow: 1px 2px 5px -2px rgba(0, 0, 0, 0.1);
        border-radius: 3px;
        padding: 10px 10px 10px 15px;
        display: flex;
        align-items: center;
    }

    #reportrange_right {
        width: 100%;
    }

    .advanced-search-filter__form-group-inner::before {
        content: "";
        background: transparent no-repeat scroll 0 0;
        display: block;
        margin-right: 4px;
    }

    .advanced-search-filter__form-companies::before {
   
        width: 21px;
        height: 16px;
        margin-top: 3px;
    }

    .advanced-search-filter__form-search-by-client::before {
        background-image: url("../images/advanced-search-filter/client-icon.png");
        width: 18px;
        height: 16px;
        margin-top: 3px;
    }

    .advanced-search-filter__form-service-requested::before {
        background-image: url("../images/advanced-search-filter/userdefined-field-colored.png");
        width: 20px;
        height: 16px;
    }

    .advanced-search-filter__form-lead-type::before {
        background-image: url("../images/advanced-search-filter/lead-icon.png");
        width: 18px;
        height: 16px;
    }

    .advanced-search-filter__form-progress::before {
        background-image: url("../images/advanced-search-filter/userdefined-icon.png");
        width: 16px;
        height: 16px;
    }

    .advanced-search-filter__form-search-by-date::after {
        /*content: "";*/
        content: none;
        background: transparent url("../images/advanced-search-filter/calendar-icon.png") no-repeat scroll 0 0;
        width: 16px;
        height: 16px;
        display: block;
    }

    .advanced-search-filter__form-search-by-date .advanced-search-filter__form-input {
        width: 70%;
        margin-right: 10px;
    }

    .advanced-search-filter__form-select {
        width: 100%;
        color: #2a3f54;
        font-weight: 600;
        border: 0 none;
        font-size: 14px;
    }

    .advanced-search-filter__form-input {
        width: 100%;
        color: #2a3f54;
        font-weight: 600;
        border: 0 none;
        font-size: 14px;
    }

    .advanced-search-filter__form-input::placeholder {
        color: #2a3f54;
    }

    .advanced-search-filter__form-search-by-date {
        display: flex;
        align-items: center;
        /*padding: 5px 4px;*/
    }

    .advanced-search-filter__form-label {
        display: block;
        width: 21%;
        margin-right: 5px;
        text-align: center;
        background-color: #2a3f54;
        color: #ffffff;
        border-radius: 2px;
        padding: 6px 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .advanced-search-filter__actions {
        padding: 0 10px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    #advanced-search-filter-form {
        margin-bottom: 40px;
    }

    /**
   * Datatable
   */
    .advanced-search-filter__results #advanced-search-filter-table_info {
        color: #2a3f54;
        font-weight: 600;
    }

    .advanced-search-filter__results input[type="search"] {
        border: 1px solid #e1e1e1;
        box-shadow: 1px 2px 5px -2px rgba(0, 0, 0, 0.1);
        padding: 19px 20px;
        border-radius: 3px;
    }

    .advanced-search-filter__results .dataTables_filter label {
        color: #2a3f54;
        font-weight: 600 !important;
    }

    .advanced-search-filter__results .dataTables_length label {
        color: #2a3f54;
        font-weight: 600 !important;
    }

    .advanced-search-filter__results .dataTables_length select {
        border: 1px solid #e1e1e1;
        box-shadow: 1px 2px 5px -2px rgba(0, 0, 0, 0.1);
        padding: 9px 13px;
        border-radius: 3px;
        height: auto;
        width: 50% !important;
    }

    /**
   * Buttons
   */
    .custom-btn {
        border-radius: 5px;
        font-size: 15px;
        padding: 7px 18px;
        color: #314456;
        font-weight: 500;
        border: 2px solid transparent;
        background-image: linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, 0)), linear-gradient(0deg, #00b6b7, #a5d420);
        background-origin: border-box;
        background-clip: content-box, border-box;
        box-shadow: 2px 1000px 1px #fff inset;
        position: relative;
        cursor: pointer;
        margin: 0;
    }

    .custom-btn:hover {
        color: #ffffff;
        box-shadow: none;
    }

    .search-btn::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/search-icon.png") no-repeat scroll 0 0 / 100% auto;
        width: 16px;
        height: 16px;
        display: block;
        float: left;
        margin: 3px 6px 0 0;
    }

    .search-btn:hover::before {
        background-image: url("../images/advanced-search-filter/search-icon-hover.png");
    }

    .reset-btn {
        margin: 0 8px;
    }

    .reset-btn::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/reset-icon.png") no-repeat scroll 0 0 / 100% auto;
        width: 16px;
        height: 16px;
        display: block;
        float: left;
        margin: 3px 6px 0 0;
    }

    .reset-btn:hover::before {
        background-image: url("../images/advanced-search-filter/reset-icon-hover.png");
    }

    .close-btn::before,
    .cancel-btn::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/close-icon.png") no-repeat scroll 0 0 / 100% auto;
        width: 16px;
        height: 16px;
        display: block;
        float: left;
        margin: 3px 6px 0 0;
    }

    .close-btn:hover::before,
    .cancel-btn:hover::before {
        background-image: url("../images/advanced-search-filter/close-icon-hover.png");
    }

    .save-btn::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/save-icon.png") no-repeat scroll 0 0 / 100% auto;
        width: 16px;
        height: 16px;
        display: block;
        float: left;
        margin: 3px 6px 0 0;
    }

    .save-btn:hover::before {
        background-image: url("../images/advanced-search-filter/save-icon-hover.png");
    }

    .save-btn,
    .cancel-btn {
        padding: 10px 35px;
    }

    .save-btn {
        margin-right: 10px;
    }

    .filter-settings__actions {
        display: flex;
        justify-content: flex-end;
        padding: 0 34px;
    }

    .saved-filters-table h3 {
        color: #182f45;
        margin: 0;
        padding: 0 20px;
        font-size: 16px;
    }

    .saved-filters-btn {
        width: 100%;
        background: rgb(238, 238, 238);
        background: linear-gradient(0deg, rgba(238, 238, 238, 1) 0%, rgba(255, 255, 255, 1) 58%);
        border: 1px solid #dddddd;
        padding: 6px 15px;
        display: inline-block;
        color: #182f45;
        border-radius: 4px;
        text-align: center;
        max-width: 125px;
        font-weight: 600;
    }

    .saved-filters-btn img {
        margin-top: -3px;
        margin-right: 3px;
    }

    .saved-filters-btn__rename {
        margin: 0 20px;
    }

    .saved-filters-table td {
        vertical-align: middle !important;
    }

    .load-filter-btn {
        display: block;
        margin: 0 auto;
    }

    /**
   * Tabs
   */
    .advanced-search-filter__tabs .bar_tabs {
        background: transparent;
        padding-left: 0;
        height: auto;
        margin-bottom: 0;
        border-bottom: 0;
    }

    .advanced-search-filter__tabs .bar_tabs>li {
        margin-left: 0;
        margin-top: 0;
        border: 0 none;
    }

    .advanced-search-filter__tabs .bar_tabs>li a {
        border-radius: 7px 7px 0 0;
        border: 1px solid #E6E9ED !important;
        border-bottom: 0 none;
        font-weight: 500;
        font-size: 15px;
        border: 0 none;
    }

    .advanced-search-filter__tabs .bar_tabs>li a:hover {
        background-color: #2a3f54;
        color: #ffffff;
        border-bottom: 0;
    }

    .advanced-search-filter__tabs .bar_tabs>li.active {
        border-right: 0;
        margin-top: 0;
    }

    .advanced-search-filter__tabs .bar_tabs>li.active a {
        border-bottom: none;
        background-color: #2a3f54;
        color: #ffffff;
    }

    .advanced-search-filter__tabs .tab-content {
        padding: 20px;
        border-radius: 0 10px 10px 10px;
        border: 1px solid #E6E9ED;
    }

    .advanced-search-filter__tabs .tab-content-inner {
        border: 1px solid #E6E9ED;
        padding: 15px;
        display: flex;
        align-items: center;
        height: 298px;
        overflow-y: scroll;
        flex-wrap: wrap;
    }

    .advanced-search-filter__tabs .tab-content-col {
        width: 31%;
        margin: 0 10px;
    }

    .filter-settings__form-group {
        border: 1px solid #e6e9ed;
        background-color: #fbfbfb;
        border-radius: 3px;
        padding: 3px;
        display: flex;
        align-items: center;
        margin-bottom: 7px;
    }

    .filter-settings__label {
        margin: 0;
        color: #767778;
        font-size: 13px;
        font-weight: 600;
    }

    .filter-settings__img {
        margin: 0 10px;
    }

    .filter-settings__form-group .icheckbox_flat-green {
        border: 1px solid #E6E9ED;
        border-radius: 3px;
        width: 23px;
        height: 23px;
        background: url("../images/advanced-search-filter/filter-check.png");
    }

    .filter-settings__form-group .icheckbox_flat-green.checked {
        background-position: 199px 0 !important;
    }

    .filter-settings__label--regular {
        font-weight: 300;
    }

    .filter-settings__applied {
        border: 1px solid #e1e1e1;
        border-radius: 3px;
        padding: 0 15px;
        box-shadow: 1px 2px 5px -2px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
    }

    .filter-settings__applied h4 {
        color: #2a3f54;
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        width: 11%;
    }

    .filter-settings__group {
        display: flex;
        align-items: center;
        padding: 8px 15px 8px 50px;
    }

    .filter-settings__companies {
        position: relative;
        /* min-width: 20%; */
        border-right: 1px solid #e1e1e1;
    }

    .filter-settings__companies::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/company-icon.png") no-repeat scroll 0 0;
        width: 19px;
        height: 16px;
        position: absolute;
        left: 20px;
        top: 13px;
    }

    .filter-settings__clients {
        position: relative;
        /* min-width: 20%; */
        border-right: 1px solid #e1e1e1;
    }

    .filter-settings__clients::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/client-icon.png") no-repeat scroll 0 0;
        width: 19px;
        height: 16px;
        position: absolute;
        left: 20px;
        top: 13px;
    }

    .filter-settings__date {
        position: relative;
        /* width: 19%; */
        border-right: 1px solid #e1e1e1;
    }

    .filter-settings__date::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/calendar-icon-colored.png") no-repeat scroll 0 0;
        width: 16px;
        height: 16px;
        position: absolute;
        left: 20px;
        top: 13px;
    }

    .filter-settings__type {
        position: relative;
        /* min-width: 20%; */
        padding-left: 30px;
    }

    .filter-settings__type::before {
        content: "";
        background: transparent url("../images/advanced-search-filter/userdefined-field-colored.png") no-repeat scroll 0 0;
        width: 16px;
        height: 16px;
        position: absolute;
        left: 20px;
        top: 13px;
    }

    .filter-settings__action {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        position: absolute;
        /* top: 12px; */
        right: 30px;
    }

    .filter-value {
        display: block;
        line-height: normal;
        font-size: 11px;
        background-color: #f7f7f7;
        padding: 5px;
        border: 1px dashed #e1e1e1;
        border-radius: 3px;
        margin-right: 6px;
        white-space: nowrap;
    }

    .filter-value-remove {
        color: #2a3f54;
        font-weight: 700;
    }


    /**
   * Popups
   */
    .advanced-search-filter__popup .modal-dialog {
        max-width: 1080px;
        width: 100%;
        border: 11px solid rgba(0, 0, 0, .4);
        border-radius: 10px;
    }

    .advanced-search-filter__popup .modal-header {
        background: #182f45 url("../images/advanced-search-filter/popup-header.png") no-repeat scroll 0 0 / cover;
        padding: 15px 12px 15px 15px;
        border-bottom: 0 none;
    }

    .advanced-search-filter__popup .modal-body {
        padding: 35px 35px 10px;
    }

    .advanced-search-filter__popup .modal-content {
        border-radius: 4px;
        border: 0 none;
    }

    .advanced-search-filter__popup .modal-footer {
        border-top: 0 none;
        text-align: center;
        padding-bottom: 40px;
    }

    .advanced-search-filter__popup .close {
        opacity: 1;
    }

    .modal-header-info {
        display: flex;
        float: left;
        align-items: center;
    }

    .modal-header-icon {
        margin-right: 15px;
    }

    .modal-header-details .modal-title {
        color: #ffffff;
        font-size: 24px;
    }

    .modal-header-details span {
        font-size: 16px;
    }

    .filter-settings__checkbox .icheckbox_flat {
        margin-right: 10px;
        width: 32px;
        height: 32px;
        background: url("../images/advanced-search-filter/flat-custom.png") no-repeat;
    }

    .filter-settings__checkbox .icheckbox_flat {
        margin: 0 auto;
        display: table;
    }

    .filter-settings__checkbox .icheckbox_flat {
        background-position: -1px 0;
    }

    .filter-settings__checkbox .icheckbox_flat.checked {
        background-position: -35px 0;
    }


    .search-dropdown-group {
        margin-bottom: 0 !important;
    }

    .advanced-search-filter__form-companies {
        display:block;
    }

    #companies-search-active-labels .label,
    #clients-search-active-labels .label {
        background-color: #f8f8fa !important;
        color: #000;
        font-weight: 400;
        border: #ddd 1px solid;
        border-radius: 3px;
        padding: 5px;
        margin-left: 5px;
        display: inline-block;
        margin-bottom: 5px;
    }

    #companies-search-active-labels .label a,
    #clients-search-active-labels .label a {
        margin-left: 5px;
        font-weight: 900;
    }

    /**
   * Mobile
   */
    @media screen and (max-width: 1343px) {
        .advanced-search-filter__form-group {
            margin: 0 5px 10px;
        }

        .advanced-search-filter__form-label {
            font-size: 9px;
        }

        .advanced-search-filter__form-search-by-date {
            padding: 7px 4px;
        }
    }

    @media screen and (max-width: 1200px) {
        .advanced-search-filter__form-group {
            width: 48%;
        }
    }

    @media screen and (max-width: 1176px) {
        .advanced-search-filter__form-group {
            width: 48%;
        }
    }

    @media screen and (max-width: 1024px) {
        .advanced-search-filter-btn {
            width: 40%;
            margin-top: 15px;
            font-size: 15px;
            display: block;
        }

        .advanced-search-filter__btn-group {
            float: none !important;
        }
    }

    .btn btn-primary{
        margin-left: 46px;
    }

    .dropdown-menu {
        left: 120px;
    }

    .btn-primary{
        margin-left: 45px;
    }
</style>



@if($u->isAgent() || $u->isAdmin())
<div class="panel-body">
    @include('ticketit::tickets.partials.stats')
</div>
@endif

<div class="panel panel-default">

    <div class="panel-heading">
        @if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin)
        <div class="pull-left">
            <h2>{{ trans('ticketit::lang.index-my-tickets') }}</h2>
        </div>
        <div class="col-md-6 pull-right" style="margin-top:10px; display:flex;">
                        <div id="companies-search-active-labels" style="margin-left: 100px;">
                            <div style="display: flex;" data-search-id="#companies-search" data-dropdown-id="#companiesDropdown" data-list-id="#companies-list">
                                <input type="text" class="form-control" id="companies-search" name="" placeholder="Toggle Columns" autocomplete="off" style="height: 40px;" readonly>
                                <div class="input-group-btn position_unset" id="companiesDropdown">
                                    <button type="button" class="btn btn-default dropdown-toggle mega-dropdown" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu row " role="menu">
                                        <ul id="companies-list" class="mega-dropdown-menu_alphabet">
                                            <li class="main_alphabet">
                                                <ul class="sub_menu">
                                                <li class="main_sub_alphabet search_term search_term_companies" data-search-term="tesla" data-name="Tesla">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="*" value="*" name="" class=" ticket_dropdown_option company-checkboxes company-checkboxes_131">
                                                                Show all
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="tesla" data-name="Tesla">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="0" value="0"  class="ticket_dropdown_option company-checkboxes company-checkboxes_131">
                                                                #
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="1" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Subject
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="2" value="1" class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                               Status
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="3" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Last Reply
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="4" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Last Updated
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="5" value="1" class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Agent
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="6" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Priority
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="7" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Owner
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="8" value="1" class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Category
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="main_sub_alphabet search_term search_term_companies  " data-search-term="test company" data-name="Test Company">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" data-column="9" value="1"  class="ticket_dropdown_option company-checkboxes company-checkboxes_132">
                                                                Actions
                                                            </label>
                                                        </div>
                                   
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
          

            <!--<div class="col-md-4" style="border:1px solid green;">
        <small class="">Toggle Columns:</small>
        
        <li class="dropdown" style="text-decoration: none;">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle">
            Dropdown Form<b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
            <li><label class="checkbox"><input type="checkbox">Two</label></li>
            <li><label class="checkbox"><input type="checkbox">Two</label></li>
            </ul>
        </li>
     
        
        </div> -->


            <!-- <select id="tickets_show" class="ticket_dropdown_filter " >  
            <option disabled selected value>Columns  </option>
            <option class="ticket_dropdown_option" data-column="all" selected value="" >Show All  </option>
            <option class="ticket_dropdown_option" data-column="0">#</option>
            <option class="ticket_dropdown_option" data-column="1">Subject</option>
            <option class="ticket_dropdown_option" data-column="2">Status</option>
            <option class="ticket_dropdown_option" data-column="3">Last Reply</option>
            <option class="ticket_dropdown_option" data-column="4">Last Updated</option>
            <option class="ticket_dropdown_option" data-column="5">Agent</option>
            <option class="ticket_dropdown_option" data-column="6">Priority</option>
            <option class="ticket_dropdown_option" data-column="7">Owner</option>
            <option class="ticket_dropdown_option" data-column="8">Category</option>
            <option class="ticket_dropdown_option" data-column="9">Actions</option>
        </select> -->
        {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary']) !!}
        <button class="btn btn-default" onclick="openNav()" id="filter_open"><i class="fa fa-filter"></i></button>
        </div>
        @else
        <h2>{{ trans('ticketit::lang.index-my-tickets') }}
            {!! link_to_route($setting->grab('main_route').'.create', trans('ticketit::lang.btn-create-new-ticket'), null, ['class' => 'btn btn-primary pull-right']) !!}</h2>
        @endif

    </div>



    @if(Sentinel::getUser()->ticketit_agent || Sentinel::getUser()->ticketit_admin )
    <div class="panel-body">

        @include('ticketit::shared.filters')
    </div>
    @endif

    <div class="panel-body">
        <div id="message"></div>

        @include('ticketit::tickets.partials.datatable')
    </div>

</div>