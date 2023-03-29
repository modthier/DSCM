<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessageResource;

class ChartJSController extends Controller
{
    public function groupByMonth()
    {
        //$sellerOrders = BuyerSeller::where('seller_id', Auth::user()->id)->pluck('order_id')->all() ;
        if (Auth::user()->role_id != '1')
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }
        
        $orders = Order::select(DB::raw("COUNT(*) as count"), DB::raw("MONTHNAME(created_at) as month_name"))
                    ->whereYear('created_at', date('Y'))
                    ->groupBy(DB::raw("Month(created_at)"))
                    ->pluck('count', 'month_name');
 
        $labels = $orders->keys();
        $data = $orders->values();
              
        //return view('chart', compact('labels', 'data'));
        return response()->json([
            $labels,
            $data
        ]);
    }

    public function groupByCity()
    {
        //$sellerOrders = BuyerSeller::where('seller_id', Auth::user()->id)->pluck('order_id')->all() ;
        if (Auth::user()->role_id != '1')
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }
        
        $orders = Order::select(DB::raw("COUNT(*) as count"), DB::raw("destination_city as city_name"))
                    ->groupBy(DB::raw("destination_city"))
                    ->pluck('count', 'city_name');
 
        $labels = $orders->keys();
        $data = $orders->values();
              
        //return view('chart', compact('labels', 'data'));
        return response()->json([
            $labels,
            $data
        ]);
    }

    public function groupByYear()
    {
        //$sellerOrders = BuyerSeller::where('seller_id', Auth::user()->id)->pluck('order_id')->all() ;
        if (Auth::user()->role_id != '1')
        {
            $message = Message::where('key', 'unauthenticated')->first();
            return new MessageResource($message);
        }
        
        $orders = Order::select(DB::raw("COUNT(*) as count"), DB::raw("YEAR(created_at) as year"))
                    ->groupBy(DB::raw("YEAR(created_at)"))
                    ->pluck('count', 'year');
 
        $labels = $orders->keys();
        $data = $orders->values();
              
        //return view('chart', compact('labels', 'data'));
        return response()->json([
            $labels,
            $data
        ]);
    }
}
