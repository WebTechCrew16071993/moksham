<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?string $navigationGroup = 'Setting';

    protected static ?int $navigationSort = 51;

    protected static string $view = 'filament.pages.settings';

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->profileForm->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'profile_photo' => $user->profile_photo,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'profileForm' => $this->makeForm()
                ->schema([
                    Section::make('My Profile')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->rules(['email', 'required', 'max:255', 'unique:users,email,' . Auth::id()])
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->required()
                                ->tel()
                                ->maxLength(20),
                            FileUpload::make('profile_photo')
                                ->label('Profile Photo')
                                ->required()
                                ->image()
                                ->disk('public')
                                ->directory('profiles')
                                ->visibility('public')
                                ->imageEditor()
                                ->imagePreviewHeight('150')
                                ->openable(),
                        ])->columns(2),
                ])
                ->statePath('profileData'),

            'passwordForm' => $this->makeForm()
                ->schema([
                    Section::make('Change Password')
                        ->extraAttributes(['class' => 'mt-6 md:mt-8'])
                        ->schema([
                            Forms\Components\TextInput::make('current_password')
                                ->password()
                                ->label('Current Password')
                                ->required()
                                ->rules(['required', 'current_password'])
                                ->revealable(),
                            Forms\Components\TextInput::make('new_password')
                                ->password()
                                ->label('New Password')
                                ->required()
                                ->rules(['required', PasswordRule::defaults()])
                                ->revealable(),
                            Forms\Components\TextInput::make('new_password_confirmation')
                                ->password()
                                ->label('Confirm New Password')
                                ->required()
                                ->same('new_password')
                                ->rules(['required'])
                                ->revealable(),
                        ])->columns(2),
                ])
                ->statePath('passwordData'),
        ];
    }

    public function saveProfile(): void
    {
        $user = auth()->user();
        $data = $this->profileForm->getState();

        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'profile_photo' => $data['profile_photo'],
        ])->save();

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }

    public function changePassword(): void
    {
        $user = auth()->user();
        $data = $this->passwordForm->getState();

        // Validate rules again to be safe
        $this->validate([
            'passwordData.current_password' => ['required', 'current_password'],
            'passwordData.new_password' => ['required', PasswordRule::defaults(), 'same:passwordData.new_password_confirmation'],
            'passwordData.new_password_confirmation' => ['required'],
        ], [], [
            'passwordData.current_password' => 'current password',
            'passwordData.new_password' => 'new password',
            'passwordData.new_password_confirmation' => 'new password confirmation',
        ]);

        $user->password = Hash::make($data['new_password']);
        $user->save();

        // Clear password fields in the form
        $this->passwordForm->fill([
            'current_password' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        Notification::make()
            ->title('Password changed')
            ->success()
            ->send();
    }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return false; // hidden from navigation as requested
    // }
}
