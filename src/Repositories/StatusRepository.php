<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Status;

class StatusRepository {

    public function getAll()
    {
        return Status::all();
    }

    public function getById($id)
    {
       return Status::find($id); 
    }
    
    public function update_asana_gid($id, $tag_gid)
    {
        $status = Status::find($id);
        $status->asana_tag_gid = $tag_gid;
        
        return $status->save();
    }
}