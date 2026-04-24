<x-filament-panels::page>
    @if(count($this->backups) > 0)
        <div class="overflow-hidden rounded-xl bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Arquivo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Tamanho
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Criado em
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($this->backups as $backup)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $backup['filename'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $backup['size'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $backup['modified'] }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500 bg-white rounded-xl shadow">
            <x-heroicon-o-cloud-arrow-up class="w-12 h-12 mx-auto mb-4 text-gray-400" />
            <p>Nenhum backup encontrado.</p>
            <p class="text-sm mt-1">Clique em "Criar Backup" para fazer o primeiro backup.</p>
        </div>
    @endif
</x-filament-panels::page>
