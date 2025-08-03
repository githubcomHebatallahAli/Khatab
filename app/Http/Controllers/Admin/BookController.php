<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
use App\Http\Resources\Admin\BookResource;
use App\Http\Resources\Admin\ShowAllBookResource;
use App\Models\Book;

use App\Traits\ManagesModelsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
        use ManagesModelsTrait;
    public function showAll(Request $request)
  {
    $this->authorize('manage_users');
     
     $query = Book::query();
    if ($request->filled('grade_id')) {
        
        $gradeIds = explode(',', $request->grade_id);
        $query->whereIn('grade_id', $gradeIds);
    }

        if ($request->filled('nameOfBook')) {
            $query->where('nameOfBook', 'like', '%' . $request->nameOfBook . '%');
        }
    
       
     $Books = $query->orderBy('created_at', 'desc')->get();
      return response()->json([
          'data' => ShowAllBookResource::collection($Books),
          'message' => "Show All Books Successfully."
      ]);
  }

    public function userShowAll(Request $request)
  {
     $query = Book::with('grade')->query();
     
   
         if ($request->filled('grade_id')) {
       
        $gradeIds = explode(',', $request->grade_id);
        $query->whereIn('grade_id', $gradeIds);
    }

        if ($request->filled('nameOfBook')) {
            $query->where('nameOfBook', 'like', '%' . $request->nameOfBook . '%');
        }
    
     $Books = $query->orderBy('created_at', 'desc')->get();

      return response()->json([
          'data' => ShowAllBookResource::collection($Books),
          'message' => "Show All Books Successfully."
      ]);
  }


  public function create(BookRequest $request)
  {
      $this->authorize('manage_users');
      $formattedPrice = number_format($request->price, 2, '.', '');
      

         $Book =Book::create ([
            "grade_id" => $request->grade_id,
            "nameOfBook" => $request->nameOfBook,
            "price" => $formattedPrice,
            "description" => $request->description,
            'creationDate' => now()->format('Y-m-d')
        ]);

        if ($request->hasFile('img')) {
          $imgPath = $request->file('img')->store(Book::storageFolder);
          $Book->img =  $imgPath;
      }

         $Book->save();
    
         return response()->json([
          'data' =>new BookResource($Book),
          'message' => "Book Created Successfully."
      ]);

      }


  public function edit(string $id)
  {
    $this->authorize('manage_users');
      $Book = Book::find($id);
      if (!$Book) {
          return response()->json([
              'message' => "Book not found."
          ]);
      }

      return response()->json([
          'data' =>new BookResource($Book),
          'message' => "Edit Book By ID Successfully."
      ]);
  }

  public function userEdit(string $id)
  {
      $Book = Book::find($id);
      if (!$Book) {
          return response()->json([
              'message' => "Book not found."
          ]);
      }

      return response()->json([
          'data' =>new BookResource($Book),
          'message' => "Edit Book By ID Successfully."
      ]);
  }



  public function update(BookRequest $request, string $id)
  {
      $this->authorize('manage_users');

      $formattedPrice = number_format($request->price, 2, '.', '');
      
     $Book =Book::findOrFail($id);

     if (!$Book) {
      return response()->json([
          'message' => "Book not found."
      ]);
  }
     $Book->update([
        "grade_id" => $request->grade_id,
        "nameOfBook" => $request->nameOfBook,
        "price" => $formattedPrice,
        "description" => $request->description,
        // 'creationDate' => today()->toDateString(),
        'creationDate' => $request -> creationDate
      ]);

      if ($request->hasFile('img')) {
        if ($Book->img) {
            Storage::disk('public')->delete($Book->img);
        }
        $imgPath = $request->file('img')->store('Book', 'public');
        $Book->img = $imgPath;
    }

     $Book->save();
     return response()->json([
      'data' =>new BookResource($Book),
      'message' => " Update Book By Id Successfully."
  ]);
}

  public function destroy(string $id)
  {
      return $this->destroyModel(Book::class, BookResource::class, $id);
  }

  public function showDeleted(){
    $this->authorize('manage_users');
$Books=Book::onlyTrashed()->get();
return response()->json([
    'data' =>BookResource::collection($Books),
    'message' => "Show Deleted Books Successfully."
]);
}


public function restore(string $id)
{
$this->authorize('manage_users');
$Book = Book::withTrashed()->where('id', $id)->first();
if (!$Book) {
    return response()->json([
        'message' => "Book not found."
    ]);
}

$Book->restore();
return response()->json([
    'data' =>new BookResource($Book),
    'message' => "Restore Book By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Book::class, $id);
  }
}
