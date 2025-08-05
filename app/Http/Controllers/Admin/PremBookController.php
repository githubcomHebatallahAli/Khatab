<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PremBookRequest;
use App\Http\Resources\Admin\PremBookResource;
use App\Http\Resources\Admin\ShowAllPremBookResource;
use App\Models\PremBook;
use App\Traits\ManagesModelsTrait;


class PremBookController extends Controller
{
      use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $PremBooks = PremBook::get();
        return response()->json([
            'data' => ShowAllPremBookResource::collection($PremBooks),
            'message' => "Show All Premium Books Successfully."
        ]);
    }

    // public function showAllPremBook()
    // {
    //     $this->authorize('manage_users');

    //     // $Book = PremBook::paginate(10);

    //     $Book = PremBook::orderBy('created_at', 'desc')->paginate(10);

    //     return response()->json([
    //         'data' => ShowAllPremBookResource::collection($Book),
    //         'pagination' => [
    //             'total' => $Book->total(),
    //             'count' => $Book->count(),
    //             'per_page' => $Book->perPage(),
    //             'current_page' => $Book->currentPage(),
    //             'total_pages' => $Book->lastPage(),
    //             'next_page_url' => $Book->nextPageUrl(),
    //             'prev_page_url' => $Book->previousPageUrl(),
    //         ],
    //         'message' => "Show All Premium Books Successfully."
    //     ]);
    // }

      public function userShowAll()
    {
        $PremBooks = PremBook::get();
        return response()->json([
            'data' => ShowAllPremBookResource::collection($PremBooks),
            'message' => "Show All Premium Books Successfully."
        ]);
    }


    public function create(PremBookRequest $request)
    {
        $this->authorize('manage_users');

           $PremBook =PremBook::create ([
                "book_id" => $request->book_id
            ]);
           $PremBook->save();
           return response()->json([
            'data' =>new PremBookResource($PremBook),
            'message' => "Premium Book Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $PremBook = PremBook::find($id);

        if (!$PremBook) {
            return response()->json([
                'message' => "Premium Book not found."
            ], 404);
        }

        return response()->json([
            'data' =>new PremBookResource($PremBook),
            'message' => "Edit Premium Book By ID Successfully."
        ]);
    }


    public function userEdit(string $id)
    {  
        $PremBook = PremBook::find($id);

        if (!$PremBook) {
            return response()->json([
                'message' => "Premium Book not found."
            ], 404);
        }

        return response()->json([
            'data' =>new PremBookResource($PremBook),
            'message' => "Edit Premium Book By ID Successfully."
        ]);
    }

    public function update(PremBookRequest $request, string $id)
    {
        $this->authorize('manage_users');
       $PremBook =PremBook::findOrFail($id);

       if (!$PremBook) {
        return response()->json([
            'message' => "Premium Book not found."
        ], 404);
    }
       $PremBook->update([
        "book_id" => $request->book_id
        ]);

       $PremBook->save();
       return response()->json([
        'data' =>new PremBookResource($PremBook),
        'message' => " Update Premium Book By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(PremBook::class,PremBookResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$PremBooks=PremBook::onlyTrashed()->get();
return response()->json([
    'data' =>PremBookResource::collection($PremBooks),
    'message' => "Show Deleted Premium Books Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$PremBook = PremBook::withTrashed()->where('id', $id)->first();
if (!$PremBook) {
    return response()->json([
        'message' => "Premium Book not found."
    ], 404);
}
$PremBook->restore();
return response()->json([
    'data' =>new PremBookResource($PremBook),
    'message' => "Restore Premium Book By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(PremBook::class, $id);
  }
}
