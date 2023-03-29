<?php

namespace App\Http\Controllers\DashboardControllers;

use App\Models\Message;
use App\Models\DrugType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;

class DrugTypeController extends Controller
{
    public function index()
    {
        return DrugType::select('id', 'drug_type_title', 'drug_type_description')->with('drug')->get();
    }

    public function store(Request $request)
    {
        $input =  $request->validate([
            'drug_type_title' => 'required',
            'drug_type_description' => 'required'
        ]);

        $drugTypes = DrugType::create($input);
        $message = Message::where('key', 'success')->first();
        return new MessageResource($message);
    }

    public function show($id)
    {
        $drugTypes = DrugType::findOrFail($id);
        if ($drugTypes != null) {
            return $drugTypes;
        } else {
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }

    public function update(Request $request, $id)
    {

        $drugTypes = DrugType::findOrFail($id);
        if ($drugTypes != null) {
            // $input =  $request->validate([
            //     'drug_type_title' => 'required',
            //     'drug_type_description' => 'required'
            // ]);

            $drugTypes->update($request->all());
            $message = Message::where('key', 'success')->first();
            return new MessageResource($message);
        } else {
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }

    public function destroy($id)
    {
        $drugTypes = DrugType::findOrFail($id);

        if ($drugTypes != null) {
            $drugTypes->delete();
            // $drugTypes->drugType()->delete();
            $message = Message::where('key', 'success')->first();
            return new MessageResource($message);
        } else {
            $message = Message::where('key', 'not_found')->first();
            return new MessageResource($message);
        }
    }
}