@props([
    'concursos' => [],
    'id' => 'concursosList',
])

@if(empty($concursos))
    <div class="alert alert-warning">Nenhum concurso encontrado no momento.</div>
@else
    <div class="row g-3" id="{{ $id }}">
        @foreach($concursos as $index => $concurso)
            @php
                $iteration = $loop->iteration;
                $titulo = data_get($concurso, 'titulo')
                    ?? data_get($concurso, 'nome')
                    ?? data_get($concurso, 'name')
                    ?? 'Concurso ' . $iteration;
                $descricao = data_get($concurso, 'descricao') ?? data_get($concurso, 'description');
                $cursos = data_get($concurso, 'cursos', data_get($concurso, 'courses', []));
                $totalCursos = is_countable($cursos) ? count($cursos) : 0;
                $modalidadesCollection = collect($cursos)->pluck('modalidade')->filter()->unique()->values();
                $modalidades = $modalidadesCollection->take(3);
                $modalidadesExtrasTotal = max(0, $modalidadesCollection->count() - $modalidades->count());
                $isBonus = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($titulo), ['bônus', 'bonus']);
                $iconClass = $isBonus ? 'fas fa-star text-warning' : 'fas fa-landmark text-primary';
                $slug = data_get($concurso, 'slug');
                $link = $slug ? route('concursos.show', ['slug' => $slug]) : null;
            @endphp

            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <span class="fs-4"><i class="{{ $iconClass }}"></i></span>
                            <div class="flex-grow-1">
                                <h2 class="h5 fw-semibold mb-1">{{ $titulo }}</h2>
                                @if($descricao)
                                    <p class="mb-0 text-muted small">{{ $descricao }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge rounded-pill text-bg-light border border-light text-secondary">
                                <i class="fas fa-layer-group me-1"></i>{{ $totalCursos }} {{ \Illuminate\Support\Str::plural('curso', $totalCursos) }}
                            </span>
                            @foreach($modalidades as $modalidade)
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                    <i class="fas fa-tag me-1"></i>{{ $modalidade }}
                                </span>
                            @endforeach
                            @if($modalidadesExtrasTotal > 0)
                                <span class="badge rounded-pill text-bg-light border border-light text-secondary">+{{ $modalidadesExtrasTotal }}</span>
                            @endif
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Visualize os cursos disponíveis</span>
                            @if($link)
                                <a href="{{ $link }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-chevron-right me-1"></i>Ver cursos
                                </a>
                            @else
                                <span class="btn btn-outline-secondary btn-sm disabled">Indisponível</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
