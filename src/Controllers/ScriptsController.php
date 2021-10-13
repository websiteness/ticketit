<?php
namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\Scripts;
use Kordy\Ticketit\Models\Ticket;

class ScriptsController extends Controller
{
    public function index()
    {
        $scripts = Scripts::orderBy('id', 'DESC')->get();
        return view('ticketit::admin.scripts.index', compact('scripts'));
    }

    public function store(Request $request)
    {
        if($request->ajax()) {
            $script = new Scripts();
            $script->title = $request->title;
            $script->content = $request->content;
            if($script->save()) {
                 return response()->json(['success' => true, 'message' => 'Script successfully created.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Error creating script.']);
            }             
        } else {
            $this->validate($request, [
                'name' => 'required',
            ]);
    
            $tag = new Scripts();
            $tag->name = $request->name;
            $tag->save();
    
            return response()->json(['success' => true, 'message' => 'Tag successfully created.'], 200);
        }
    }

    public function all()
    {
        $tags = Scripts::all();
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

        $tag = new Scripts();
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
        $tag = Scripts::find($id);
        return view('ticketit::admin.tags.show')->with('tag', $tag);
    }

    public function update(Request $request, $id = null)
    {
        if($request->ajax()) {
            $script = Scripts::find($request->id);
            $script->title = $request->title ?? '';
            $script->content = $request->content ?? '';
            if($script->save()) {
                return response()->json(['success' => true, 'message' => 'Script successfully updated.']);
           } else {
               return response()->json(['success' => false, 'message' => 'Error updating script.']);
           }    
        } else {
            $script = Scripts::find($id);
            if($script) {
                $script = Scripts::find($request->id);
                $script->title = $request->title ?? '';
                $script->content = $request->content ?? '';

                if($script->save()) {
                    session()->flash('status', 'Script successfully updated.');
                    return redirect('tickets/scripts/index');
                } else {
                    session()->flash('error', 'Error updating script.');
                    return redirect('tickets/scripts/index');
                }
            } else {
                return redirect('tickets/scripts/index');
            }
        }
  
    }

    public function delete($id)
    {
        $script = Scripts::where('id', $id)->first();
        if($script->delete()) {
            session()->flash('status', 'Script successfully removed.');
            return response()->json(['success' => true, 'message' => 'Tag successfully deleted.']);
        } else {
            session()->flash('error', 'Script unsuccessfully removed.');
            return response()->json(['success' => false, 'message' => 'Tag unsuccessfully deleted.']);
        }
    }
}
