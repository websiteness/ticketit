<?php

Route::group(['middleware' => \Kordy\Ticketit\Helpers\LaravelVersion::authMiddleware()], function () use ($main_route, $main_route_path, $admin_route, $admin_route_path) {

    //Route::group(['middleware' => '', function () use ($main_route) {
    //Ticket public route
    Route::get("$main_route_path/complete", 'Kordy\Ticketit\Controllers\TicketsController@indexComplete')
            ->name("$main_route-complete");
    Route::get("$main_route_path/data/{id?}", 'Kordy\Ticketit\Controllers\TicketsController@data')
            ->name("$main_route.data");

    $field_name = last(explode('/', $main_route_path));
    Route::get($main_route_path."/crm-ticket", 'Kordy\Ticketit\Controllers\TicketsController@create')->name("$main_route.crmticket.create");

    Route::resource($main_route_path, 'Kordy\Ticketit\Controllers\TicketsController', [
            'names' => [
                'index'   => $main_route.'.index',
                'store'   => $main_route.'.store',
                'create'  => $main_route.'.create',
                'update'  => $main_route.'.update',
                'show'    => $main_route.'.show',
                'destroy' => $main_route.'.destroy',
                'edit'    => $main_route.'.edit',
            ],
            'parameters' => [
                $field_name => 'ticket',
            ],
        ]);
    

    //Ticket Comments public route
    $field_name = last(explode('/', "$main_route_path-comment"));
    Route::resource("$main_route_path-comment", 'Kordy\Ticketit\Controllers\CommentsController', [
            'names' => [
                'index'   => "$main_route-comment.index",
                'store'   => "$main_route-comment.store",
                'create'  => "$main_route-comment.create",
                'update'  => "$main_route-comment.update",
                'show'    => "$main_route-comment.show",
                'destroy' => "$main_route-comment.destroy",
                'edit'    => "$main_route-comment.edit",
            ],
            'parameters' => [
                $field_name => 'ticket_comment',
            ],
        ]);

    //Ticket complete route for permitted user.
    Route::get("$main_route_path/{id}/complete", 'Kordy\Ticketit\Controllers\TicketsController@complete')
            ->name("$main_route.complete");

    //Ticket reopen route for permitted user.
    Route::get("$main_route_path/{id}/reopen", 'Kordy\Ticketit\Controllers\TicketsController@reopen')
            ->name("$main_route.reopen");
    //});

    Route::group(['middleware' => 'Kordy\Ticketit\Middleware\IsAgentMiddleware'], function () use ($main_route, $main_route_path) {

        //API return list of agents in particular category
        Route::get("$main_route_path/agents/list/{category_id?}/{ticket_id?}", [
            'as'   => $main_route.'agentselectlist',
            'uses' => 'Kordy\Ticketit\Controllers\TicketsController@agentSelectList',
        ]);
    });

    Route::group(['middleware' => 'Kordy\Ticketit\Middleware\IsAdminMiddleware'], function () use ($admin_route, $admin_route_path) {
        //Ticket admin index route (ex. http://url/tickets-admin/)
        Route::get("$admin_route_path/indicator/{indicator_period?}", [
                'as'   => $admin_route.'.dashboard.indicator',
                'uses' => 'Kordy\Ticketit\Controllers\DashboardController@index',
        ]);
        Route::get($admin_route_path, 'Kordy\Ticketit\Controllers\DashboardController@index');

        //Ticket statuses admin routes (ex. http://url/tickets-admin/status)
        Route::resource("$admin_route_path/status", 'Kordy\Ticketit\Controllers\StatusesController', [
            'names' => [
                'index'   => "$admin_route.status.index",
                'store'   => "$admin_route.status.store",
                'create'  => "$admin_route.status.create",
                'update'  => "$admin_route.status.update",
                'show'    => "$admin_route.status.show",
                'destroy' => "$admin_route.status.destroy",
                'edit'    => "$admin_route.status.edit",
            ],
        ]);

        //Ticket priorities admin routes (ex. http://url/tickets-admin/priority)
        Route::resource("$admin_route_path/priority", 'Kordy\Ticketit\Controllers\PrioritiesController', [
            'names' => [
                'index'   => "$admin_route.priority.index",
                'store'   => "$admin_route.priority.store",
                'create'  => "$admin_route.priority.create",
                'update'  => "$admin_route.priority.update",
                'show'    => "$admin_route.priority.show",
                'destroy' => "$admin_route.priority.destroy",
                'edit'    => "$admin_route.priority.edit",
            ],
        ]);

        //Agents management routes (ex. http://url/tickets-admin/agent)
        Route::resource("$admin_route_path/agent", 'Kordy\Ticketit\Controllers\AgentsController', [
            'names' => [
                'index'            => "$admin_route.agent.index",
                'store'            => "$admin_route.agent.store",
                'create'           => "$admin_route.agent.create",
                'update'           => "$admin_route.agent.update",
                'show'             => "$admin_route.agent.show",
                'destroy'          => "$admin_route.agent.destroy",
                'edit'             => "$admin_route.agent.edit",
            ],
        ]);

        //Agents management routes (ex. http://url/tickets-admin/agent)
        Route::resource("$admin_route_path/category", 'Kordy\Ticketit\Controllers\CategoriesController', [
            'names' => [
                'index'   => "$admin_route.category.index",
                'store'   => "$admin_route.category.store",
                'create'  => "$admin_route.category.create",
                'update'  => "$admin_route.category.update",
                'show'    => "$admin_route.category.show",
                'destroy' => "$admin_route.category.destroy",
                'edit'    => "$admin_route.category.edit",
            ],
        ]);

        //Settings configuration routes (ex. http://url/tickets-admin/configuration)
        Route::resource("$admin_route_path/configuration", 'Kordy\Ticketit\Controllers\ConfigurationsController', [
            'names' => [
                'index'   => "$admin_route.configuration.index",
                'store'   => "$admin_route.configuration.store",
                'create'  => "$admin_route.configuration.create",
                'update'  => "$admin_route.configuration.update",
                'show'    => "$admin_route.configuration.show",
                'destroy' => "$admin_route.configuration.destroy",
                'edit'    => "$admin_route.configuration.edit",
            ],
        ]);

        //Administrators configuration routes (ex. http://url/tickets-admin/administrators)
        Route::resource("$admin_route_path/administrator", 'Kordy\Ticketit\Controllers\AdministratorsController', [
            'names' => [
                'index'   => "$admin_route.administrator.index",
                'store'   => "$admin_route.administrator.store",
                'create'  => "$admin_route.administrator.create",
                'update'  => "$admin_route.administrator.update",
                'show'    => "$admin_route.administrator.show",
                'destroy' => "$admin_route.administrator.destroy",
                'edit'    => "$admin_route.administrator.edit",
            ],
        ]);

        //Tickets demo data route (ex. http://url/tickets-admin/demo-seeds/)
        // Route::get("$admin_route/demo-seeds", 'Kordy\Ticketit\Controllers\InstallController@demoDataSeeder');
    });

    # Admin Routes
    Route::group(['middleware' => ['Kordy\Ticketit\Middleware\IsAdminMiddleware']], function () use ($admin_route) {

        # Settings
        Route::prefix($admin_route.'/settings')->name($admin_route.'.settings.')->group(function() {
            Route::get('/', 'Kordy\Ticketit\Controllers\SettingsController@index')->name('index');

            Route::prefix('overdue')->name('overdue.')->group(function() {
                Route::post('save', 'Kordy\Ticketit\Controllers\SettingsController@saveOverdueHours')->name('save');
            });
        });
    });

    # Agent and Admin Routes
    Route::prefix($admin_route)->name($admin_route.'.')->middleware(['Kordy\Ticketit\Middleware\IsAdminMiddleware'])->group(function () use ($admin_route) {

        # Agent
        Route::prefix('agent/{id}')->name('agent.')->group(function () {

            # Notfications
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('settings', 'Kordy\Ticketit\Controllers\AgentsController@viewNotifications')->name('settings');
                Route::post('settings', 'Kordy\Ticketit\Controllers\AgentsController@saveNotificationSettings')->name('settings.store');
            });
        });

        # Categories
        Route::prefix('categories')->name('categories.')->group(function() {

            # Owners
            Route::prefix('owners')->name('owners.')->group(function() {
                Route::get('/', '\Kordy\Ticketit\Controllers\CategoriesController@viewCategoryOwners')->name('index');
                Route::post('/store', '\Kordy\Ticketit\Controllers\CategoriesController@storeCategoryOwners')->name('store');
            });
        });

        # Asana
        Route::prefix('asana')->name('asana.')->group(function() {
            Route::get('/', '\Kordy\Ticketit\Controllers\Integrations\AsanaController@index')->name('index');
            Route::post('token/store', '\Kordy\Ticketit\Controllers\Integrations\AsanaController@store_token')->name('token.store');

            Route::get('projects','\Kordy\Ticketit\Controllers\Integrations\AsanaController@get_projects')->name('projects');
            Route::post('projects/store','\Kordy\Ticketit\Controllers\Integrations\AsanaController@store_project')->name('projects.store');

            Route::prefix('categories')->name('categories.')->group(function() {
                Route::get('/','\Kordy\Ticketit\Controllers\Integrations\AsanaController@categories_index')->name('index');
                Route::post('map','\Kordy\Ticketit\Controllers\Integrations\AsanaController@map_sections')->name('map');
            });

            Route::prefix('users')->name('users.')->group(function() {
                Route::get('/','\Kordy\Ticketit\Controllers\Integrations\AsanaController@users_index')->name('index');
                Route::post('map','\Kordy\Ticketit\Controllers\Integrations\AsanaController@map_users')->name('map');
            }); 

            Route::prefix('settings')->name('settings.')->group(function() {
                Route::post('store','\Kordy\Ticketit\Controllers\Integrations\AsanaController@store_settings')->name('store');
            });

            Route::prefix('tags')->name('tags.')->group(function() {
                Route::get('list','\Kordy\Ticketit\Controllers\Integrations\AsanaController@get_tag_list')->name('list');
            });
            
            Route::prefix('workspaces')->name('workspaces.')->group(function() {
                Route::get('list','\Kordy\Ticketit\Controllers\Integrations\AsanaController@get_workspace_list')->name('list');
                Route::post('store','\Kordy\Ticketit\Controllers\Integrations\AsanaController@store_workspace')->name('store');
            });
            
            Route::prefix('status')->name('status.')->group(function() {
                Route::get('list','\Kordy\Ticketit\Controllers\Integrations\AsanaController@status_index')->name('index');
                Route::post('map','\Kordy\Ticketit\Controllers\Integrations\AsanaController@map_statuses')->name('map');
            });
        });
    });

    # Stats
    Route::prefix($admin_route.'/stats')->name($admin_route.'.stats.')->group(function() {
        Route::get('/', 'Kordy\Ticketit\Controllers\StatsController@index')->name('index');
        Route::get('status-count', 'Kordy\Ticketit\Controllers\StatsController@getStatusCount')->name('status_count');
        Route::get('categories-count', 'Kordy\Ticketit\Controllers\StatsController@getCategoriesCount')->name('categories_count');
        // Route::get('status', 'Kordy\Ticketit\Controllers\StatsController@getStatus')->name('status');
    });


});
