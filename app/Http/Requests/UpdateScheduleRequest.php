<?php

namespace App\Http\Requests;

use App\Models\Ministry;
use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'service_time' => [
                'required',
                'date_format:H:i',
            ],

            'assignments' => [
                'required',
                'array',
            ],

            'assignments.*' => [
                'required',
                'array',
                'min:1',
            ],

            'assignments.*.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('members', 'id')
                    ->where('is_active', true),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateScheduleUniqueness($validator);
                $this->validateMinistryAssignments($validator);
            },
        ];
    }

    private function validateScheduleUniqueness(Validator $validator): void
    {
        if (!$this->filled('service_date') || !$this->filled('service_time')) {
            return;
        }

        /** @var Schedule|null $schedule */
        $schedule = $this->route('schedule');

        if (!$schedule) {
            return;
        }

        $exists = Schedule::query()
            ->whereDate('service_date', $this->input('service_date'))
            ->where('service_time', $this->input('service_time'))
            ->whereKeyNot($schedule->id)
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'service_time',
                'A schedule already exists for this service date and time.'
            );
        }
    }

    private function validateMinistryAssignments(Validator $validator): void
    {
        $assignments = $this->input('assignments', []);

        if (!is_array($assignments)) {
            return;
        }

        $activeMinistries = Ministry::query()
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (Ministry $ministry) => (string) $ministry->id);

        foreach ($assignments as $ministryId => $memberIds) {
            $ministry = $activeMinistries->get((string) $ministryId);

            if (!$ministry) {
                $validator->errors()->add(
                    "assignments.{$ministryId}",
                    'The selected ministry is invalid or inactive.'
                );

                continue;
            }

            if (
                !$ministry->allow_multiple_members
                && is_array($memberIds)
                && count($memberIds) > 1
            ) {
                $validator->errors()->add(
                    "assignments.{$ministryId}",
                    "{$ministry->name} only allows one member."
                );
            }
        }

        foreach ($activeMinistries as $ministry) {
            if (!array_key_exists((string) $ministry->id, $assignments)) {
                $validator->errors()->add(
                    "assignments.{$ministry->id}",
                    "Please select at least one member for {$ministry->name}."
                );
            }
        }
    }

    public function messages(): array
    {
        return [
            'service_date.required' => 'Please select the service date.',
            'service_date.date' => 'The service date must be a valid date.',
            'service_date.after_or_equal' => 'The service date cannot be in the past.',

            'service_time.required' => 'Please select the service time.',
            'service_time.date_format' => 'The service time must use a valid time format.',

            'assignments.required' => 'Please assign members to the ministries.',
            'assignments.array' => 'The ministry assignments are invalid.',

            'assignments.*.required' => 'Please select at least one member.',
            'assignments.*.array' => 'The ministry assignment must contain valid members.',
            'assignments.*.min' => 'Please select at least one member.',

            'assignments.*.*.required' => 'A selected member is required.',
            'assignments.*.*.integer' => 'The selected member is invalid.',
            'assignments.*.*.distinct' => 'The same member cannot be selected twice for one ministry.',
            'assignments.*.*.exists' => 'The selected member does not exist or is inactive.',
        ];
    }
}