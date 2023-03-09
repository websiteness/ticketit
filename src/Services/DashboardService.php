<?php

namespace Kordy\Ticketit\Services;
use Carbon\Carbon;

class DashboardService
{

    public function saveFiltersInSession($request)
    {

        session([
            'ticket_dashboard_filter_ignore_user_ids' => '',
            'ticket_dashboard_filter_user_id' => '',
            'ticket_dashboard_filter_ticket_date_range_type' => '',
            'ticket_dashboard_filter_ticket_date_range_start' => '',
            'ticket_dashboard_filter_ticket_date_range_end' => '',
            'ticket_dashboard_filter_ignore_test_accounts' => ''
        ]);

        // save the filter so we can save it when user leaves the page
        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids']) && is_array($request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids']) && count($request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids'])) {

            $tag_ids = $request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids'];
            session(['ticket_dashboard_filter_ignore_user_ids' => $tag_ids]);

        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_user_id']) && $request->custom_filters['hdn_ticket_dashboard_filter_user_id']) {

            $user_id = $request->custom_filters['hdn_ticket_dashboard_filter_user_id'];
            session(['ticket_dashboard_filter_user_id' => $user_id]);

        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type']) {

            $ticket_date_range_type = $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type'];
            session(['ticket_dashboard_filter_ticket_date_range_type' => $ticket_date_range_type]);

            if($ticket_date_range_type=='Custom'){
                if (
                    isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start'] &&
                    isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']
                ) {
                    session(['ticket_dashboard_filter_ticket_date_range_start' => $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start']]);
                    session(['ticket_dashboard_filter_ticket_date_range_end' => $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']]);
                }
            }

        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ignore_test_accounts']) && $request->custom_filters['hdn_ticket_dashboard_filter_ignore_test_accounts']) {

            session(['ticket_dashboard_filter_ignore_test_accounts' => $request->custom_filters['hdn_ticket_dashboard_filter_ignore_test_accounts']]);

        }
    }


    public function getFilterArrayFromRequest($request)
    {

        $filter_arr = [];

        // check if filters are applied
        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids']) && is_array($request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids']) && $request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids']) {
            $filter_arr['ignore_user_id_arr'] = $request->custom_filters['hdn_ticket_dashboard_filter_ignore_user_ids'];
        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_user_id']) && $request->custom_filters['hdn_ticket_dashboard_filter_user_id']) {
            $filter_arr['user_id'] = $request->custom_filters['hdn_ticket_dashboard_filter_user_id'];
        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type']) {

            $start = new Carbon(now());
            $start->startOfDay()->subSeconds(1);
            $start = $start->format('Y-m-d H:i:s');

            $end = new Carbon(now());
            $end->endOfDay();
            $end = $end->format('Y-m-d H:i:s');

            $ticket_date_range_type = $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_type'];

            if ($ticket_date_range_type == 'Custom') {
                if (
                    isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start'] &&
                    isset($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']) && $request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']
                ) {

                    $start = new Carbon($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_start']);
                    $start->startOfDay()->subSeconds(1);
                    $start = $start->format('Y-m-d H:i:s');

                    $end = new Carbon($request->custom_filters['hdn_ticket_dashboard_filter_ticket_date_range_end']);
                    $end->endOfDay();
                    $end = $end->format('Y-m-d H:i:s');
                }
            } else if ($ticket_date_range_type == 'Today') {
                $start = new Carbon(now());
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');

                $end = new Carbon(now());
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Yesterday') {
                $start = new Carbon(now());
                $start = $start->subDays(1);
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');
                $end = new Carbon(now());
                $end = $end->subDays(1);
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 7 Days') {
                $carbon_obj = Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(7);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 30 Days') {
                $carbon_obj = Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(30);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'This Month') {
                $start = Carbon::now()->startOfMonth()->subSeconds(1);
                $end = Carbon::now()->endOfMonth();
            }
            else if ($ticket_date_range_type == 'Last Month') {
                $start = new Carbon('first day of last month');
                $start->startOfDay()->subSeconds(1);
                $end = new Carbon('last day of last month');
                $end->endOfDay();
            }

            $filter_arr['date_range']['start_date'] = (string) $start;
            $filter_arr['date_range']['end_date'] = (string) $end;

        }

        if (isset($request->custom_filters['hdn_ticket_dashboard_filter_ignore_test_accounts']) && $request->custom_filters['hdn_ticket_dashboard_filter_ignore_test_accounts']) {
            $filter_arr['ignore_test_accounts'] = session('hdn_ticket_dashboard_filter_ignore_test_accounts');
        }

        return $filter_arr;
    }

    public function getFiltersFromSession()
    {

        $filter_arr = [];

        // check if filters are applied

        if (session('ticket_dashboard_filter_ignore_user_ids') !== null && is_array(session('ticket_dashboard_filter_ignore_user_ids')) && count(session('ticket_dashboard_filter_ignore_user_ids'))) {
            $filter_arr['ignore_user_id_arr'] = session('ticket_dashboard_filter_ignore_user_ids');
        }

        if (session('ticket_dashboard_filter_user_id') !== null  && session('ticket_dashboard_filter_user_id')) {
            $filter_arr['user_id'] = session('ticket_dashboard_filter_user_id');
        }

        if (session('ticket_dashboard_filter_ticket_date_range_type') !== null   && session('ticket_dashboard_filter_ticket_date_range_type')) {

            $start = new Carbon(now());
            $start->startOfDay()->subSeconds(1);
            $start = $start->format('Y-m-d H:i:s');

            $end = new Carbon(now());
            $end->endOfDay();
            $end = $end->format('Y-m-d H:i:s');

            $ticket_date_range_type = session('ticket_dashboard_filter_ticket_date_range_type');

            if ($ticket_date_range_type == 'Custom') {
                if (
                    session('ticket_dashboard_filter_ticket_date_range_start') !== null && session('ticket_dashboard_filter_ticket_date_range_start') &&
                    session('ticket_dashboard_filter_ticket_date_range_end') !== null && session('ticket_dashboard_filter_ticket_date_range_end')
                ) {

                    $start = new Carbon(session('ticket_dashboard_filter_ticket_date_range_start'));
                    $start->startOfDay()->subSeconds(1);
                    $start = $start->format('Y-m-d H:i:s');

                    $end = new Carbon(session('ticket_dashboard_filter_ticket_date_range_end'));
                    $end->endOfDay();
                    $end = $end->format('Y-m-d H:i:s');
                }
            } else if ($ticket_date_range_type == 'Today') {
                $start = new Carbon(now());
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');

                $end = new Carbon(now());
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Yesterday') {
                $start = new Carbon(now());
                $start = $start->subDays(1);
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');
                $end = new Carbon(now());
                $end = $end->subDays(1);
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 7 Days') {
                $carbon_obj = Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(7);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 30 Days') {
                $carbon_obj = Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(30);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'This Month') {
                $start = Carbon::now()->startOfMonth()->subSeconds(1);
                $end = Carbon::now()->endOfMonth();
            }
            else if ($ticket_date_range_type == 'Last Month') {
                $start = new Carbon('first day of last month');
                $start->startOfDay()->subSeconds(1);
                $end = new Carbon('last day of last month');
                $end->endOfDay();
            }

            $filter_arr['date_range']['start_date'] = (string) $start;
            $filter_arr['date_range']['end_date'] = (string) $end;

        }

        if (session('ticket_dashboard_filter_ignore_test_accounts') !== null  && session('ticket_dashboard_filter_ignore_test_accounts')) {
            $filter_arr['ignore_test_accounts'] = session('ticket_dashboard_filter_ignore_test_accounts');
        }

        return $filter_arr;
    }

}
