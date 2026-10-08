<?php

namespace App\Http\Controllers;

use App\ViewModels\AuthorDashboardViewModel;
use Illuminate\View\View;

class AuthorDashboardController extends Controller
{
    /**
     * Display the Author Dashboard Portal.
     */
    public function index(): View
    {
        $viewModel = new AuthorDashboardViewModel;

        return view('dashboard.index', compact('viewModel'));
    }
}
