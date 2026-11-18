<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Show the user management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.user-management.index');
    }

    /**
     * Show the role management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function roles()
    {
        return view('admin.user-management.roles');
    }

    /**
     * Show the activity trail of what the office has done.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activities()
    {
        return view('admin.user-management.activities');
    }
}
