<?php

namespace Modules\Newsletters\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewslettersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var view-string $view */
        $view = 'newsletters::index';

        return view($view);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var view-string $view */
        $view = 'newsletters::create';

        return view($view);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        /** @var view-string $view */
        $view = 'newsletters::show';

        return view($view);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        /** @var view-string $view */
        $view = 'newsletters::edit';

        return view($view);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
