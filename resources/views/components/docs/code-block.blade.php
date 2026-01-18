@props([
    'code' => '',
    'language' => 'blade',
    'title' => null,
    'showLineNumbers' => true,
])

@php
    $lines = explode("\n", rtrim($code));
    $lineCount = count($lines);
    
    // Função de syntax highlighting para Blade/HTML (só declara uma vez)
    if (!function_exists('highlightBladeSyntax')) {
        function highlightBladeSyntax($line) {
            $escaped = e($line);
            
            // Cores no estilo JetBrains Darcula
            $tagColor = '#CC7832';       // Laranja - tags HTML
            $attrColor = '#BABABA';      // Cinza claro - atributos
            $attrValueColor = '#6A8759'; // Verde - valores de atributos (strings)
            $bladeColor = '#9876AA';     // Roxo - diretivas Blade
            $varColor = '#FFC66D';       // Amarelo - variáveis
            $textColor = '#A9B7C6';      // Cinza azulado - texto normal
            
            // Padrões para highlighting
            $patterns = [
                // Diretivas Blade: @foreach, @if, etc.
                '/(@\w+)/' => '<span style="color: '.$bladeColor.';">$1</span>',
                
                // Variáveis Blade: {{ $var }}, {!! $var !!}
                '/(\{\{|\{\!\!)(\s*\$[\w\-\>]+\s*)(\}\}|\!\!\})/' => '<span style="color: '.$bladeColor.';">$1</span><span style="color: '.$varColor.';">$2</span><span style="color: '.$bladeColor.';">$3</span>',
                
                // Tags de abertura com atributos: <x-ui.button, <div, etc.
                '/(&lt;)(\/?)([a-zA-Z][\w\.\-]*)/' => '$1$2<span style="color: '.$tagColor.';">$3</span>',
                
                // Atributos com valores entre aspas: variant="primary"
                '/([\w\-\:]+)(=)(&quot;)([^&]*)(&quot;)/' => '<span style="color: '.$attrColor.';">$1</span>$2<span style="color: '.$attrValueColor.';">$3$4$5</span>',
                
                // Tags de fechamento: />
                '/(\/&gt;|&gt;)/' => '<span style="color: '.$tagColor.';">$1</span>',
                
                // Variáveis PHP: $variable
                '/(\$[\w\-\>]+)/' => '<span style="color: '.$varColor.';">$1</span>',
            ];
            
            foreach ($patterns as $pattern => $replacement) {
                $escaped = preg_replace($pattern, $replacement, $escaped);
            }
            
            return $escaped;
        }
    }
@endphp

<div class="code-block rounded-xl overflow-hidden shadow-lg border border-white/10">
    {{-- Header --}}
    <div class="code-header flex items-center justify-between px-4 py-3 bg-[#2b2b2b] border-b border-white/10">
        <div class="flex items-center gap-3">
            {{-- Window dots --}}
            <div class="flex gap-1.5">
                <span class="w-3 h-3 rounded-full bg-[#ff5f56]"></span>
                <span class="w-3 h-3 rounded-full bg-[#ffbd2e]"></span>
                <span class="w-3 h-3 rounded-full bg-[#27c93f]"></span>
            </div>
            @if($title)
                <span class="text-sm text-gray-400 font-mono">{{ $title }}</span>
            @endif
        </div>
        
        {{-- Copy button --}}
        <button
            type="button"
            x-data="{ copied: false }"
            x-on:click="
                navigator.clipboard.writeText($refs.code.textContent);
                copied = true;
                setTimeout(() => copied = false, 2000);
            "
            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-all duration-200"
        >
            <template x-if="!copied">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </template>
            <template x-if="copied">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </template>
            <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
        </button>
    </div>
    
    {{-- Code content - sempre tema escuro independente do tema global --}}
    <div class="code-content bg-[#1e1e1e] overflow-x-auto" style="color: #A9B7C6;">
        <pre class="py-4 text-sm font-mono leading-relaxed"><code x-ref="code" class="language-{{ $language }}">@foreach($lines as $index => $line)
<span class="code-line flex">@if($showLineNumbers)<span class="line-number select-none text-gray-600 text-right pr-4 pl-4 w-12 inline-block border-r border-white/5">{{ $index + 1 }}</span>@endif<span class="line-content pl-4 pr-4 flex-1">{!! highlightBladeSyntax($line) !!}</span></span>
@endforeach</code></pre>
    </div>
</div>
