<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use UnitEnum;

class ProfilePage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Meu Perfil';

    protected static ?string $title = 'Meu Perfil';

    protected static ?string $slug = 'profile';

    protected static ?int $navigationSort = 999;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::CONFIGURACOES;

    protected string $view = 'filament.admin.pages.profile-page';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    public string $current_password = '';

    #[Validate('nullable|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('nullable|string|min:8')]
    public string $password_confirmation = '';

    public bool $passwordChanged = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function form(Form $schema): Form
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Dados Pessoais')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->unique('users', 'email', ignoreRecord: true)
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function formPassword(Form $schema): Form
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Alterar Senha')
                    ->description('Deixe em branco se não quiser alterar')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Senha Atual')
                            ->password()
                            ->currentPassword(),
                        TextInput::make('password')
                            ->label('Nova Senha')
                            ->password()
                            ->minLength(8)
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar Nova Senha')
                            ->password(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar')
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        $user = auth()->user();

        if (! empty($this->password)) {
            if (! Hash::check($this->current_password, $user->password)) {
                ValidationException::withMessages([
                    'current_password' => 'A senha atual está incorreta.',
                ]);
            }

            $user->password = Hash::make($this->password);
            $this->passwordChanged = true;
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        \Filament\Notifications\Notification::make()
            ->title($this->passwordChanged ? 'Perfil e senha atualizados!' : 'Perfil atualizado!')
            ->success()
            ->send();
    }
}
