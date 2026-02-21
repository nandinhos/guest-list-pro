@props(['items'])

<div class="mt-8 overflow-hidden rounded-2xl border border-white/5 bg-white/5">
    <div class="px-6 py-4 border-b border-white/5 bg-white/5">
        <h4 class="text-sm font-bold text-surface-primary flex items-center gap-2">
            <x-heroicon-o-eye class="w-4 h-4 text-[var(--color-brand-admin-500)]" />
            Preview da Importação (primeiros 20 itens)
        </h4>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-white/5 text-surface-secondary uppercase text-[10px] font-bold tracking-wider">
                <tr>
                    <th class="px-6 py-3">Linha</th>
                    <th class="px-6 py-3">Nome</th>
                    <th class="px-6 py-3">Documento</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($items as $item)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4 text-surface-secondary font-mono text-xs">{{ $item['line'] }}</td>
                        <td class="px-6 py-4 text-surface-primary font-medium group-hover:text-[var(--color-brand-admin-500)] transition-colors">
                            {{ $item['name'] }}
                        </td>
                        <td class="px-6 py-4 text-surface-secondary">{{ $item['document'] ?: '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-success-500/10 text-success-700">
                                Válido
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-surface-secondary italic">
                            Aguardando conteúdo para processar o preview...
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
