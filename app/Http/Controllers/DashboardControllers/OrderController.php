<?php

namespace App\Http\Controllers\DashboardControllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Status;
use App\Models\Message;
use App\Models\BuyerSeller;
use App\Models\OrderDetails;
use App\Models\StockDetails;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Resources\MessageResource;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    use HttpResponses;
    
    
    public function store(StoreOrderRequest $request)
    {
        $orderDate = Carbon::now();
        $orderDate->toDateTimeString();

        $request->validated($request->all());

        $buyerRole = Role::where('id', Auth::user()->role_id)->first();
        $sellerRole = Role::where('id', $request->seller_id)->first();
        $buyerStock = Stock::where('stock_number', $request->buyer_stock_number)->first();

        if (Auth::user()->id !== $buyerStock->user_id || $buyerStock == null)
        {
            $message = Message::where('key', 'invalid_stock_number')->first();
            return new MessageResource($message);
        }

        $z = $buyerRole->role - $sellerRole->role;
        
        if ($buyerRole->role === 4 || $sellerRole->role === 4 || Auth::user()->id === $request->seller_id || $z != 1)
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }

        // if ($z != 1)
        // {
        //     $message = Message::where('key', 'unauthenticated')->first();
        //     return new MessageResource($message);
        // }
        //return $request;

        $order = Order::create([
            'order_description' => $request->order_description,
            'order_date' => $orderDate,
            'buyer_stock_number' => $request->buyer_stock_number,
            'destination_city' => $request->destination_city,
            'destination_address' => $request->destination_address,
            'status_id' => '1'
        ]);

        $buyerSeller = BuyerSeller::create([
            'order_id' => $order->id,
            'buyer_id' => Auth::user()->id,
            'seller_id' => $request->seller_id
        ]);

        return new OrderResource($order);
    }

    
    public function show(Order $order)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id || Auth::user()->id === $buyerSellerOrder->seller_id)
        {
            return new OrderResource($order);
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);
    }

    
    public function update(Request $request, Order $order)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id)
        {
            $order->update($request->all());

            return response()->json($order);
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);
    }

    
    public function destroy(Order $order)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id)
        {
            $order->delete();

            return response()->json('Order Deleted');
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);
    }

    public function getBuyerOrders ()
    {
        $buyerSellerOrder = BuyerSeller::where('buyer_id', Auth::user()->id)->pluck('order_id')->all();

        return OrderResource::collection(
            Order::where('id', $buyerSellerOrder)->get()
        );
    }

    public function getSellerOrders ()
    {
        $buyerSellerOrder = BuyerSeller::where('seller_id', Auth::user()->id)->pluck('order_id')->all();

        return OrderResource::collection(
            Order::where('id', $buyerSellerOrder)->get()
        );
    }

    public function approveOrder(Order $order)
    {
        $status = Status::where('id', '2')->first();
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();

        if (Auth::user()->id !== $buyerSellerOrder->seller_id)
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }

        if ($order->status_id == '3' || $order->status_id == '4')
        {
            $message = Message::where('key', 'fail')->first();
            return new MessageResource($message);
        }

        $order->status_id = $status->id;
        $order->save();

        $message = Message::where('key', 'success')->first();

        return new MessageResource($message);
    }
    
    public function performOrder(Order $order)
    {
        if ($order->status_id == '3' || $order->status_id == '4')
        {
            $message = Message::where('key', 'fail')->first();
            return new MessageResource($message);
        }

        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();

        if (Auth::user()->id !== $buyerSellerOrder->seller_id)
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }
        
        $entryDate = Carbon::now();
        $entryDate->toDateTimeString();

        $totalCost = 0.0;
        
        $orderDetails = OrderDetails::where('order_id', $order->id)->get();
        $buyerStock = Stock::where('stock_number', $order->buyer_stock_number)->first();
        $status = Status::where('id', '3')->first();
        
        
        foreach ($orderDetails as $od) {
            # code...
            $orderedAmount = $od->drug_amount;
            $stockDetails = StockDetails::where('id', $od->stock_details_id)->first();
            $stockAmount = $stockDetails->drug_amount;
            $newAmount = $stockAmount - $orderedAmount;

            $totalCost = $totalCost + $od->drug_total_cost;
            $supplierEmail = User::where('id', $buyerSellerOrder->seller_id)->first();

            $buyerStockDetails = StockDetails::create([
                'drug_amount' => $od->drug_amount,
                'drug_entry_date' => $entryDate,
                'drug_residual' => $stockDetails->drug_residual,
                'production_date' => $stockDetails->production_date,
                'expiration_date' => $stockDetails->expiration_date,
                'drug_unit_price' => $stockDetails->drug_unit_price,
                'stock_id' => $buyerStock->id,
                'drug_id' => $stockDetails->drug_id,
                'supplier_email' => $supplierEmail->email
            ]);

            $stockDetails->drug_amount = $newAmount;
            $stockDetails->save();
        }

        $order->status_id = $status->id;
        $order->order_total_cost = $totalCost;
        $order->save();
        return new OrderResource($order);
    }

    public function cancelOrder(Order $order)
    {
        $status = Status::where('id', '4')->first();
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();

        if (Auth::user()->id !== $buyerSellerOrder->buyer_id)
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }

        if ($order->status_id == '3')
        {
            $message = Message::where('key', 'fail')->first();
            return new MessageResource($message);
        }
        
        $order->status_id = $status->id;
        $order->save();

        $message = Message::where('key', 'success')->first();
        return new MessageResource($message);
    }

    private function validateUser (Order $order)
    {

    }
}
