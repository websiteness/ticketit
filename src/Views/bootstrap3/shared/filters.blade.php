
@push('header_styles')
<style>
    /* The side navigation menu */
    .sidenav {
        height: 100%; /* 100% Full-height */
        width: 0; /* 0 width - change this with JavaScript */
        position: fixed; /* Stay in place */
        z-index: 9999; /* Stay on top */
        top: 0; /* Stay at the top */
        right: -40px;
        background-color: #2b3f54; /* Black*/
        overflow-x: hidden; /* Disable horizontal scroll */
        padding-top: 60px; /* Place content 60px from the top */
        transition: 0.5s; /* 0.5 second transition effect to slide in the sidenav */
    }
    .sidenav > div {
        width: 335px;
        padding: 20px;
    }
    /* The navigation menu links */
    .sidenav a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
    }

    /* When you mouse over the navigation links, change their color */
    .sidenav a:hover {
        color: #f1f1f1;
    }

    /* Position and style the close button (top right corner) */
    .sidenav .closebtn {
        position: absolute;
        top: 0;
        left: 0;
        font-size: 36px;
        margin-right: 50px;
    }

    /* Style page content - use this if you want to push the page content to the right when you open the side navigation */
    #main {
        transition: margin-left .5s;
        padding: 20px;
    }

    /* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
    @media screen and (max-height: 450px) {
        .sidenav {padding-top: 15px;}
        .sidenav a {font-size: 18px;}
    }

    .panel-heading:after {
        content: '';
        display: block;
        clear: both;
    }

    .select2-container--open {
        z-index: 9999999 !important;
    }
</style>
@endpush

<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

    <div>
        <div class="form-group">
            <label>Owner</label>
            <select class="form-control select2" id="filter_owner">
                <option value="">Select Owner</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select class="form-control" id="filter_status">
                <option value="">Select Status</option>
                @foreach($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
                <option value="no_response">No Response</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>
        <div class="form-group">
            <label>Sub Categoy</label>
            <select class="form-control" id="filter_sub_category">
                <option value="">Select Cateory</option>
                @foreach($sub_categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Last Reply</label>
            <select class="form-control" id="filter_last_reply">
                <option value="">Select Last Reply</option>
                <option value="support">Support</option>
                <option value="user">User</option>
            </select>
        </div>
        <div class="form-group">
            <label>Message</label>
            <input type="text" class="form-control" id="filter_message" placeholder="Search messages"/>
        </div>
        <div class="form-group">
            <label for="filter_hide_closed_tickets">
            <input type="checkbox" id="filter_hide_closed_tickets" onclick="filterTickets()" style="margin-top:30px;" checked/> Hide closed tickets
            </label>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-success" onclick="filterTickets()" id="btn_search_filter" style="margin-top:23px;margin-right:0;">Search</button>
            <button type="button" class="btn btn-danger" onclick="clearFilters()" style="margin-top:23px;">Clear</button>
        </div>
    </div>

</div>
<!-- Use any element to open the sidenav -->
<!-- <span onclick="openNav()">open</span> -->

@push('footer_scripts')
<script>
    /* Set the width of the side navigation to 250px */
    function openNav() {
		$('.select2').select2();
        document.getElementById("mySidenav").style.width = "370px";
    }

    /* Set the width of the side navigation to 0 */
    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }

    $(document).ready(function() {
        $(window).click(function() {
            document.getElementById("mySidenav").style.width = "0";
        });

        $('#mySidenav, #filter_open').click(function(event){
            event.stopPropagation();
        });
    });
</script>
@endpush