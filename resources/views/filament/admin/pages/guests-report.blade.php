<div class="fi-page">
    <div class="fi-header mb-6">
        <h1 class="fi-title text-2xl font-bold">{{ $this->title }}</h1>
    </div>

    <x-filament-panels::form>
        {{ $this->form }}
    </x-filament-panels::form>

    @if($this->reportData && $this->reportData->isNotEmpty())
        <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Responsável
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            🎟 PISTA
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            🎭 BACKSTAGE
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            Total
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            Entregues
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            Validados
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($this->reportData as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $row['promoter_name'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">
                                {{ $row['pista_total'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">
                                {{ $row['backstage_total'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">
                                {{ $row['total'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-success-600">
                                {{ $row['total'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-warning-600">
                                {{ $row['total_validated'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">
                            TOTAL GERAL
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">
                            {{ $this->totals['pista_total'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">
                            {{ $this->totals['backstage_total'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-900">
                            {{ $this->totals['grand_total'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-success-600">
                            {{ $this->totals['grand_total'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-warning-600">
                            {{ $this->totals['grand_validated'] }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="mt-6 text-center py-12 text-gray-500 bg-white rounded-xl shadow">
            Selecione um evento para ver o relatório.
        </div>
    @endif

    <div class="mt-4">
        {{ $this->registerHeaderActions }}
    </div>
</div>
