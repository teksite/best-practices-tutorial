<?php

namespace Modules\User\Http\Controllers\Api\Panel\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Uploader\Enums\DiskType;
use Modules\User\Models\User;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        return response()->json([]);
    }

    /**
     * Show the specified resource.
     */
    public function show(User $user)
    {
        //

        return response()->json([]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = uploadFile($request->file('avatar'), [
            'disk' => DiskType::PUBLIC,
        ]);

        return ;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //

        return response()->json([]);
    }
}
