<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-6">
        {{-- Profile Form --}}
        <x-filament::section variant="bordered" class="overflow-hidden">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-user" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Dados Pessoais</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Atualize suas informações de perfil</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <x-filament::input.wrapper>
                    <x-filament::input-label for="name">Nome</x-filament::input-label>
                    <x-filament::input type="text" id="name" wire:model="name" />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input-label for="email">Email</x-filament::input-label>
                    <x-filament::input type="email" id="email" wire:model="email" />
                </x-filament::input.wrapper>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button type="button" color="primary" wire:click="save">
                        Salvar Alterações
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Password Section --}}
        <x-filament::section variant="bordered" class="overflow-hidden">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-warning-500/10">
                        <x-filament::icon icon="heroicon-o-lock-closed" class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Alterar Senha</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mantenha sua conta segura</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <x-filament::input.wrapper>
                    <x-filament::input-label for="current_password">Senha Atual</x-filament::input-label>
                    <x-filament::input type="password" id="current_password" wire:model="current_password" />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input-label for="password">Nova Senha</x-filament::input-label>
                    <x-filament::input type="password" id="password" wire:model="password" />
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input-label for="password_confirmation">Confirmar Nova Senha</x-filament::input-label>
                    <x-filament::input type="password" id="password_confirmation" wire:model="password_confirmation" />
                </x-filament::input.wrapper>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button type="button" color="warning" wire:click="save">
                        Atualizar Senha
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
