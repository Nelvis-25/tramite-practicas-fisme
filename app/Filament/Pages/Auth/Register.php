<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rules\Password; // No es estrictamente necesaria si solo usas 'digits:10'
use Illuminate\Validation\ValidationException; // Asegúrate de que esta línea esté presente
use Illuminate\Database\QueryException; // <-- ¡NUEVA IMPORTACIÓN NECESARIA!
use Filament\Notifications\Notification; // <-- ¡NUEVA IMPORTACIÓN NECESARIA!

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): User
    {
        // Tu lógica de validación de contraseña vs. email (si la tienes aquí) iría aquí.
        // Si no, la validación se hace a nivel de campo en el método form().

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('Estudiante');

            // ✅ NOTIFICACIÓN DE ÉXITO (Opcional, Filament ya suele tenerla por defecto)
            Notification::make()
                ->title('¡Registro exitoso!')
                ->body('Cuenta creada con exito.')
                ->success()
                ->send();

            return $user;

        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1062 Duplicate entry')) {
                if (str_contains($e->getMessage(), 'users_email_unique')) {

                    // ✅ NOTIFICACIÓN DE ERROR POR DUPLICIDAD DE EMAIL
                    Notification::make()
                        ->title('Error de registro')
                        ->body('Este correo electrónico ya está registrado. Por favor, utiliza otro.')
                        ->danger() // Muestra la notificación en color rojo (peligro)
                        ->send();

                    // Importante: No lanzar ValidationException aquí si quieres solo la notificación.
                    // Si lanzas ValidationException, el mensaje aparecerá bajo el campo de email.
                    // Si solo quieres la notificación, no la lances.
                    // return null; // Puedes retornar null o una respuesta vacía si no quieres redirigir inmediatamente

                    // Para que Filament sepa que la operación no continuó, puedes devolver null o simplemente no hacer nada más.
                    // El flujo normal de Filament espera una excepción o un retorno del modelo creado.
                    // Si el formulario no debe enviar nada, el flujo estándar es relanzar la ValidationException.
                    // Si solo quieres la notificación, la mejor práctica es lanzar la ValidationException
                    // porque Filament está diseñado para manejar eso y refrescará el formulario con el error.
                    throw ValidationException::withMessages([
                        'email' => 'Este usuario ya exite.',
                    ]);

                }
            }
            // Si es otra excepción de base de datos no relacionada con el email duplicado, relanzarla
            throw $e;
        }
    }

    // Tu método form(Form $form): Form no necesita cambios para esto
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('filament-panels::pages/auth/register.form.email.label'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->rules(['regex:/^\d{10}@untrm\.edu\.pe$/i'])
                    ->validationMessages([
                        'regex' => 'Ingrese su correo institucional.',
                    ]),
                TextInput::make('password')
                    ->label(__('filament-panels::pages/auth/register.form.password.label'))
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->autocomplete('new-password')
                    ->rules(['digits:10'])
                    ->validationMessages([
                        'digits' => 'La contraseña debe ser su código institucional.',
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