<?php

namespace App\Http\Requests\Api\V1\Job;

use App\Enums\StatusJobEnum;
use App\Enums\TypeJobEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ApplyJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cv_file'      => [
                'required',
                'file',
                'max:5120',
                'mimes:pdf',
            ],
            'cover_letter' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cv_file.required' => 'File CV wajib diunggah.',
            'cv_file.file'     => 'CV harus berupa file yang valid.',
            'cv_file.max'      => 'Ukuran CV maksimal 5 MB.',
            'cv_file.mimes'    => 'CV harus dalam format PDF.',
            'cover_letter.string' => 'Surat lamaran harus berupa teks.',
            'cover_letter.max'    => 'Surat lamaran maksimal 500 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
