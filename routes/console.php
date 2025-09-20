<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Exceptions\EstrategiaApiException;
use App\Services\EstrategiaApiService;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('estrategia:sync-cursos', function (EstrategiaApiService $estrategiaApiService) {
    $this->comment('Sincronizando cursos do Estratégia...');

    try {
        $concursos = Cache::remember('estrategia.concursos', now()->addMinutes(10), function () use ($estrategiaApiService) {
            return $estrategiaApiService->listarConcursos();
        });
    } catch (EstrategiaApiException $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    $totalConcursos = is_countable($concursos) ? count($concursos) : 0;
    $this->info("Total de concursos armazenados: {$totalConcursos}");
    $this->info('Cache salvo por 10 minutos com a chave estrategia.concursos.');

    return self::SUCCESS;
})->purpose('Sincroniza e armazena em cache a lista de cursos do Estratégia por 10 minutos');

