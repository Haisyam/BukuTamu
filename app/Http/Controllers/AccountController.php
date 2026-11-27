<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Show the admin account settings page (email & password).
     */
    public function edit(Request $request): View
    {
        return view('admin.account.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated admin's email address.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validateWithBag('updateEmail', [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password_email' => ['required', 'string', 'current_password'],
        ], [
            'email.required' => 'Alamat email baru wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'current_password_email.required' => 'Kata sandi saat ini wajib diisi untuk konfirmasi.',
            'current_password_email.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);

        $user->update([
            'email' => strtolower(trim($validated['email'])),
        ]);

        return back()->with('success', 'Alamat email akun berhasil diperbarui.');
    }

    /**
     * Update the authenticated admin's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->regenerate();

        return back()->with('success', 'Kata sandi akun berhasil diperbarui.');
    }
}
