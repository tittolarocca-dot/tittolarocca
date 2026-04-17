<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return inertia('Auth/Register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:80'],
            'email'    => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'in:member,inserent'],
            'agb'      => ['accepted'],
            'age'      => ['accepted'],
        ], [
            'name.required'      => 'Bitte gib deinen Namen ein.',
            'email.required'     => 'Bitte gib deine E-Mail-Adresse ein.',
            'email.unique'       => 'Diese E-Mail-Adresse ist bereits registriert.',
            'password.required'  => 'Bitte wähle ein Passwort.',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein.',
            'password.min'       => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'role.required'      => 'Bitte wähle eine Rolle.',
            'agb.accepted'       => 'Du musst die AGB akzeptieren.',
            'age.accepted'       => 'Du musst bestätigen, dass du 18 Jahre oder älter bist.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'status'   => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($user->role === 'inserent') {
            return redirect()->route('inserat.profile.edit')
                ->with('success', 'Willkommen! Erstelle jetzt dein Inserat.');
        }

        return redirect()->route('home')
            ->with('success', 'Willkommen! Dein Konto wurde erfolgreich erstellt.');
    }
}
