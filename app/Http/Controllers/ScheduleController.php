<?php

namespace App\Http\Controllers;

use App\Enums\ScheduleStatus;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $schedule->load([
            'creator',
            'assignments' => fn ($query) => $query
                ->with([
                    'ministry',
                    'member',
                ])
                ->orderBy('display_order'),
        ]);

        $groupedAssignments = $schedule->assignments
            ->groupBy('ministry_id')
            ->sortBy(
                fn ($assignments) => $assignments->first()->ministry->display_order
            );

        return view('schedule.show', compact(
            'schedule',
            'groupedAssignments'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        abort_unless(
            in_array($schedule->status, [
                ScheduleStatus::DRAFT,
                ScheduleStatus::REJECTED,
            ], true),
            403
        );

        $schedule->load('assignments');

        $ministries = Ministry::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $assignedMemberIds = $schedule->assignments()
            ->pluck('member_id');

        $members = Member::query()
            ->where(function ($query) use ($assignedMemberIds) {
                $query->where('is_active', true)
                    ->orWhereIn('id', $assignedMemberIds);
            })
            ->orderBy('name')
            ->get();

        $selectedAssignments = $schedule->assignments
            ->groupBy('ministry_id')
            ->map(fn ($assignments) => $assignments
                ->sortBy('display_order')
                ->pluck('member_id')
                ->all()
            );

        return view('schedule.edit', compact(
            'schedule',
            'ministries',
            'members',
            'selectedAssignments'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateScheduleRequest $request,
        Schedule $schedule
    ): RedirectResponse {
        abort_unless(
            in_array($schedule->status, [
                ScheduleStatus::DRAFT,
                ScheduleStatus::REJECTED,
            ], true),
            403
        );

        try {
            $this->scheduleService->update(
                $schedule,
                $request->validated(),
                (int) $request->user()->id
            );

            return redirect()
                ->route('schedules.index')
                ->with('success', 'Weekly schedule updated successfully.');

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update the weekly schedule. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        //
    }

    public function poster(Schedule $schedule)
    {
        $schedule->load([
            'assignments' => fn ($query) => $query->orderBy('display_order'),
            'assignments.member',
            'assignments.ministry',
        ]);

        $assignments = $schedule->assignments->groupBy('ministry.name');

        $posterData = [
            'mc' => $assignments->get('MC', collect()),
            'firman' => $assignments->get('Pelayan Firman', collect()),
            'music' => $assignments->get('Music', collect()),
            'multimedia' => $assignments->get('Multimedia', collect()),
        ];

        Log::info('Schedule poster preview viewed.', [
            'schedule_id' => $schedule->id,
            'viewed_by' => auth()->id(),
        ]);

        return view('poster.template', compact(
            'schedule',
            'posterData'
        ));
    }
}
