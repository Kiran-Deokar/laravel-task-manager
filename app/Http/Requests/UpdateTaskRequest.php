<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare / sanitize input
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:TODO,IN_PROGRESS,DONE,OVERDUE'],
            'title' => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:LOW,MEDIUM,HIGH'],
            'due_date' => ['sometimes', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid status value.',
            'priority.in' => 'Priority must be LOW, MEDIUM, or HIGH.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
        ];
    }
}