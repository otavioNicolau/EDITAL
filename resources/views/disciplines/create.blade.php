@extends('layouts.app')

@section('title', 'Nova Disciplina')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Nova Disciplina</h1>
                <a href="{{ route('disciplines.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    @php
                        $selectedBlockId = old('block_id', $selectedBlockId);
                        $selectedBlock = $blocks->firstWhere('id', $selectedBlockId);
                    @endphp
                    <form action="{{ route('disciplines.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="block_id" value="{{ $selectedBlockId }}">

                        <div class="row">
                            <div class="col-md-6">
                                @if($selectedBlock)
                                    <div class="mb-3">
                                        <label class="form-label">Bloco</label>
                                        <div class="form-control-plaintext">{{ $selectedBlock->name }}</div>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-3" role="alert">
                                        Selecione um bloco acessando a página do bloco e criando a disciplina a partir dela.
                                    </div>
                                @endif
                                @error('block_id')
                                    <div class="text-danger small mb-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Disciplina <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Código</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code') }}" placeholder="Ex: MAT, POR, DIR">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Descrição da disciplina...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Cor</label>
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color', '#007bff') }}">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativa</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('disciplines.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Disciplina
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
