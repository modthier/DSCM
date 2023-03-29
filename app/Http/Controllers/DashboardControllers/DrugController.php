<?php

namespace App\Http\Controllers\DashboardControllers;

use App\Models\Drug;
use App\Models\Message;
use App\Models\DrugType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;

class DrugController extends Controller
{
        public function index()
    {
        return Drug::select('id', 'trade_name', 'scientific_name',
        'drug_description', 'drug_dose', 'image')->with('drugType')->get();
    }

    public function store(Request $request)
    {
        // $drug_type =DrugType::all();
        $input =  $request->validate([
            'trade_name' => 'required',
            'scientific_name' => 'required',
            'drug_description' => 'required',
            'drug_dose' => 'required',
            'drug_type_id' => 'required'
            //'image'=>'required'
            // 'image'=>'required|image'
        ]);

        $drugs = Drug::create($input);
        $message = Message::where('key', 'success')->first();
        return new MessageResource($message);
    }

    public function show($id)
    {
        $drugs = Drug::where('id', $id)->with('drugType')->get();
        if ($drugs != null) {
            return $drugs;
        }
        else {
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }

    public function update(Request $request, $id)
    {
        $drugs = Drug::where('id', $id)->with('drugType')->first();
        if ($drugs != null) {
            // $input =  $request->validate([
            //     'trade_name' => 'required',
            //     'scientific_name' => 'required',
            //     'drug_description' => 'required',
            //     'drug_dose' => 'required',
            //     'image'=>'required'
            //     // 'image'=>'required|image'
            // ]);

            $drugs->update($request->all());
            $message = Message::where('key', 'success')->first();
            return new MessageResource($message);
        } else {
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }

    public function destroy($id)
    {
        $drugs = Drug::findOrFail($id);

        if ($drugs != null) {
            $drugs->delete();
            $message = Message::where('key', 'success')->first();
            return new MessageResource($message);
        }
        else{
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }

    public function getDrugsByType (DrugType $drugType)
    {
        $drugs = Drug::where('drug_type_id', $drugType->id)->with('drugType')->get();
        if ($drugs != null)
        {
            return response()->json($drugs);
        }

        $message = Message::where('key', 'not_found')->first();
        return new MessageResource($message);
    }
}