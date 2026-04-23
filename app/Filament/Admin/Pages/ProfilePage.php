<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('data')
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

                Section::make('Alterar Senha')
                    ->description('Deixe em branco se não quiser alterar')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Senha Atual')
                            ->password(),
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
        $data = $this->form->getState();

        $user = auth()->user();

        if (! empty($data['password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                ValidationException::withMessages([
                    'current_password' => 'A senha atual está incorreta.',
                ]);
                return;
            }

            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Perfil atualizado com sucesso!')
            ->success()
            ->send();
    }
}
