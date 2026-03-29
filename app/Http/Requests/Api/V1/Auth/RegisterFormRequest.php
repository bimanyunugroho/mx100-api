<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\RoleUserEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class RegisterFormRequest extends FormRequest
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
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'email:rfc,dns', 'max:100', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'role'         => ['required', 'string', Rule::in(RoleUserEnum::values())],
            'company_name' => ['nullable', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'min:11', 'max:13', 'regex:/^[0-9+]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string'   => 'Nama harus berupa teks.',
            'name.max'      => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string'   => 'Email harus berupa teks.',
            'email.email'    => 'Format email tidak sesuai standar RFC/DNS.',
            'email.max'      => 'Email maksimal 100 karakter.',
            'email.unique'   => 'Email sudah terdaftar.',
            'password.min'   => 'Password minimal 8 karakter.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role.required' => 'Peran pengguna wajib diisi.',
            'role.string'   => 'Peran pengguna harus berupa teks.',
            'role.in'       => 'Peran pengguna tidak valid.',
            'company_name.string' => 'Nama perusahaan harus berupa teks.',
            'company_name.max'    => 'Nama perusahaan maksimal 100 karakter.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.min'    => 'Nomor telepon minimal 11 karakter.',
            'phone.max'    => 'Nomor telepon maksimal 13 karakter.',
            'phone.regex'  => 'Nomor telepon hanya boleh berisi angka dan tanda +.',
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
