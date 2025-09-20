<?php

namespace App\Services;

use App\Exceptions\EstrategiaApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EstrategiaApiService
{
    private string $baseUri;
    private ?string $token;

    public function __construct()
    {
        $config = config('services.estrategia', []);

        $this->baseUri = rtrim($config['base_uri'] ?? 'https://api.estrategiaconcursos.com.br/api', '/');
        $this->token = $config['token'] ?? null;
    }

    /**
     * @return array<int, mixed>
     * @throws EstrategiaApiException
     */
    public function listarConcursos(): array
    {
        try {
            $response = $this->request()->get('/aluno/curso');
        } catch (ConnectionException $exception) {
            throw EstrategiaApiException::fromMessage('Erro de comunicação com o Estratégia Concursos. Verifique sua conexão e tente novamente.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw EstrategiaApiException::fromMessage('Não foi possível interpretar os concursos retornados pelo Estratégia.');
        }

        $concursos = $this->normalizeConcursosPayload($data);

        return array_map(function ($concurso) {
            if (!is_array($concurso)) {
                return $concurso;
            }

            $id = $concurso['id'] ?? null;

            if ($id === null) {
                return $concurso;
            }

            $titulo = $concurso['titulo']
                ?? $concurso['nome']
                ?? $concurso['name']
                ?? (string) $id;

            $slug = \Illuminate\Support\Str::slug($titulo);

            return $concurso + [
                'slug' => $slug . '-' . $id,
            ];
        }, $concursos);
    }

    /**
     * @throws EstrategiaApiException
     */
    public function obterCurso(int $id): array
    {
        try {
            $response = $this->request()->get("/aluno/curso/{$id}");
        } catch (ConnectionException $exception) {
            throw EstrategiaApiException::fromMessage('Erro de comunicação com o Estratégia Concursos. Verifique sua conexão e tente novamente.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw EstrategiaApiException::fromMessage('Não foi possível interpretar os detalhes do curso retornados pelo Estratégia.');
        }

        if (Arr::isAssoc($data) && isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    /**
     * @throws EstrategiaApiException
     */
    public function obterAula(int $id): array
    {
        try {
            $response = $this->request()->get("/aluno/aula/{$id}");
        } catch (ConnectionException $exception) {
            throw EstrategiaApiException::fromMessage('Erro de comunicação com o Estratégia Concursos. Verifique sua conexão e tente novamente.');
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw EstrategiaApiException::fromMessage('Não foi possível interpretar os detalhes da aula retornados pelo Estratégia.');
        }

        if (Arr::isAssoc($data) && isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    /**
     * @throws EstrategiaApiException
     */
    private function request(): PendingRequest
    {
        if (empty($this->token)) {
            throw EstrategiaApiException::fromMessage('Configure o token de acesso do Estratégia antes de consultar os cursos.');
        }

        return Http::baseUrl($this->baseUri)
            ->withToken($this->sanitizeToken($this->token))
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 200)
            ->throw(function (Response $response, ?RequestException $exception) {
                $status = $response->status();
                $message = $this->resolveErrorMessage($response);

                Log::warning('Erro ao chamar API do Estratégia', [
                    'status' => $status,
                    'message' => $exception?->getMessage(),
                    'body' => $response->json(),
                ]);

                throw EstrategiaApiException::fromMessage($message);
            });
    }

    private function sanitizeToken(string $token): string
    {
        return preg_replace('/^Bearer\s+/i', '', $token) ?: $token;
    }

    private function resolveErrorMessage(?Response $response): string
    {
        if ($response === null) {
            return 'Não foi possível se conectar ao Estratégia Concursos. Tente novamente em instantes.';
        }

        $body = $response->json();

        if (is_array($body)) {
            $possibleMessage = $body['message']
                ?? $body['error']
                ?? ($body['errors'][0] ?? null)
                ?? null;

            if (is_string($possibleMessage) && $possibleMessage !== '') {
                return $possibleMessage;
            }
        }

        return 'Não foi possível completar a solicitação junto ao Estratégia Concursos. Tente novamente em instantes.';
    }

    /**
     * @param array<mixed> $payload
     * @return array<int, mixed>
     */
    private function normalizeConcursosPayload(array $payload): array
    {
        if (isset($payload['concursos']) && is_array($payload['concursos'])) {
            return array_values($payload['concursos']);
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $this->normalizeConcursosPayload($payload['data']);
        }

        if (Arr::isAssoc($payload)) {
            return [$payload];
        }

        return array_values($payload);
    }
}
