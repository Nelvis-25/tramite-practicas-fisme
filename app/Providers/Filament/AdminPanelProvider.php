<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Filament\Navigation\UserMenuItem;
use Filament\Pages\Auth\EditProfile;
use Filament\Pages\Tenancy\RegisterTenant;

use Spatie\Permission\Models\Role;
use App\Filament\Pages\Auth\Register;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\Css;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
        
            ->font('Roboto Flex')
            ->default()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->id('admin')
            ->navigationGroups([
            NavigationGroup::make()->label('Registro académico'),     
            NavigationGroup::make()->label('Plan de Prácticas'),       
            NavigationGroup::make()->label('Informe de Prácticas'),    
            NavigationGroup::make()->label('Gestión de Accesos'),       
            ])
            ->profile(EditProfile::class)
            ->registration(Register::class)
            ->path('admin')
            ->databaseNotifications()
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Blue,

            ])
            
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->brandLogo(asset('img/untrmazul.png'))
            ->brandLogoHeight('3rem')
            
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                \App\Filament\Widgets\PracticasTotal::class,
                \App\Filament\Widgets\PracticasPastel::class,
                \App\Filament\Widgets\Asesorlista::class,

               
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->sidebarCollapsibleOnDesktop()
            ->authMiddleware([
                Authenticate::class,
                
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 1,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ]);

            
            
            
    }
     
    
}