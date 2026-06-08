<?php

namespace App\Http\Requests\Admin;

use App\Enums\PartType;
use App\Enums\TestAccessType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTestAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isEnt = $this->input('type') === TestAccessType::Ent->value;
        $nusqaMode = $this->input('nusqa_mode', 'random');

        return [
            'type' => ['required', new Enum(TestAccessType::class)],
            'user_id' => ['nullable', 'required_without:group_id', 'exists:users,id'],
            'group_id' => ['nullable', 'required_without:user_id', 'exists:groups,id'],

            'student_chooses_subject' => ['boolean'],
            'attempts_limit' => ['integer', 'min:0', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],

            'elective' => ['nullable', 'array', 'max:2'],
            'elective.*.subject_id' => ['nullable', 'exists:subjects,id'],

            'nusqa_mode' => [Rule::requiredIf($isEnt), Rule::in(['random', 'student', 'specific'])],
            'nusqa_number' => [
                Rule::requiredIf($isEnt && $nusqaMode === 'specific'),
                'nullable',
                'integer',
                'min:1',
                'max:9999',
            ],

            'subject' => [Rule::requiredIf(! $isEnt), 'array'],
            'subject.subject_id' => [Rule::requiredIf(! $isEnt), 'nullable', 'exists:subjects,id'],
            'subject.part_type' => ['nullable', new Enum(PartType::class)],
            'subject.part_id' => ['nullable', 'exists:parts,id'],
            'subject.student_chooses_part' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'user_id.required_without' => 'Укажите студента или группу.',
            'group_id.required_without' => 'Укажите студента или группу.',
            'subject.subject_id.required_if' => 'Выберите предмет.',
            'nusqa_number.required_if' => 'Введите номер нұсқа.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (! empty($data['user_id'])) {
            $data['group_id'] = null;
        }

        $data['student_chooses_subject'] = (bool) ($data['student_chooses_subject'] ?? false);

        $nusqaMode = $data['nusqa_mode'] ?? 'random';
        $data['student_chooses_nusqa'] = $nusqaMode === 'student';
        $data['nusqa_number'] = $nusqaMode === 'specific' ? (int) ($data['nusqa_number'] ?? 0) : null;

        return $data;
    }
}
