@props(['videos' => []])

@php
    $videosCollection = collect($videos)
        ->filter(fn ($video) => is_array($video) && !empty($video['resolucoes']))
        ->values();
@endphp

@if($videosCollection->isEmpty())
    <p class="text-muted">Nenhum vídeo disponível.</p>
@else
    @php
        $normalized = $videosCollection->map(function ($video, $index) {
            $resolucoes = collect($video['resolucoes'] ?? [])
                ->filter(fn ($item) => is_array($item) && !empty($item['url']))
                ->map(fn ($item) => [
                    'label' => strtoupper($item['label'] ?? 'LINK'),
                    'url' => $item['url'],
                ])
                ->values();

            return [
                'title' => $video['titulo'] ?? $video['nome'] ?? ('Vídeo ' . ($index + 1)),
                'resolutions' => $resolucoes->all(),
            ];
        })->filter(fn ($video) => !empty($video['resolutions']))->values();

        $activeVideo = $normalized->first();
        $activeResolution = $activeVideo['resolutions'][0] ?? null;
        $videosJson = $normalized->toJson(JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
    @endphp

    <div class="video-player mb-4" data-videos='{{ $videosJson }}' data-video-index="0" data-video-url="{{ $activeResolution['url'] ?? '' }}" data-titulo="{{ $activeVideo['title'] }}">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="ratio ratio-16x9 mb-3">
                    <video controls preload="metadata" src="{{ $activeResolution['url'] ?? '' }}" class="rounded border w-100" playsinline>
                        Seu navegador não suporta o elemento de vídeo.
                    </video>
                </div>

                <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch gap-2 mb-3" role="toolbar">
                    <a href="{{ $activeResolution ? 'vlc://' . $activeResolution['url'] : '#' }}" target="_blank" rel="noreferrer noopener" class="btn btn-outline-primary btn-sm w-100 w-sm-auto" data-action="abrir-vlc">Abrir no VLC</a>
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto" data-action="copiar-vlc">Copiar link para VLC</button>
                    <button type="button" class="btn btn-outline-success btn-sm w-100 w-sm-auto" data-action="baixar-playlist">Baixar playlist (.m3u)</button>
                    <a href="{{ $activeResolution['url'] ?? '#' }}" class="btn btn-outline-info btn-sm w-100 w-sm-auto" download data-download-current target="_blank" rel="noreferrer noopener">
                        Baixar vídeo ({{ $activeResolution['label'] ?? 'LINK' }})
                    </a>
                </div>

                @if($normalized->count() > 1)
                    <div class="d-flex flex-wrap gap-2 mb-3" role="group" data-video-switch>
                        @foreach($normalized as $index => $video)
                            <button
                                type="button"
                                class="btn btn-sm {{ $index === 0 ? 'btn-primary text-white' : 'btn-outline-primary' }}"
                                data-video-index="{{ $index }}"
                            >
                                {{ $video['title'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2" role="group" data-resolution-switch>
                    @foreach($activeVideo['resolutions'] as $index => $resolucao)
                        <button type="button" class="btn btn-sm {{ $index === 0 ? 'btn-primary text-white' : 'btn-outline-primary' }}" data-quality="{{ $resolucao['label'] }}" data-url="{{ $resolucao['url'] }}">
                            {{ $resolucao['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="alert alert-info mt-3 d-none feedback-message" role="alert"></div>
            </div>
        </div>

        @if($normalized->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h2 class="h6 mb-0">Playlist da aula para VLC</h2>
                    <button type="button" class="btn btn-outline-success btn-sm" data-action="baixar-playlist-completa">
                        Baixar playlist completa (.m3u)
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Abra ou copie os links abaixo para assistir a aula no VLC media player com todos os vídeos disponíveis.</p>
                    <ol class="list-group list-group-numbered list-group-flush">
                        @foreach($normalized as $video)
                            @php($primaryResolution = $video['resolutions'][0] ?? null)
                            <li class="list-group-item px-0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <span class="fw-semibold">{{ $video['title'] }}</span>
                                        @if(count($video['resolutions']) > 1)
                                            <span class="badge text-bg-light ms-2">{{ count($video['resolutions']) }} resoluções</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($primaryResolution)
                                            <a href="{{ 'vlc://' . $primaryResolution['url'] }}" target="_blank" rel="noreferrer noopener" class="btn btn-outline-primary btn-sm" data-action="abrir-vlc-item" data-url="{{ $primaryResolution['url'] }}">
                                                Abrir no VLC ({{ $primaryResolution['label'] }})
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="copiar-vlc-item" data-url="{{ $primaryResolution['url'] }}">
                                                Copiar link
                                            </button>
                                        @else
                                            <span class="text-muted small">Link indisponível</span>
                                        @endif
                                    </div>
                                </div>

                                @if(count($video['resolutions']) > 1)
                                    <div class="mt-3">
                                        <span class="text-muted small d-block mb-2">Outras resoluções:</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($video['resolutions'] as $resolution)
                                                <a href="{{ 'vlc://' . $resolution['url'] }}" target="_blank" rel="noreferrer noopener" class="btn btn-outline-primary btn-sm" data-action="abrir-vlc-item" data-url="{{ $resolution['url'] }}">
                                                    {{ $resolution['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif
    </div>
@endif
