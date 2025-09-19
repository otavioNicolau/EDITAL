<?php
namespace Database\Seeders;
use App\Models\Block;
use App\Models\Topic;
use App\Models\Discipline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StudyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🌱 Iniciando seed do banco de dados...\n";
        // Limpar apenas dados de tópicos (os blocos já foram criados pelo DisciplineSeeder)
        DB::table('study_sessions')->delete();
        DB::table('reviews')->delete();
        DB::table('study_items')->delete();
        DB::table('topics')->delete();
        
        // Buscar disciplinas existentes
        $disciplinas = [
            'Língua Portuguesa' => Discipline::where('name', 'Língua Portuguesa')->first(),
            'Raciocínio Lógico-Matemático' => Discipline::where('name', 'Raciocínio Lógico-Matemático')->first(),
            'Informática' => Discipline::where('name', 'Informática')->first(),
            'Atualidades' => Discipline::where('name', 'Atualidades')->first(),
            'Ética no Serviço Público' => Discipline::where('name', 'Ética no Serviço Público')->first(),
            'Direito Constitucional' => Discipline::where('name', 'Direito Constitucional')->first(),
            'Direito Administrativo' => Discipline::where('name', 'Direito Administrativo')->first(),
            'Direito Penal' => Discipline::where('name', 'Direito Penal')->first(),
            'Direito Processual Penal' => Discipline::where('name', 'Direito Processual Penal')->first(),
            'Legislação Especial' => Discipline::where('name', 'Legislação Especial')->first(),
            'Legislação de Trânsito' => Discipline::where('name', 'Legislação de Trânsito')->first(),
            'Direito Civil' => Discipline::where('name', 'Direito Civil')->first(),
            'Direito Empresarial' => Discipline::where('name', 'Direito Empresarial')->first(),
            'Direito Tributário' => Discipline::where('name', 'Direito Tributário')->first(),
            'Direito Previdenciário' => Discipline::where('name', 'Direito Previdenciário')->first(),
        ];
        
        // ========== BLOCO 1 - CONHECIMENTOS BÁSICOS ==========
        // LÍNGUA PORTUGUESA - usar o bloco da disciplina
        $linguaPortuguesa = $disciplinas['Língua Portuguesa']->block;
        $topicosPortugues = [
            'Compreensão e interpretação de textos',
            'Reconhecimento de tipos e gêneros textuais',
            'Domínio da ortografia oficial',
            'Coesão textual',
            'Emprego de elementos de referenciação, substituição e repetição',
            'Emprego de conectores e outros elementos de sequenciação textual',
            'Domínio da estrutura morfossintática do período',
            'Emprego das classes de palavras',
            'Relações de coordenação entre orações e termos',
            'Relações de subordinação entre orações e termos',
            'Emprego dos sinais de pontuação',
            'Concordância verbal e nominal',
            'Regência verbal e nominal',
            'Emprego do sinal indicativo de crase',
            'Colocação dos pronomes átonos',
            'Significação das palavras',
            'Substituição de palavras ou trechos',
            'Reorganização de orações e períodos',
            'Reescrita de textos de diferentes gêneros e formalidade',
            'Correspondência oficial (Manual de Redação da PR)',
            'Aspectos gerais da redação oficial',
            'Finalidade dos expedientes oficiais',
            'Adequação da linguagem ao documento',
            'Adequação do formato ao gênero',
        ];
        foreach ($topicosPortugues as $topico) {
            Topic::create([
                'block_id' => $linguaPortuguesa->id,
                'discipline_id' => $disciplinas['Língua Portuguesa']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // RACIOCÍNIO LÓGICO-MATEMÁTICO - usar o bloco da disciplina
        $raciocinio = $disciplinas['Raciocínio Lógico-Matemático']->block;
        $topicosRaciocinio = [
            'Modelagem de situações-problema por equações do 1º e 2º graus e sistemas lineares',
            'Noção de função',
            'Funções afim, quadrática, exponencial e logarítmica',
            'Aplicações de funções',
            'Taxas de variação',
            'Razão e proporção',
            'Regra de três simples e composta',
            'Porcentagem',
            'Sequências numéricas, PA e PG',
            'Contagem, probabilidade e estatística',
            'Descrição e análise de dados',
            'Leitura e interpretação de tabelas e gráficos',
            'Cálculo de médias e desvios',
            'Teoria dos conjuntos',
            'Geometria plana e espacial',
            'Escalas',
            'Visualização espacial, projeções, cortes',
            'Métrica, áreas e volumes',
            'Estimativas e aplicações',
        ];
        foreach ($topicosRaciocinio as $topico) {
            Topic::create([
                'block_id' => $raciocinio->id,
                'discipline_id' => $disciplinas['Raciocínio Lógico-Matemático']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // INFORMÁTICA - usar o bloco da disciplina
        $informatica = $disciplinas['Informática']->block;
        $topicosInformatica = [
            'Conceitos e utilização de tecnologias, ferramentas, aplicativos e internet/intranet',
            'Ferramentas comerciais (navegadores, e-mail, redes sociais, colaboração)',
            'Noções de sistema operacional (Windows)',
            'Transformação digital',
            'IoT, Big Data, Inteligência Artificial',
            'Segurança (vírus, phishing, antivírus, firewall, VPN etc.)',
            'Computação em nuvem',
        ];
        foreach ($topicosInformatica as $topico) {
            Topic::create([
                'block_id' => $informatica->id,
                'discipline_id' => $disciplinas['Informática']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // NOÇÕES DE FÍSICA - criar bloco se não existir disciplina
        $fisica = Block::firstOrCreate([
            'name' => 'Noções de Física',
        ], [
            'order' => 4,
        ]);
        $topicosFisica = [
            'Cinemática escalar e vetorial',
            'Movimento circular',
            'Leis de Newton',
            'Energia mecânica, trabalho e potência',
            'Impulso e quantidade de movimento',
            'Gravitação',
            'Estática dos corpos rígidos',
            'Hidrostática',
        ];
        foreach ($topicosFisica as $topico) {
            Topic::create([
                'block_id' => $fisica->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // ÉTICA NO SERVIÇO PÚBLICO - usar o bloco da disciplina
        $etica = $disciplinas['Ética no Serviço Público']->block;
        $topicosEtica = [
            'Ética e moral',
            'Ética, princípios e valores',
            'Ética e democracia: exercício da cidadania',
            'Ética e função pública',
            'Ética no Setor Público',
            'Lei nº 8.112/1990 e suas alterações: regime disciplinar',
            'Lei nº 8.429/1992: improbidade administrativa',
            'Lei nº 12.813/2013: conflito de interesses no exercício de cargo ou emprego do Poder Executivo',
            'Decreto nº 1.171/1994: Código de Ética Profissional do Servidor Público Civil do Poder Executivo Federal',
            'Decreto nº 6.029/2007: Sistema de Gestão da Ética do Poder Executivo Federal',
            'Resolução nº 10/2008 da Comissão de Ética Pública da Presidência da República',
        ];
        foreach ($topicosEtica as $topico) {
            Topic::create([
                'block_id' => $etica->id,
                'discipline_id' => $disciplinas['Ética no Serviço Público']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // ========== BLOCO 2 - CONHECIMENTOS ESPECÍFICOS ==========
        // DIREITO CONSTITUCIONAL - usar o bloco da disciplina
        $direitoConstitucional = $disciplinas['Direito Constitucional']->block;
        $topicosDireitoConstitucional = [
            'Constituição da República Federativa do Brasil de 1988',
            'Princípios fundamentais',
            'Aplicabilidade das normas constitucionais',
            'Direitos e garantias fundamentais',
            'Direitos e deveres individuais e coletivos',
            'Direitos sociais',
            'Direitos de nacionalidade',
            'Direitos políticos',
            'Partidos políticos',
            'Organização político-administrativa do Estado',
            'Estado federal brasileiro',
            'União, estados, Distrito Federal e municípios',
            'Administração pública',
            'Disposições gerais e princípios',
            'Servidores públicos',
            'Poder executivo',
            'Atribuições e responsabilidades do presidente da República',
            'Poder legislativo',
            'Estrutura, funcionamento e atribuições',
            'Processo legislativo',
            'Fiscalização contábil, financeira e orçamentária',
            'Poder judiciário',
            'Disposições gerais',
            'Órgãos do poder judiciário',
            'Funções essenciais à justiça',
            'Ministério público',
            'Advocacia pública',
            'Defensoria pública',
        ];
        foreach ($topicosDireitoConstitucional as $topico) {
            Topic::create([
                'block_id' => $direitoConstitucional->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO ADMINISTRATIVO - usar o bloco da disciplina
        $direitoAdministrativo = $disciplinas['Direito Administrativo']->block;
        $topicosDireitoAdministrativo = [
            'Estado, governo e administração pública',
            'Conceitos, elementos, poderes, natureza, fins e princípios',
            'Direito administrativo',
            'Conceito, fontes e princípios',
            'Organização administrativa',
            'Centralização, descentralização, concentração e desconcentração',
            'Administração direta e indireta',
            'Autarquias, fundações, empresas públicas e sociedades de economia mista',
            'Ato administrativo',
            'Conceito, requisitos, atributos, classificação e espécies',
            'Invalidação, anulação e revogação',
            'Prescrição',
            'Agentes administrativos',
            'Investidura, exercício, direitos, deveres e responsabilidades',
            'Lei nº 8.112/1990 e suas alterações',
            'Poderes da administração',
            'Hierárquico, disciplinar, regulamentar e de polícia',
            'Uso e abuso do poder',
            'Licitação',
            'Princípios, contratação direta, modalidades, tipos e procedimento',
            'Lei nº 8.666/1993 e suas alterações',
            'Lei nº 10.520/2002 e suas alterações (pregão)',
            'Lei nº 12.462/2011 e suas alterações (RDC)',
            'Decreto nº 10.024/2019 (licitações eletrônicas)',
            'Controle da administração pública',
            'Controle exercido pela administração pública',
            'Controle judicial',
            'Controle legislativo',
            'Improbidade administrativa: Lei nº 8.429/1992 e suas alterações',
        ];
        foreach ($topicosDireitoAdministrativo as $topico) {
            Topic::create([
                'block_id' => $direitoAdministrativo->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO CIVIL - usar o bloco da disciplina
        $direitoCivil = $disciplinas['Direito Civil']->block;
        $topicosDireitoCivil = [
            'Lei de Introdução às Normas do Direito Brasileiro',
            'Vigência, aplicação, interpretação e integração das leis',
            'Conflito das leis no tempo',
            'Eficácia da lei no espaço',
            'Pessoas naturais',
            'Conceito, início da personalidade, capacidade, direitos da personalidade',
            'Pessoas jurídicas',
            'Conceito, classificação, constituição, extinção e desconsideração',
            'Domicílio',
            'Bens',
            'Conceito, classificação, espécies',
            'Fatos jurídicos',
            'Conceito e classificação',
            'Negócio jurídico',
            'Conceito, classificação, interpretação, elementos, defeitos, invalidade',
            'Atos jurídicos lícitos e ilícitos',
            'Prescrição e decadência',
            'Obrigações',
            'Conceito, classificação, fontes, elementos',
            'Adimplemento e extinção das obrigações',
            'Inadimplemento das obrigações',
            'Contratos',
            'Conceito, classificação, interpretação, formação',
            'Extinção, inadimplemento, lesão, teoria da imprevisão',
            'Responsabilidade civil',
            'Conceito, pressupostos e espécies',
            'Indenização',
            'Dano material e dano moral',
        ];
        foreach ($topicosDireitoCivil as $topico) {
            Topic::create([
                'block_id' => $direitoCivil->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO PROCESSUAL CIVIL - criar bloco se não existir disciplina
        $direitoProcessualCivil = Block::firstOrCreate([
            'name' => 'Direito Processual Civil',
        ], [
            'order' => 9,
        ]);
        $topicosProcessualCivil = [
            'Direito processual civil',
            'Conceito, objeto, divisão',
            'Fontes, interpretação',
            'Lei processual civil no tempo e no espaço',
            'Função jurisdicional',
            'Conceito, características, órgãos',
            'Jurisdição contenciosa e voluntária',
            'Competência',
            'Conceito, critérios determinadores, modificações',
            'Incompetência',
            'Sujeitos processuais',
            'Conceito de parte e terceiro',
            'Capacidade processual e postulatória',
            'Representação e assistência',
            'Sucessão das partes',
            'Litisconsórcio e assistência',
            'Intervenção de terceiros',
            'Ministério Público',
            'Atos processuais',
            'Conceito, classificação',
            'Atos das partes, do juiz e dos auxiliares da justiça',
            'Forma, tempo e lugar dos atos processuais',
            'Prazos',
            'Comunicação dos atos processuais',
            'Citação, intimação e notificação',
            'Nulidades processuais',
            'Formação, suspensão e extinção do processo',
            'Processo de conhecimento',
            'Conceito, espécies',
            'Procedimento comum',
            'Petição inicial',
            'Resposta do réu',
            'Revelia',
            'Providências preliminares',
            'Julgamento conforme o estado do processo',
            'Audiência de instrução e julgamento',
            'Sentença e coisa julgada',
            'Recursos',
            'Conceito, pressupostos, classificação',
            'Apelação, embargos de declaração, recurso ordinário, recurso especial, recurso extraordinário',
            'Processo de execução',
            'Execução em geral',
            'Execução de título executivo judicial e extrajudicial',
            'Execução das obrigações de fazer, não fazer e dar',
            'Execução por quantia certa',
            'Embargos à execução',
            'Processo cautelar',
            'Conceito, características',
            'Procedimentos cautelares específicos',
        ];
        foreach ($topicosProcessualCivil as $topico) {
            Topic::create([
                'block_id' => $direitoProcessualCivil->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO PENAL - usar o bloco da disciplina
        $direitoPenal = $disciplinas['Direito Penal']->block;
        $topicosDireitoPenal = [
            'Aplicação da lei penal',
            'Princípios da legalidade e da anterioridade',
            'Lei penal no tempo e no espaço',
            'Tempo e lugar do crime',
            'Lei penal excepcional, especial e temporária',
            'Territorialidade e extraterritorialidade da lei penal',
            'Pena cumprida no estrangeiro',
            'Eficácia da sentença estrangeira',
            'Contagem de prazo',
            'Frações não computáveis da pena',
            'Interpretação da lei penal',
            'Analogia',
            'Irretroatividade da lei penal',
            'Conflito aparente de normas penais',
            'Crime',
            'Classificação dos crimes',
            'Teorias do crime',
            'O fato típico e seus elementos',
            'Crime consumado e tentado',
            'Pena da tentativa',
            'Concurso de crimes',
            'Ilicitude e causas de exclusão',
            'Excesso punível',
            'Culpabilidade',
            'Elementos e causas de exclusão',
            'Imputabilidade penal',
            'Concurso de pessoas',
            'Autoria e participação',
            'Penas',
            'Espécies de penas',
            'Cominação das penas',
            'Aplicação da pena',
            'Suspensão condicional da pena',
            'Livramento condicional',
            'Efeitos da condenação',
            'Reabilitação',
            'Medidas de segurança',
            'Espécies de medidas de segurança',
            'Aplicação da medida de segurança',
            'Extinção da punibilidade',
            'Crimes contra a pessoa',
            'Crimes contra o patrimônio',
            'Crimes contra a fé pública',
            'Crimes contra a administração pública',
        ];
        foreach ($topicosDireitoPenal as $topico) {
            Topic::create([
                'block_id' => $direitoPenal->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // ========== BLOCO 3 - CONHECIMENTOS COMPLEMENTARES ==========
        // DIREITO PROCESSUAL PENAL - usar o bloco da disciplina
        $direitoProcessualPenal = $disciplinas['Direito Processual Penal']->block;
        $topicosProcessualPenal = [
            'Aplicação da lei processual no tempo, no espaço e em relação às pessoas',
            'Disposições preliminares do Código de Processo Penal',
            'Inquérito policial',
            'Histórico, natureza, conceito, finalidade, características, fundamento, titularidade, grau de cognição, valor probatório, formas de instauração, notitia criminis, delatio criminis, procedimentos, indiciamento, garantias do investigado, conclusão e prazos',
            'Prova',
            'Conceito, objeto, finalidade, destinatário, ônus, princípio da comunhão da prova, classificação, meios, procedimento probatório, limitações constitucionais das provas, prova ilícita, prova pericial, interrogatório, confissão, perguntas ao ofendido, testemunhas, reconhecimento, acareação, documentos, indícios',
            'Prisão, medidas cautelares e liberdade provisória',
            'Prisão em flagrante, prisão preventiva, prisão por pronúncia, prisão por sentença condenatória recorrível, prisão temporária, liberdade provisória',
            'Ação penal',
            'Conceito, características, condições, classificação, princípios, titularidade, ação penal pública, ação penal privada',
            'Jurisdição',
            'Conceito, características, princípios, competência',
            'Critérios de determinação e modificação da competência, competência funcional, territorial, em razão da matéria, por prerrogativa de função',
            'Juiz, Ministério Público, acusado, defensor, assistentes e auxiliares da justiça',
            'Forma, lugar e tempo, prazos, comunicações e intimações',
            'Citação e intimações',
            'Conceito, sentença absolutória, sentença condenatória, efeitos civis da sentença penal',
            'Processos em espécie',
            'Processo comum, procedimento do júri, processos especiais',
            'Nulidades',
            'Conceito, princípios, classificação, declaração de nulidade, nulidades absolutas e relativas',
            'Recursos em geral',
            'Conceito, fundamentos, princípios, classificação, pressupostos de admissibilidade',
            'Recursos em espécie',
            'Apelação, recurso em sentido estrito, embargos de declaração, carta testemunhável, recurso extraordinário, recurso especial',
            'Habeas corpus e seu processo',
            'Relações jurisdicionais com autoridade estrangeira',
        ];
        foreach ($topicosProcessualPenal as $topico) {
            Topic::create([
                'block_id' => $direitoProcessualPenal->id,
                'discipline_id' => $disciplinas['Direito Processual Penal']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO EMPRESARIAL - usar o bloco da disciplina
        $direitoEmpresarial = $disciplinas['Direito Empresarial']->block;
        $topicosEmpresarial = [
            'Direito de empresa',
            'Conceito, evolução histórica',
            'Fontes do direito empresarial',
            'Autonomia do direito empresarial',
            'Empresário',
            'Conceito e caracterização',
            'Inscrição',
            'Capacidade',
            'Empresa individual de responsabilidade limitada',
            'Microempresário individual',
            'Sociedades',
            'Sociedade não personificada',
            'Sociedade em comum',
            'Sociedade em conta de participação',
            'Sociedade personificada',
            'Sociedade simples',
            'Sociedade em nome coletivo',
            'Sociedade em comandita simples',
            'Sociedade limitada',
            'Sociedade anônima',
            'Sociedade em comandita por ações',
            'Sociedade cooperativa',
            'Estabelecimento',
            'Conceito',
            'Natureza jurídica',
            'Elementos do estabelecimento',
            'Trespasse',
            'Institutos complementares',
            'Registro',
            'Nome empresarial',
            'Prepostos',
            'Escrituração',
            'Títulos de crédito',
            'Conceito, características, princípios',
            'Classificação',
            'Endosso',
            'Aval',
            'Vencimento',
            'Pagamento',
            'Ações cambiárias',
            'Letra de câmbio',
            'Nota promissória',
            'Cheque',
            'Duplicata',
            'Contratos empresariais',
            'Compra e venda mercantil',
            'Contratos bancários',
            'Recuperação judicial, extrajudicial e falência',
            'Conceito, evolução legislativa, princípios',
            'Recuperação judicial',
            'Recuperação extrajudicial',
            'Falência',
        ];
        foreach ($topicosEmpresarial as $topico) {
            Topic::create([
                'block_id' => $direitoEmpresarial->id,
                'discipline_id' => $disciplinas['Direito Empresarial']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO DO TRABALHO - criar bloco se não existir disciplina
        $direitoTrabalho = Block::firstOrCreate([
            'name' => 'Direito do Trabalho',
        ], [
            'order' => 13,
        ]);
        $topicosTrabalho = [
            'Direito do trabalho',
            'Conceito, características, divisão',
            'Fontes e princípios',
            'Relação de trabalho e relação de emprego',
            'Conceitos e distinções',
            'Relação de emprego',
            'Conceito, características, elementos',
            'Sujeitos da relação de emprego',
            'Empregado',
            'Conceito, caracterização',
            'Empregado doméstico',
            'Empregado rural',
            'Empregado público',
            'Empregado temporário',
            'Estagiário',
            'Empregador',
            'Poderes do empregador',
            'Grupo econômico',
            'Sucessão de empregadores',
            'Responsabilidade solidária',
            'Contrato individual de trabalho',
            'Conceito, classificação, características',
            'Alteração das condições de trabalho',
            'Suspensão e interrupção do contrato de trabalho',
            'Rescisão do contrato de trabalho',
            'Aviso prévio',
            'Jornada de trabalho',
            'Conceito e espécies de jornada de trabalho',
            'Períodos de descanso',
            'Trabalho noturno',
            'Trabalho extraordinário',
            'Sistema de compensação de horas',
            'Salário e remuneração',
            'Conceito e distinções',
            'Composição do salário',
            'Modalidades de salário',
            'Formas e meios de pagamento do salário',
            'Equiparação salarial',
            'Férias',
            'Direito a férias e sua duração',
            'Concessão e época das férias',
            'Remuneração e abono de férias',
            'Fundo de Garantia do Tempo de Serviço (FGTS)',
            'Segurança e medicina do trabalho',
            'CIPA',
            'Conceito e características',
            'Composição e funcionamento',
            'Proteção ao trabalho da mulher',
            'Estabilidade da gestante',
            'Licença-maternidade',
            'Proteção ao trabalho do menor',
            'Idade mínima para o trabalho',
            'Trabalho do menor de 18 anos',
            'Direitos constitucionais dos trabalhadores',
            'Direitos sociais na Constituição de 1988',
            'Direito coletivo do trabalho',
            'Liberdade sindical',
            'Organização sindical',
            'Convenções e acordos coletivos de trabalho',
            'Direito de greve',
            'Representação dos trabalhadores na empresa',
            'Processo do trabalho',
            'Conceito, fontes, princípios',
            'Organização da Justiça do Trabalho',
            'Competência da Justiça do Trabalho',
            'Partes e procuradores',
            'Atos, termos e prazos processuais',
            'Audiência',
            'Recursos trabalhistas',
            'Execução trabalhista',
        ];
        foreach ($topicosTrabalho as $topico) {
            Topic::create([
                'block_id' => $direitoTrabalho->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO TRIBUTÁRIO - usar o bloco da disciplina
        $direitoTributario = $disciplinas['Direito Tributário']->block;
        $topicosTributario = [
            'Sistema Tributário Nacional',
            'Conceito de tributo',
            'Espécies de tributos',
            'Competência tributária',
            'Limitações constitucionais ao poder de tributar',
            'Princípios constitucionais tributários',
            'Imunidades',
            'Código Tributário Nacional',
            'Conceito e classificação dos tributos',
            'Legislação tributária',
            'Vigência da legislação tributária',
            'Aplicação da legislação tributária',
            'Interpretação e integração da legislação tributária',
            'Obrigação tributária',
            'Obrigação principal e acessória',
            'Fato gerador da obrigação tributária',
            'Sujeição ativa e passiva',
            'Solidariedade',
            'Capacidade tributária',
            'Domicílio tributário',
            'Responsabilidade tributária',
            'Responsabilidade dos sucessores',
            'Responsabilidade de terceiros',
            'Responsabilidade por infrações',
            'Crédito tributário',
            'Constituição do crédito tributário',
            'Lançamento',
            'Modalidades de lançamento',
            'Suspensão da exigibilidade do crédito tributário',
            'Extinção do crédito tributário',
            'Exclusão do crédito tributário',
            'Garantias e privilégios do crédito tributário',
            'Administração tributária',
            'Fiscalização',
            'Dívida ativa',
            'Certidões negativas',
            'Impostos da União',
            'Imposto sobre importação',
            'Imposto sobre exportação',
            'Imposto sobre renda e proventos',
            'Imposto sobre produtos industrializados',
            'Imposto sobre operações financeiras',
            'Impostos dos Estados e Distrito Federal',
            'Imposto sobre transmissão causa mortis e doação',
            'Imposto sobre circulação de mercadorias e serviços',
            'Imposto sobre propriedade de veículos automotores',
            'Impostos dos Municípios',
            'Imposto sobre propriedade predial e territorial urbana',
            'Imposto sobre transmissão inter vivos',
            'Imposto sobre serviços',
            'Processo administrativo tributário',
            'Conceito e princípios',
            'Processo judicial tributário',
            'Ação de execução fiscal',
            'Ação anulatória de débito fiscal',
            'Ação de repetição de indébito',
            'Ação declaratória',
            'Mandado de segurança',
            'Ação cautelar fiscal',
            'Crimes contra a ordem tributária',
        ];
        foreach ($topicosTributario as $topico) {
            Topic::create([
                'block_id' => $direitoTributario->id,
                'discipline_id' => $disciplinas['Direito Tributário']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        // DIREITO PREVIDENCIÁRIO - usar o bloco da disciplina
        $direitoPrevidenciario = $disciplinas['Direito Previdenciário']->block;
        $topicosPrevidenciario = [
            'Seguridade social',
            'Conceito, origem e evolução legislativa no Brasil',
            'Organização e princípios constitucionais',
            'Regime Geral de Previdência Social',
            'Conceito, características e fonte de custeio',
            'Regime próprio de previdência social',
            'Segurados obrigatórios',
            'Empregado, empregado doméstico, contribuinte individual, trabalhador avulso e segurado especial',
            'Segurado facultativo',
            'Filiação',
            'Conceito, características e espécies de beneficiários',
            'Dependentes',
            'Equiparação',
            'Perda da qualidade de dependente',
            'Carência',
            'Tabela de carência',
            'Carência das espécies de benefícios',
            'Salário de benefício',
            'Salário de contribuição',
            'Conceito e limitações',
            'Parcelas integrantes e parcelas não integrantes',
            'Salário-base',
            'Renda mensal inicial',
            'Data de início do benefício',
            'Valor mínimo e máximo dos benefícios',
            'Reajustamento do valor dos benefícios',
            'Benefícios',
            'Aposentadoria por invalidez',
            'Aposentadoria por idade',
            'Aposentadoria por tempo de contribuição',
            'Aposentadoria especial',
            'Auxílio-doença',
            'Salário-família',
            'Salário-maternidade',
            'Auxílio-acidente',
            'Pensão por morte',
            'Auxílio-reclusão',
            'Abono anual',
            'Serviços',
            'Habilitação e reabilitação profissional',
            'Serviço social',
            'Custeio da seguridade social',
            'Receitas da União',
            'Receitas das contribuições sociais',
            'Arrecadação e recolhimento das contribuições',
            'Decadência e prescrição',
            'Crimes contra a previdência social',
        ];
        foreach ($topicosPrevidenciario as $topico) {
            Topic::create([
                'block_id' => $direitoPrevidenciario->id,
                'discipline_id' => $disciplinas['Direito Previdenciário']?->id,
                'name' => $topico,
                'weight' => 1,
            ]);
        }
        echo "\n✅ Seed concluído com sucesso!\n";
        echo "📚 Foram criadas 15 disciplinas organizadas em 3 blocos:\n";
        echo "   • BLOCO 1 - CONHECIMENTOS BÁSICOS (5 disciplinas)\n";
        echo "   • BLOCO 2 - CONHECIMENTOS ESPECÍFICOS (5 disciplinas)\n";
        echo "   • BLOCO 3 - CONHECIMENTOS COMPLEMENTARES (5 disciplinas)\n";
        echo "🎯 Total de tópicos criados: " . Topic::count() . "\n";
    }
}