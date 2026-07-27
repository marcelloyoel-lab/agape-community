<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMinistryRequest;
use App\Http\Requests\UpdateMinistryRequest;
use App\Models\Ministry;
use App\Services\MinistryService;
use Illuminate\Http\RedirectResponse;
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
        return view('ministries.edit', compact('ministry'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateMinistryRequest $request,
        Ministry $ministry
    ): RedirectResponse {
        try {
            $this->ministryService->update(
                $ministry,
                $request->validated()
            );

            return redirect()
                ->route('ministries.index')
                ->with('success', 'Ministry updated successfully.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update ministry. Please try again.');
        }
    }

    public function toggleStatus(Ministry $ministry): RedirectResponse
    {
        try {
            $ministry = $this->ministryService->toggleStatus($ministry);

            $status = $ministry->is_active
                ? 'activated'
                : 'deactivated';

            return redirect()
                ->route('ministries.index')
                ->with('success', "Ministry {$status} successfully.");
        } catch (Throwable $e) {
            return redirect()
                ->route('ministries.index')
                ->with('error', 'Failed to update ministry status. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ministry $ministry)
    {
        try {
            $this->ministryService->delete($ministry);

            return redirect()
                ->route('ministries.index')
                ->with('success', 'Ministry deleted successfully.');
        } catch (\DomainException $e) {
            return redirect()
                ->route('ministries.index')
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()
                ->route('ministries.index')
                ->with('error', 'Failed to delete ministry. Please try again.');
        }
    }
}
