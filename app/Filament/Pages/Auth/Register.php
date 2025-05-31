<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('Estudiante');

            Notification::make()
                ->title('¡Registro exitoso!')
                ->body('Tu cuenta ha sido creada con éxito y se te ha asignado el rol de Estudiante.')
                ->success()
                ->send();

            return $user;

        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1062 Duplicate entry')) {
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    Notification::make()
                        ->title('Error de registro')
                        ->body('Este correo electrónico ya está registrado. Por favor, utiliza otro.')
                        ->danger()
                        ->send();

                    throw ValidationException::withMessages([
                        'email' => 'Este correo electrónico ya existe.',
                    ]);
                }
                elseif (str_contains($e->getMessage(), 'users_name_unique')) {
                    Notification::make()
                        ->title('Error de registro')
                        ->body('Este nombre de usuario ya está en uso. Por favor, elige otro.')
                        ->danger()
                        ->send();

                    throw ValidationException::withMessages([
                        'name' => 'Este nombre de usuario ya está en uso.',
                    ]);
                }
            }
            throw $e;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->required()
                    ->maxLength(255)
                    ->rules(['unique:users,name'])
                    ->validationMessages([
                        'unique' => 'Este nombre de usuario ya está en uso. Por favor, elige otro.',
                    ]),

                TextInput::make('email')
                    ->label(__('filament-panels::pages/auth/register.form.email.label'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->rules(['regex:/^\d{10}@untrm\.edu\.pe$/i', 'unique:users,email'])
                    ->validationMessages([
                        'regex' => 'Ingrese su correo institucional con 10 dígitos seguido de @untrm.edu.pe (ej. 9635632365@untrm.edu.pe).',
                        'unique' => 'Este correo electrónico ya está registrado. Por favor, utiliza otro.',
                    ]),

                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->autocomplete('new-password')
                    ->rules(['digits:10'])
                    ->validationMessages([
                        'digits' => 'La contraseña debe ser su código institucional (exactamente 10 dígitos numéricos).',
                    ]),

                TextInput::make('password_confirmation')
                    ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->same('password')
                    ->autocomplete('new-password'),
            ]);
    }
}
