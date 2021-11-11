<?php
namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\Tags;
use Kordy\Ticketit\Models\Ticket;

class TagsController extends Controller
{
    public function __construct()
    {
        
    }

    public function index()
    {
        $tags = Tags::orderBy('id', 'DESC')->get();
        return view('ticketit::admin.tags.index', compact('tags'));
    }

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

    public function create()
    {
        return view('ticketit::admin.tags.create');
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $tag = new Tags();
        $tag->name = $request->name;
        if($tag->save()) {
            session()->flash('status', 'Tag successfully added.');
            return redirect('tickets/tags/index');
        } else {
            session()->flash('error', 'Tag unsuccessfully added.');
            return redirect('tickets/tags/index');
        }
    }

    public function show($id)
    {
        $tag = Tags::find($id);
        return view('ticketit::admin.tags.show')->with('tag', $tag);
    }

    public function update(Request $request, $id)
    {
        $tag = Tags::find($id);
        if($tag) {
            $tag->name = $request->name;
            if($tag->save()) {
                session()->flash('status', 'Tag successfully updated.');
                return redirect('tickets/tags/index');
            } else {
                session()->flash('error', 'Tag unsuccessfully updated.');
                return redirect('tickets/tags/index');
            }
        } else {
            return redirect('tickets/tags/index');
        }
    }

    public function delete($id)
    {
        $tag = Tags::where('id', $id)->first();
        if($tag->delete()) {
            session()->flash('status', 'Tag successfully removed.');
            return response()->json(['success' => true, 'message' => 'Tag successfully deleted.']);
        } else {
            session()->flash('error', 'Tag unsuccessfully removed.');
            return response()->json(['success' => false, 'message' => 'Tag unsuccessfully deleted.']);
        }
    }
}
