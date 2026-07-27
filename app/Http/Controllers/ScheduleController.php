<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(
        private readonly ScheduleService $scheduleService
    ) {
    }
    
    public function index()
    {
        $schedules = Schedule::query()
            ->with('creator')
            ->withCount('assignments')
            ->latest('service_date')
            ->get();

        return view('schedule.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ministries = Ministry::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $members = Member::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('schedule.create', compact(
            'ministries',
            'members'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        try {
            $schedule = $this->scheduleService->create(
                $request->validated(),
                (int) $request->user()->id
            );

            return redirect()
                ->route('schedules.index', $schedule)
                ->with('success', 'Weekly schedule created successfully.');

        } catch (Throwable) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create the weekly schedule. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        //
    }
}
