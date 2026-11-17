<?php

namespace App\Http\Controllers;

use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountDeletionController extends Controller
{
    public function destroy(Request $request, AccountDeletionService $accountDeletion)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'string'],
        ]);

        try {
            $accountDeletion->validateDeletion($user, $data['password']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors(), 'deleteAccount');
        }

        // Log out before deleting the row so SessionGuard does not re-save the deleted model via remember_token.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $accountDeletion->performDeletion($user);

        return redirect()->route('login')->with('status', 'Your account has been permanently deleted.');
    }
}
