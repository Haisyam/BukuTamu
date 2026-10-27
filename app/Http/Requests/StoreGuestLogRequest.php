<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestLogRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:25'],
            'institution' => ['required', 'string', 'max:150'],
            'division_id' => ['required', 'exists:divisions,id'],
            'person_to_meet' => ['nullable', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:2000'],
            'photo' => ['required', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator errors in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap tamu wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 150 karakter.',
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'phone.max' => 'Nomor HP maksimal 25 karakter.',
            'institution.required' => 'Asal instansi / alamat / organisasi wajib diisi.',
            'institution.max' => 'Asal instansi maksimal 150 karakter.',
            'division_id.required' => 'Silakan pilih bagian atau bidang tujuan kunjungan.',
            'division_id.exists' => 'Bagian / bidang tujuan yang dipilih tidak valid.',
            'person_to_meet.max' => 'Nama staf / pejabat yang ditemui maksimal 150 karakter.',
            'purpose.required' => 'Keperluan kunjungan wajib diisi.',
            'purpose.max' => 'Keperluan kunjungan maksimal 2000 karakter.',
            'photo.required' => 'Foto webcam tamu wajib diambil sebelum menyimpan data.',
        ];
    }
}
