@props(['items'])

<div class="mt-4 border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden shadow-sm">
    <div class="bg-gray-50 dark:bg-white/5 px-4 py-2 border-b border-gray-200 dark:border-white/10">
        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex items-center gap-2">
            <x-heroicon-m-eye class="w-3 h-3" />
            Prévia da Importação
        </h4>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50/50 dark:bg-white/5 text-gray-500 font-bold border-b border-gray-200 dark:border-white/10">
                <tr>
                    <th class="px-4 py-2">Linha</th>
                    <th class="px-4 py-2">Nome</th>
                    <th class="px-4 py-2">Documento</th>
                    <th class="px-4 py-2">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-4 py-2 text-gray-400 font-mono">{{ $item['line'] }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $item['document'] ?: '-' }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-success-500/10 text-success-700 dark:text-success-400">
                                Válido
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                            O preview aparecerá conforme você digita...
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
