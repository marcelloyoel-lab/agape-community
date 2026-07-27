<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMinistryRequest;
use App\Models\Ministry;
use App\Services\MinistryService;
use Illuminate\Http\Request;
use Throwable;

class MinistryController extends Controller
{

    public function __construct(
        private readonly MinistryService $ministryService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ministries = Ministry::query()
            ->orderBy('display_order')
            ->get();

        return view('ministries.index', compact('ministries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ministries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMinistryRequest $request)
    {
        try {
            $this->ministryService->create($request->validated());

            return redirect()
                ->route('ministries.index')
                ->with('success', 'Ministry created successfully.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create ministry. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ministry $ministry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ministry $ministry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ministry $ministry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ministry $ministry)
    {
        //
    }
}
