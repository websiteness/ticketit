
@push('header_styles')
<style>
.count:hover{
  cursor: grab;
}
</style>
@endpush
<div class="row">
    <div class="col-md-2">
        <select class="form-control" id="show_stats">
            <option value="status">Status</option>
            <option value="module">Module</option>
        </select>
    </div>
</div>
<div class="row" >
    <div class="tile_count" id="stats_widget">
        <!-- <div class="col-md-2 col-sm-4  tile_stats_count">
            <span class="count_top"><i class="fa fa-user"></i> Total Users</span>
            <div class="count">2500</div>
            <span class="count_bottom"><i class="green">4% </i> From last Week</span>
        </div> -->
    </div>
</div>
                                                    
@push('footer_scripts')
<script>
    renderStats();

    document.getElementById('show_stats').addEventListener('change', renderStats);

    function renderStats() {
        let active_stat = document.getElementById('show_stats').value;

        if(active_stat == 'status') {
            getStatusStats();
            return;
        }

        getCategoriesStats();
    }
                                                      
    function getStatusStats() {
        let url = `{{ route($setting->grab('admin_route') . '.stats.status_count') }}`;
        let content = '';
        console.log(url);

        fetch(url)
        .then((res) => {
            res.json().then((data) => {
                // console.log(data);
                data.forEach(element => {
                    console.log(element.name)
                    content = content + `<div class="col-md-2 col-sm-4 tile_stats_count" onclick="filterTable('`+element.name+`')">
                                    <span class="count_top"> ${element.name}</span>
                                    <div class="count green">${element.count}</div>
                                </div>`;
                    document.getElementById('stats_widget').innerHTML = content;
                });
            });
        })
        .catch((err) => {
            console.log('Error', err);
        });
    }
                                                                                
    function getCategoriesStats() {
        let url = `{{ route($setting->grab('admin_route') . '.stats.categories_count') }}`;
        let content = '';
        fetch(url)
        .then((res) => {
            res.json().then((data) => {
                console.log(data);
                data.forEach(element => {
                    console.log(element)
                    content = content + `<div class="col-md-2 col-sm-4  tile_stats_count">
                                    <span class="count_top"> ${element.name}</span>
                                    <div class="count green">${element.tickets_count}</div>
                                </div>`;
                    document.getElementById('stats_widget').innerHTML = content;
                });
            });
        })
        .catch((err) => {
            console.log('Error', err);
        });
    };

    function filterTable(status_name){
        if(status_name == 'Overdue') {
            let query_string = `?status=overdue`;
            initDatatable(query_string);
        }else if(status_name == 'No Response') {
            let query_string = `?status=no_response`;
            initDatatable(query_string);
        }else if (status_name !== 'Overdue' && status_name !== 'No Response' ) {
            let route = "{{ route($setting->grab('admin_route').'.status.name', ['name' => 'status_name']) }}";         
            let url = route.replace('status_name', status_name);    
            fetch(url).then((res) => {
                res.json().then((data) => {
                    console.log(data);
                    let query_string = `?status=${data.data.id}`;
                    initDatatable(query_string);
                });
            }).catch((err) => {
                console.log('Error', err);
            });
        }
    }                          
              
</script>
@endpush