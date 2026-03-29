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

class StoreJobRequest extends FormRequest
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
            'title'        => ['required', 'string', 'max:100'],
            'description'  => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'salary_range' => ['nullable', 'string', 'max:100'],
            'location'     => ['nullable', 'string', 'max:150'],
            'type'         => ['required', 'string', Rule::in(TypeJobEnum::values())],
            'status'       => ['required', 'string', Rule::in(StatusJobEnum::values())]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul lowongan pekerjaan wajib diisi.',
            'title.string'   => 'Judul lowongan pekerjaan harus berupa teks.',
            'title.max'      => 'Judul lowongan pekerjaan maksimal 100 karakter.',
            'description.required' => 'Deskripsi lowongan pekerjaan wajib diisi.',
            'description.string'   => 'Deskripsi lowongan pekerjaan harus berupa teks.',
            'requirements.string' => 'Persyaratan harus berupa teks.',
            'salary_range.string' => 'Rentang gaji harus berupa teks.',
            'salary_range.max'    => 'Rentang gaji maksimal 100 karakter.',
            'location.string' => 'Lokasi harus berupa teks.',
            'location.max'    => 'Lokasi maksimal 150 karakter.',
            'type.required' => 'Tipe lowongan pekerjaan wajib diisi.',
            'type.string'   => 'Tipe lowongan pekerjaan harus berupa teks.',
            'type.in'       => 'Tipe lowongan pekerjaan tidak valid.',
            'status.required' => 'Status lowongan pekerjaan wajib diisi.',
            'status.string'   => 'Status lowongan pekerjaan harus berupa teks.',
            'status.in'       => 'Status lowongan pekerjaan tidak valid.',
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
