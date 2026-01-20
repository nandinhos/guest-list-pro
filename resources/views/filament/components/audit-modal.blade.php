<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4">
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <span class="block text-xs text-gray-500 uppercase">Responsável</span>
            <span class="font-medium text-base">{{ $record->causer?->name ?? 'Sistema' }}</span>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <span class="block text-xs text-gray-500 uppercase">Data e Hora</span>
            <span class="font-medium text-base">{{ $record->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <span class="block text-xs text-gray-500 uppercase">Entidade</span>
            <span class="font-medium text-base">{{ class_basename($record->subject_type) }} #{{ $record->subject_id }}</span>
        </div>
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <span class="block text-xs text-gray-500 uppercase">Ação</span>
            <span class="font-medium text-base">{{ ucfirst($record->event) }}</span>
        </div>
    </div>

    @if(isset($record->properties['attributes']) || isset($record->properties['old']))
    <div class="border rounded-lg overflow-hidden border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-2 w-1/3">Campo</th>
                    <th class="px-4 py-2 w-1/3 text-red-600 dark:text-red-400">Anterior</th>
                    <th class="px-4 py-2 w-1/3 text-green-600 dark:text-green-400">Novo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @php
                    $attributes = $record->properties['attributes'] ?? [];
                    $old = $record->properties['old'] ?? [];
                    $keys = array_unique(array_merge(array_keys($attributes), array_keys($old)));
                @endphp

                @foreach($keys as $key)
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="px-4 py-2 font-mono text-gray-600 dark:text-gray-400">{{ $key }}</td>
                        <td class="px-4 py-2 break-all">
                            @if(isset($old[$key]))
                                <span class="bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 px-1 rounded">
                                    {{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 break-all">
                            @if(isset($attributes[$key]))
                                <span class="bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300 px-1 rounded">
                                    {{ is_array($attributes[$key]) ? json_encode($attributes[$key]) : $attributes[$key] }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
