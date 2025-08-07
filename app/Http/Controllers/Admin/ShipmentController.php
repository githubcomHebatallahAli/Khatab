<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ShipmentRequest;
use App\Http\Resources\User\ShipmentResource;
use App\Models\Book;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
public function create(ShipmentRequest $request)
{
    DB::beginTransaction();

    try {
        $subtotal = 0;

        // حساب المجموع الفرعي
        foreach ($request->books as $item) {
            $book = Book::findOrFail($item['id']);
            $subtotal += $book->price * $item['quantity'];
        }

        $shipping = 10.0;
        $tax = 0.0;
        $total = $subtotal + $shipping + $tax;

        // حفظ الطلب
        $order = Shipment::create([
            'full_name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
        ]);

        // حفظ تفاصيل الكتب داخل الطلب
        foreach ($request->books as $item) {
            $book = Book::findOrFail($item['id']);
            $order->books()->create([
                'book_id' => $book->id,
                'quantity' => $item['quantity'],
                'price' => $book->price,
                'total_price' => $book->price * $item['quantity'],
            ]);
        }

        DB::commit();
        return (new ShipmentResource($order))->additional([
            'message' => 'تم إنشاء الطلب بنجاح'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'حدث خطأ أثناء تنفيذ الطلب', 'details' => $e->getMessage()], 500);
    }
}


    public function show($id)
    {
        $shipment = Shipment::with('books')
        ->findOrFail($id);
        return new ShipmentResource($shipment);
    }

    public function edit($id)
    {
        $this->authorize('manage_users');
        $shipment = Shipment::with('books')
        ->findOrFail($id);
        return new ShipmentResource($shipment);
    }

    public function showAll()
    {
        $this->authorize('manage_users');
        $shipments = Shipment::with('books')->get();
        return ShipmentResource::collection($shipments);
    }
    
    public function destroy($id)
    {
        $this->authorize('manage_users');
        $shipment = Shipment::findOrFail($id);
        $shipment->delete();
        return response()->json(['message' => 'Shipment deleted successfully']);
    }
}
