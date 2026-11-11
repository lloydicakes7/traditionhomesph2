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
}
