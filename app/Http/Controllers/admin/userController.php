<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class userController extends Controller
{
    //
    public function index()
    {
        //$users = User::latest()->paginate(); // commentted for search query pagination issue
        // dd($users);
        // $users = User::latest()->get()->map(function ($user) {
        //     return [
        //         'id' => $user->id,
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         //'created_id' => $user->created_at->format(config('app.date_format')),
        //         'created_id' => $user->created_at->toFormattedDate(), //using macro
        //     ];
        // });
        //3 ways we can format the date
        //1] in controller in map function 
        //2] in user model using accessor
        //3] Using macro by adding in AppServiceProvider $user->created_at->toFormattedDate()
        //4] in vue also we can format using moment package

        $searchQuery = request('query');
        $users = User::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            })
            ->latest()
            ->paginate();
        return $users;
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8',

        ]);
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);
        return $user;
    }
    public function update(User $user)
    {
        request()->validate([
            'name' => 'required',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8',
        ]);
        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password') ? bcrypt(request('password')) : $user->password,
        ]);
        return $user;
    }
    public function destory(User $user)
    {
        $user->delete();
        return response()->noContent();
    }

    public function changeRole(User $user)
    {
        $user->update([
            'role' => request('role')
        ]);
    }

    // public function search()
    // {
    //     $searchQuery = request('query');
    //     $users = User::where('name', 'like', "%{$searchQuery}%")->paginate();
    //     return response()->json($users);
    // }
    public function bulkDelete()
    {
        User::whereIn('id', request('ids'))->delete();
        return response()->json(['message' => 'Users deleted successfully.']);
    }
}
