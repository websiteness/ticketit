<div class="ticket-system__tabs" role="tabpanel" id="app">
    <div class="ticket-system__tabs-action">
        <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
            <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.infinity.index') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.infinity.index') }}">Infinity Integration</a>
            </li>

            <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.infinity.fields.mapping') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.infinity.fields.mapping') }}">Field Mapping</a>
            </li>

            <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.infinity.users.mapping') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.infinity.users.mapping') }}">User Mapping</a>
            </li>
<!-- 
            <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.asana.categories.index') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.asana.categories.index') }}">Category Mapping</a>
            </li>
            <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.asana.users.index') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.asana.users.index') }}">Users Mapping</a>
            </li> -->
            <!-- <li role="presentation" @if(Route::currentRouteName() == $setting->grab('admin_route').'.asana.status.index') class="active" @endif>
                <a href="{{ route($setting->grab('admin_route').'.asana.status.index') }}">Status Mapping</a>
            </li> -->
        </ul>
    </div>
</div>