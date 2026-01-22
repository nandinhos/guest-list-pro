<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
use App\Services\ApprovalRequestService;
use App\Services\GuestService;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['promoter_id'] = auth()->id();
        $data['is_checked_in'] = false;

        $service = app(ApprovalRequestService::class);

        // 1. Verificar duplicidade universal (bloqueia se documento existir, avisa se nome existir)
        $duplicate = $service->checkForDuplicates(
            (int) $data['event_id'],
            $data['name'],
            $data['document'] ?? null
        );

        if ($duplicate) {
            if ($duplicate['level'] === 'error') {
                Notification::make()
                    ->title('Cadastro Bloqueado')
                    ->body($duplicate['message'])
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }

            // Aviso de homônimo
            Notification::make()
                ->title('Atenção: Possível Duplicidade')
                ->body($duplicate['message'])
                ->warning()
                ->send();
        }

        // 2. Verificar limites e permissões
        $guestService = new GuestService;
        $validation = $guestService->canRegisterGuest(
            auth()->user(),
            (int) $data['event_id'],
            (int) $data['sector_id']
        );

        if (! $validation['allowed']) {
            $this->createApprovalRequest($data, $validation['message']);

            $this->halt();
        }

        return $data;
    }

    /**
     * Cria uma solicitação de aprovação para inclusão de convidado.
     */
    protected function createApprovalRequest(array $data, string $reason): void
    {
        try {
            $service = app(ApprovalRequestService::class);

            // Verificar duplicidade (documento = bloqueante, nome = aviso)
            $duplicate = $service->checkForDuplicates(
                (int) $data['event_id'],
                $data['name'],
                $data['document'] ?? null
            );

            if ($duplicate) {
                if ($duplicate['level'] === 'error') {
                    // Documento duplicado = bloqueia completamente
                    Notification::make()
                        ->title('Cadastro Bloqueado')
                        ->body($duplicate['message'])
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                // Nome duplicado = aviso, mas continua a criação
                Notification::make()
                    ->title('Atenção: Possível Duplicidade')
                    ->body($duplicate['message'].' A solicitação será enviada para revisão.')
                    ->warning()
                    ->persistent()
                    ->send();
            }

            $request = $service->createGuestInclusionRequest(
                auth()->user(),
                (int) $data['event_id'],
                (int) $data['sector_id'],
                [
                    'name' => $data['name'],
                    'document' => $data['document'] ?? null,
                    'document_type' => $data['document_type'] ?? null,
                    'email' => $data['email'] ?? null,
                ],
                "Motivo do bloqueio: {$reason}"
            );

            Notification::make()
                ->title('Solicitação de Inclusão Enviada')
                ->body("Você atingiu o limite ou está fora do horário permitido. Uma solicitação #{$request->id} foi enviada para aprovação do administrador.")
                ->warning()
                ->persistent()
                ->actions([
                    NotificationAction::make('view')
                        ->label('Ver Solicitações')
                        ->url(route('filament.promoter.pages.my-requests')),
                ])
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao criar solicitação')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
