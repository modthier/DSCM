<?php

namespace App\Http\Controllers\DashboardControllers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Message;
use App\Models\BuyerSeller;
use App\Models\OrderDetails;
use App\Models\StockDetails;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessageResource;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Requests\AddOrderDetailsRequest;

class OrderDetailsController extends Controller
{
    use HttpResponses;
    
    
    public function store(AddOrderDetailsRequest $request)
    {
        $request->validated($request->all());
       
        $stockDetails = StockDetails::where('id', $request->stock_details_id)->first();
        $buyerSellerOrder = BuyerSeller::where('order_id', $request->order_id)->first();
        $order = Order::where('id', $request->order_id)->first();        

        if ( $stockDetails === null || $buyerSellerOrder === null )
        {
            return $this->message('not_found');
        }
        
        if ($this->check($stockDetails, $buyerSellerOrder))
        {
            return $this->check($stockDetails, $buyerSellerOrder);
        }

        if ($request->drug_amount > $stockDetails->drug_amount)
        {
            return $this->message('amount_not_available');
        }

        $totalCost = $request->drug_amount * $stockDetails->drug_unit_price;

        $productionDate = $stockDetails->production_date;
        $expirationDate = $stockDetails->expiration_date;
        $unitPrice = $stockDetails->drug_unit_price;
        //return $request;
        
        $orderDetails = OrderDetails::create([
            'production_date' => $productionDate,
            'expiration_date' => $expirationDate,
            'drug_amount' => $request->drug_amount,
            'drug_unit_price' => $unitPrice,
            'drug_total_cost' => $totalCost,
            'order_id' => $request->order_id,
            'stock_details_id' => $request->stock_details_id
        ]);

        // $order->order_total_cost = $order->order_total_cost + $orderDetails->drug_total_cost;
        // $order->save();

        return new OrderDetailsResource($orderDetails);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderDetails  $orderDetails
     * @return \Illuminate\Http\Response
     */
    public function show(OrderDetails $orderDetails)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $orderDetails->order_id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id || Auth::user()->id === $buyerSellerOrder->seller_id)
        {
            return new OrderDetailsResource($orderDetails);
        }

        $message = Message::where('key', 'unauthenticated')->first();

        return new MessageResource($message);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderDetails  $orderDetails
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDetails $orderDetails)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $orderDetails->order_id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id || Auth::user()->id === $buyerSellerOrder->seller_id)
        {
            $orderDetails->update($request->all());

            return new OrderDetailsResource($orderDetails);
        }

        $message = Message::where('key', 'unauthenticated')->first();

        return new MessageResource($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderDetails  $orderDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDetails $orderDetails)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $orderDetails->order_id)->first();
        
        if (Auth::user()->id === $buyerSellerOrder->buyer_id)
        {
            return $orderDetails->delete();
        }

        $message = Message::where('key', 'unauthenticated')->first();

        return new MessageResource($message);
    }

    public function getOrderDetails(Order $order)
    {
        $buyerSellerOrder = BuyerSeller::where('order_id', $order->id)->first();

        if (Auth::user()->id === $buyerSellerOrder->buyer_id || Auth::user()->id === $buyerSellerOrder->seller_id)
        {
            return OrderDetailsResource::collection(
                OrderDetails::where('order_id', $order->id)->get()
            );
        }
    }

    private function message($key)
    {
        $message = Message::where('key', $key)->first();
        return new MessageResource($message);
    }

    private function check(StockDetails $stockDetails, BuyerSeller $buyerSellerOrder)
    {
        $stock = Stock::where('id', $stockDetails->stock_id)->first();

        if (Auth::user()->id !== $buyerSellerOrder->buyer_id || Auth::user()->id === $stock->user_id || 
            $stock->user_id !== $buyerSellerOrder->seller_id)
        {
            return $this->message('unauthenticated');
        }
    }
}
