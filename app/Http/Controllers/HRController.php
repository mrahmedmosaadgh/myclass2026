<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HR;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HRController extends Controller
{
    public function index()
    {
        $hrs = HR::with('user')->paginate(10);
        $users = User::select('id', 'name')->get();

        // return Inertia::render('HR/Index', [
        //     'hrs' => $hrs,
        //     'users' => $users
        // ]);my_class\super_admin\HR

        return Inertia::render('my_class/super_admin/HR/Index', [
            'records' => $hrs,
            'options' => [

                'users' => $users
            ]
        ]);


    }

    public function create()
    {
        return Inertia::render('HR/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        HR::create($validated);

        return redirect()->back()->with('success', 'HR record created successfully');
    }

    public function update(Request $request, HR $hr)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        $hr->update($validated);

        return redirect()->back()->with('success', 'HR record updated successfully');
    }

    public function destroy(HR $hr)
    {
        $hr->delete();
        return redirect()->back()->with('success', 'HR record deleted successfully');
    }
}

