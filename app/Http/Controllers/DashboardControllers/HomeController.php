<?php

namespace App\Http\Controllers\DashboardControllers;

use App\Models\Drug;
use App\Models\Role;
use App\Models\User;
use App\Models\Stock;
use App\Models\BuyerSeller;
use App\Models\StockDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\StockDetailsResource;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function displayClients ()
    {
        $clients = BuyerSeller::where('seller_id', Auth::user()->id)->pluck('buyer_id')->all();
        
        return UserResource::collection(
            User::whereIn('id', $clients)->get()
        );
    }

    public function drugShortageAlert ()
    {
        $stocks = Stock::where('user_id', Auth::user()->id)->pluck('id')->all();
        
        return StockDetailsResource::collection(
            StockDetails::whereIn('stock_id', $stocks)->where('drug_amount', '<', 50)->get()
        );
    }

    public function sellers ()
    {
        if (Auth::user()->role_id === "2")
        {
            return UserResource::collection(
                User::where('role_id', '1')->get()
            );
        }
        
        return UserResource::collection(
            User::where('role_id', '2')->get()
        );
    }

    public function search($name)
    {
        $role = Role::where('id', Auth::user()->role_id)->first();
        $drug = Drug::query()->where('trade_name', 'LIKE', $name)->orWhere('scientific_name', 'LIKE', $name)
            ->first();
        $users = User::where('role_id', $role->role-1)->pluck('id')->all();
        $stocks = Stock::whereIn('user_id', $users)->pluck('id')->all();

        $stockDetails = StockDetails::whereIn('stock_id', $stocks)->where('drug_id', $drug->id)->get();

        
        return response()->json($stockDetails);
    }

    // public function autocomplete(Request $request)
    // {        
    //     $data = Drug::select("id")
    //             ->where("trade_name","LIKE","%{$request->str}%")
    //             ->orWhere("scientific_name","LIKE","%{$request->str}%")
    //             ->get('query');   
    //     return response()->json($data);
    // }

    public function replenishmentAlert ()
    {
        $stockDetails = StockDetails::where('supplier_email', Auth::user()->email)->where('drug_amount', '<', 50)->get();
        $data = array();
        
        foreach ($stockDetails as $sd) {
            $stock = Stock::where('id', $sd->stock_id)->first();
            $owner = User::where('id', $stock->user_id)->first();
            $drug = Drug::where('id', $sd->drug_id)->first();

            $data[] = [
                'drug_owner' => $owner->name, 
                'drug_name' => $drug->trade_name
            ];
        }

        return response()->json($data);
    }
}

