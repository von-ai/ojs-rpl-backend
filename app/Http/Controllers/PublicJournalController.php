<?php

namespace App\Http\Controllers;

use App\ViewModels\PublicJournalViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicJournalController extends Controller
{
    public function index(Request $request): View
    {
        $viewModel = new PublicJournalViewModel(
            searchQuery: $request->input('q'),
            selectedCategory: $request->input('category')
        );

        return view('journal.index', compact('viewModel'));
    }
}
