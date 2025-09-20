@props(['aula' => [], 'moduloNome' => null])

@php
    $titulo = data_get($aula, 'titulo')
        ?? data_get($aula, 'nome')
        ?? data_get($aula, 'name')
        ?? 'Aula';
    $descricao = data_get($aula, 'descricao') ?? data_get($aula, 'description');
    $duracao = data_get($aula, 'duracao') ?? data_get($aula, 'duration');
    $numero = data_get($aula, 'numero') ?? data_get($aula, 'order') ?? data_get($aula, 'position');
    $updatedAt = data_get($aula, 'atualizado_em') ?? data_get($aula, 'updated_at');

    $finder = function (array $paths) use ($aula) {
        foreach ($paths as $path) {
            $value = data_get($aula, $path);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    };

    $linksExtras = [
        'PDF' => $finder(['pdf']),
        'PDF (Grifado)' => $finder(['pdf_grifado']),
        'PDF (Simplificado)' => $finder(['pdf_simplificado']),
        'Áudio' => $finder(['audio.url', 'audio_url', 'links.audio', 'links.audio_url', 'arquivos.audio']),
        'Mapa mental' => $finder(['mapa_mental.url', 'mapa_mental', 'mapa_mental_url', 'links.mapa_mental', 'arquivos.mapa_mental']),
        'Resumo' => $finder(['resumo.url', 'resumo', 'resumo_url', 'links.resumo', 'arquivos.resumo']),
        'Slide' => $finder(['slide.url', 'slide', 'slide_url', 'links.slide', 'slides.url', 'arquivos.slide']),
        'Transmissão' => $finder(['livestream_link', 'livestream', 'links.livestream']),
    ];

    $linksExtras = array_filter($linksExtras);

    try {
        $updatedLabel = $updatedAt ? \Illuminate\Support\Carbon::parse($updatedAt)->format('d/m/Y H:i') : null;
    } catch (\Exception $exception) {
        $updatedLabel = $updatedAt;
    }
@endphp

<div class="card mb-4 shadow-sm border-0 aula-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h5 class="card-title fw-semibold mb-1">{{ $titulo }}</h5>
                @if($moduloNome)
                    <span class="badge bg-primary-subtle text-primary-emphasis">{{ $moduloNome }}</span>
                @endif
            </div>

            <div class="d-flex flex-column text-end small text-muted">
                @if($numero)
                    <span>Aula {{ $numero }}</span>
                @endif
                @if($duracao)
                    <span>Duração: {{ $duracao }}</span>
                @endif
                @if($updatedLabel)
                    <span>Atualizado: {{ $updatedLabel }}</span>
                @endif
            </div>
        </div>

        @if($descricao)
            <p class="mt-3 text-muted">{{ strip_tags($descricao) }}</p>
        @endif

        <x-video-player :aula="$aula" :titulo="$titulo" />

        @if(!empty($linksExtras))
            <div class="mt-3">
                <h6 class="fw-semibold small text-uppercase text-muted">Materiais complementares</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($linksExtras as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noreferrer" class="btn btn-outline-secondary btn-sm">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
