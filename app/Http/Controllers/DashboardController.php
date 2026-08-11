<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }
    
    public function analytics()
    {
        return view('dashboard.analytics');
    }
    
    public function traffic()
    {
        return view('dashboard.traffic');
    }
    
    public function salesReport()
    {
        return view('dashboard.sales-report');
    }
    
    // Tambahkan method lain sesuai kebutuhan
}