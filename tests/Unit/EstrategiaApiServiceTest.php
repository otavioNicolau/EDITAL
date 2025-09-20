<?php

namespace Tests\Unit;

use App\Exceptions\EstrategiaApiException;
use App\Services\EstrategiaApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EstrategiaApiServiceTest extends TestCase
{
    public function test_listar_concursos_lanca_excecao_em_erro(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([
                'message' => 'Token inválido',
            ], 401),
        ]);

        $this->expectException(EstrategiaApiException::class);
        $this->expectExceptionMessage('Token inválido');

        app(EstrategiaApiService::class)->listarConcursos();
    }

    public function test_token_with_bearer_prefix_is_sanitized(): void
    {
        config([
            'services.estrategia.token' => 'Bearer sanitized-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([], 200),
        ]);

        app(EstrategiaApiService::class)->listarConcursos();

        Http::assertSent(function ($request) {
            return $request->header('Authorization')[0] === 'Bearer sanitized-token';
        });
    }

    public function test_listar_concursos_trata_array_aninhado(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([
                'data' => [
                    'concursos' => [
                        ['id' => 1, 'titulo' => 'Concurso A'],
                        ['id' => 2, 'titulo' => 'Concurso B'],
                    ],
                    'cargos' => [],
                ],
            ], 200),
        ]);

        $concursos = app(EstrategiaApiService::class)->listarConcursos();

        $this->assertCount(2, $concursos);
        $this->assertSame('Concurso A', $concursos[0]['titulo']);
        $this->assertSame('concurso-a-1', $concursos[0]['slug']);
    }

    public function test_listar_concursos_normaliza_array_plano(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/curso' => Http::response([
                ['id' => 10, 'titulo' => 'Concurso Único'],
            ], 200),
        ]);

        $concursos = app(EstrategiaApiService::class)->listarConcursos();

        $this->assertCount(1, $concursos);
        $this->assertSame('Concurso Único', $concursos[0]['titulo']);
        $this->assertSame('concurso-unico-10', $concursos[0]['slug']);
    }

    public function test_obter_aula_normaliza_resposta(): void
    {
        config([
            'services.estrategia.token' => 'fake-token',
            'services.estrategia.base_uri' => 'https://api.estrategiaconcursos.com.br/api',
        ]);

        Http::fake([
            'https://api.estrategiaconcursos.com.br/api/aluno/aula/123' => Http::response([
                'data' => [
                    'id' => 123,
                    'titulo' => 'Aula de Teste',
                ],
            ], 200),
        ]);

        $aula = app(EstrategiaApiService::class)->obterAula(123);

        $this->assertSame(123, $aula['id']);
        $this->assertSame('Aula de Teste', $aula['titulo']);
    }
}
