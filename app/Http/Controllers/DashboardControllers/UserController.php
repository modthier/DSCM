<?php

namespace App\Http\Controllers\DashboardControllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MessageResource;

class UserController extends Controller
{
    use HttpResponses;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role = Role::where('descrition', 'Admin')->firstOrFail();
        
        if (Auth::user()->role_id === $role->id)
        {
            return UserResource::collection(
                User::all()
            );
        }
        
        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $role = Role::where('descrition', 'Admin')->firstOrFail();
        
        if (Auth::user()->id === $user->id || Auth::user()->role_id === $role->id)
        {
            return new UserResource ($user);
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->id === $user->id)
        {
            $user->update($request->all());

            return new UserResource ($user);
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $role = Role::where('descrition', 'Admin')->firstOrFail();
        
        if (Auth::user()->id === $user->id || Auth::user()->role_id === $role->id)
        {
            $user->delete();
            $user->tokens()->delete();
            
            $message = Message::where('key', 'success')->first();
            return new MessageResource($message);
        }

        $message = Message::where('key', 'unauthenticated')->first();
        return new MessageResource($message); 
    }

    public function getWholesalers ()
    {
        $role = Role::where('descrition', 'Wholesaler')->firstOrFail();
        
        return UserResource::collection(
            User::where('role_id', $role->id)->get()
        );
    }

    public function getRetailers ()
    {
        $role = Role::where('descrition', 'retailer')->firstOrFail();
        
        return UserResource::collection(
            User::where('role_id', $role->id)->get()
        );
    }

    public function getEmployees ()
    {
        $role = Role::where('descrition', 'Employee')->firstOrFail();
        
        return UserResource::collection(
            User::where('role_id', $role->id)->get()
        );
    }
}
