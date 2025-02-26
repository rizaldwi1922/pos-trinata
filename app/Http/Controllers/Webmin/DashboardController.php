<?php

namespace App\Http\Controllers\Webmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return self::view('web.admin.dashboard.index');
    }
}
