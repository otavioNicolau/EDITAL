<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConcursosPageTest extends TestCase
{
    public function test_home_page_lists_concursos(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'titulo' => 'Concurso Polícia Federal',
                        'cursos' => [
                            [
                                'id' => 101,
                                'titulo' => 'Curso Intensivo',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Concurso Polícia Federal', false)
            ->assertSee(route('concursos.show', ['slug' => 'concurso-policia-federal-1']), false)
            ->assertDontSee('Curso Intensivo', false);
    }

    public function test_concurso_show_page_displays_courses(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([
                'data' => [
                    'concursos' => [
                        [
                            'id' => 1,
                            'titulo' => 'Concurso Polícia Federal',
                            'cursos' => [
                                [
                                    'id' => 101,
                                    'titulo' => 'Curso Intensivo',
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->get('/concursos/concurso-policia-federal-1')
            ->assertStatus(200)
            ->assertSee('Concurso Polícia Federal', false)
            ->assertSee('Curso Intensivo', false);
    }
}
