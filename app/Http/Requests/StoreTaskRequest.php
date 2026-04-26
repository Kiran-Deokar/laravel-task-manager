<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        // Basic check (you can replace with policy later)
        return $this->user() !== null;
    }

    /**
     * Prepare / sanitize input before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->title ? trim($this->title) : null,
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,project_id'],
            'assigned_to_user_id' => ['required', 'exists:users,user_id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Custom messages (optional but professional)
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Selected project does not exist.',

            'assigned_to_user_id.required' => 'Assignee is required.',
            'assigned_to_user_id.exists' => 'Selected user does not exist.',

            'title.required' => 'Task title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',

            'priority.in' => 'Priority must be LOW, MEDIUM, or HIGH.',

            'due_date.after_or_equal' => 'Due date must be today or a future date.',
        ];
    }
}