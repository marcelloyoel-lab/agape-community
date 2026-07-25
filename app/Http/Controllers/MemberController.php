<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{

    public function __construct(
        protected MemberService $memberService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = $this->memberService->getMembers();

        return view('member.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        //
    }
}
