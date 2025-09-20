<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CursoPageTest extends TestCase
{
    public function test_course_page_lists_videos_and_materials(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso/123' => Http::response([
                'data' => [
                    'id' => 123,
                    'titulo' => 'Curso Estratégia',
                    'aulas' => [
                        [
                            'id' => 999,
                            'titulo' => 'Aula de Introdução',
                            'conteudo' => 'Introdução ao curso',
                            'video' => [
                                'url_720p' => 'https://cdn.estrategia.example/videos/aula-introducao-720.mp4',
                                'url_480p' => 'https://cdn.estrategia.example/videos/aula-introducao-480.mp4',
                            ],
                            'pdf' => 'https://cdn.estrategia.example/pdf/aula-introducao.pdf',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->get('/curso/123')
            ->assertStatus(200)
            ->assertSee('Aula de Introdução', false)
            ->assertSee('Ver conteúdo', false);

        $slug = Str::slug('Aula de Introdução-1') . '-999';

        $this->get("/curso/123/aula/{$slug}")
            ->assertStatus(200)
            ->assertSee('Aula de Introdução', false)
            ->assertSee('Baixar vídeo (720P)', false)
            ->assertSee('Abrir no VLC', false)
            ->assertSee('Copiar link para VLC', false)
            ->assertSee('Baixar playlist (.m3u)', false)
            ->assertSee('PDF', false);
    }

    public function test_course_page_handles_lessons_without_video(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso/456' => Http::response([
                'data' => [
                    'id' => 456,
                    'titulo' => 'Curso Sem Vídeo',
                    'aulas' => [
                        [
                            'titulo' => 'Aula apenas textual',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->get('/curso/456')
            ->assertStatus(200)
            ->assertSee('Aula apenas textual', false)
            ->assertSee('Ver conteúdo', false);

        $slug = Str::slug('Aula apenas textual-1') . '-1';

        $this->get("/curso/456/aula/{$slug}")
            ->assertStatus(200)
            ->assertSee('Nenhum conteúdo disponível', false);
    }
}
