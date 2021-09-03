<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\Tags;
use Kordy\Ticketit\Models\Ticket;

class TagsController extends Controller
{
   public function store(Request $request)
   {
        $this->validate($request, [
            'name' => 'required',
        ]);

       $tag = new Tags();
       $tag->name = $request->name;
       $tag->save();

       return response()->json(['success' => true, 'message' => 'Tag successfully created.'], 200);
   }

   public function all()
   {
       $tags = Tags::all();
       return response()->json(['success' => true, 'data' => $tags]);
   }

   public function getSelectedTagsByTicketId($id)
   {
       $selected_tags = Ticket::where('id', $id)->with(['tags'])->first();
       return response()->json(['success' => true, 'data' => $selected_tags->tags], 200);
   }
}
             