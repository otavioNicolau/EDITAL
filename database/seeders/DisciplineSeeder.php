<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🎯 Iniciando seed de disciplinas...\n";
        
        // Limpar registros existentes
        Discipline::truncate();
        
        // Primeiro, vamos criar os blocos necessários se não existirem
        $blocks = [
            'Conhecimentos Básicos' => 1,
            'Conhecimentos Jurídicos' => 2,
            'Conhecimentos Específicos' => 3,
        ];
        
        foreach ($blocks as $blockName => $order) {
            $block = \App\Models\Block::firstOrCreate(
                ['name' => $blockName],
                ['order' => $order]
            );
            $blocks[$blockName] = $block->id;
        }

        // ========== DISCIPLINAS BÁSICAS ==========
        
        // Língua Portuguesa
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Básicos'],
            'name' => 'Língua Portuguesa',
            'description' => 'Compreensão e interpretação de textos, gramática, redação oficial e correspondência.',
            'order' => 1
        ]);

        // Raciocínio Lógico-Matemático
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Básicos'],
            'name' => 'Raciocínio Lógico-Matemático',
            'description' => 'Modelagem matemática, funções, estatística, geometria e lógica aplicada.',
            'order' => 2
        ]);

        // Informática
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Básicos'],
            'name' => 'Informática',
            'description' => 'Tecnologias da informação, segurança digital, sistemas operacionais e aplicativos.',
            'order' => 3
        ]);

        // Atualidades
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Básicos'],
            'name' => 'Atualidades',
            'description' => 'Temas contemporâneos nacionais e internacionais, política, economia e sociedade.',
            'order' => 4
        ]);

        // Ética no Serviço Público
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Básicos'],
            'name' => 'Ética no Serviço Público',
            'description' => 'Princípios éticos, conduta profissional e responsabilidade no serviço público.',
            'order' => 5
        ]);

        // ========== DISCIPLINAS JURÍDICAS ==========

        // Direito Constitucional
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Jurídicos'],
            'name' => 'Direito Constitucional',
            'description' => 'Constituição Federal, direitos fundamentais, organização do Estado e controle de constitucionalidade.',
            'order' => 6
        ]);

        // Direito Administrativo
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Jurídicos'],
            'name' => 'Direito Administrativo',
            'description' => 'Administração pública, atos administrativos, licitações e contratos administrativos.',
            'order' => 7
        ]);

        // Direito Penal
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Jurídicos'],
            'name' => 'Direito Penal',
            'description' => 'Teoria geral do crime, crimes contra a pessoa, patrimônio e administração pública.',
            'order' => 8
        ]);

        // Direito Processual Penal
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Jurídicos'],
            'name' => 'Direito Processual Penal',
            'description' => 'Processo penal, inquérito policial, ação penal e recursos criminais.',
            'order' => 9
        ]);

        // Legislação Especial
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Jurídicos'],
            'name' => 'Legislação Especial',
            'description' => 'Leis especiais aplicáveis à atividade policial e segurança pública.',
            'order' => 10
        ]);

        // ========== DISCIPLINAS ESPECÍFICAS ==========

        // Legislação de Trânsito
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Específicos'],
            'name' => 'Legislação de Trânsito',
            'description' => 'Código de Trânsito Brasileiro, infrações, penalidades e procedimentos administrativos.',
            'order' => 11
        ]);

        // Direito Civil
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Específicos'],
            'name' => 'Direito Civil',
            'description' => 'Pessoas, bens, fatos jurídicos, obrigações, contratos e responsabilidade civil.',
            'order' => 12
        ]);

        // Direito Empresarial
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Específicos'],
            'name' => 'Direito Empresarial',
            'description' => 'Direito societário, títulos de crédito, contratos empresariais e falência.',
            'order' => 13
        ]);

        // Direito Tributário
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Específicos'],
            'name' => 'Direito Tributário',
            'description' => 'Sistema tributário nacional, impostos, taxas, contribuições e processo tributário.',
            'order' => 14
        ]);

        // Direito Previdenciário
        Discipline::create([
            'block_id' => $blocks['Conhecimentos Específicos'],
            'name' => 'Direito Previdenciário',
            'description' => 'Seguridade social, benefícios previdenciários e regime próprio de previdência.',
            'order' => 15
        ]);

        echo "✅ Disciplinas criadas com sucesso!\n";
    }
}