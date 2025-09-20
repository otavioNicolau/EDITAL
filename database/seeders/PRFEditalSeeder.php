<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Discipline;
use App\Models\StudyItem;
use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PRFEditalSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'name' => 'BLOCO I',
                'disciplines' => [
                    [
                        'name' => 'LÍNGUA PORTUGUESA',
                        'topics' => [
                            '1 Compreensão e interpretação de textos de gêneros variados.',
                            '2 Reconhecimento de tipos e gêneros textuais.',
                            '3 Domínio da ortografia oficial.',
                            '4 Domínio dos mecanismos de coesão textual.',
                            '4.1 Emprego de elementos de referenciação, substituição e repetição, de conectores e de outros elementos de sequenciação textual.',
                            '4.2 Emprego de tempos e modos verbais.',
                            '5 Domínio da estrutura morfossintática do período.',
                            '5.1 Emprego das classes de palavras.',
                            '5.2 Relações de coordenação entre orações e entre termos da oração.',
                            '5.3 Relações de subordinação entre orações e entre termos da oração.',
                            '5.4 Emprego dos sinais de pontuação.',
                            '5.5 Concordância verbal e nominal.',
                            '5.6 Regência verbal e nominal.',
                            '5.7 Emprego do sinal indicativo de crase.',
                            '5.8 Colocação dos pronomes átonos.',
                            '6 Reescrita de frases e parágrafos do texto.',
                            '6.1 Significação das palavras.',
                            '6.2 Substituição de palavras ou de trechos de texto.',
                            '6.3 Reorganização da estrutura de orações e de períodos do texto. ',
                            '6.4 Reescrita de textos de diferentes gêneros e níveis de formalidade.',
                            '7 Correspondência oficial (conforme Manual de Redação da Presidência da República).',
                            '7.1 Aspectos gerais da redação oficial.',
                            '7.2 Finalidade dos expedientes oficiais.',
                            '7.3 Adequação da linguagem ao tipo de documento.',
                            '7.4 Adequação do formato do texto ao gênero.',
                        ],
                    ],
                    [
                        'name' => 'RACIOCÍNIO LÓGICO-MATEMÁTICO',
                        'topics' => [
                            '1 Modelagem de situações-problema por meio de equações do 1º e 2º graus e sistemas lineares.',
                            '2 Noção de função.',
                            '2.1 Análise gráfica.',
                            '2.2 Funções afim, quadrática, exponencial e logarítmica.',
                            '2.3 Aplicações.',
                            '3 Taxas de variação de grandezas.',
                            '3.1 Razão e proporção com aplicações.',
                            '3.2 Regra de três simples e composta.',
                            '4 Porcentagem.',
                            '5 Regularidades e padrões em sequências.',
                            '5.1 Sequências numéricas.',
                            '5.2 Progressão aritmética e progressão geométrica.',
                            '6 Noções básicas de contagem, probabilidade e estatística.',
                            '7 Descrição e análise de dados.',
                            '7.1 Leitura e interpretação de tabelas e gráficos apresentados em diferentes linguagens e representações.',
                            '7.2 Cálculo de médias e análise de desvios de conjuntos de dados.',
                            '8 Noções básicas de teoria dos conjuntos.',
                            '9 Análise e interpretação de diferentes representações de figuras planas, como desenhos, mapas e plantas.',
                            '9.1 Utilização de escalas.',
                            '9.2 Visualização de figuras espaciais em diferentes posições.',
                            '9.3 Representações bidimensionais de projeções, planificações e cortes.',
                            '10 Métrica.',
                            '10.1 Áreas e volumes.',
                            '10.2 Estimativas.',
                            '10.3 Aplicações.',
                        ],
                    ],
                    [
                        'name' => 'INFORMÁTICA',
                        'topics' => [
                            '1 Conceito de internet e intranet.',
                            '2. Conceitos e modos de utilização de tecnologias, ferramentas, aplicativos e procedimentos associados a internet/intranet.',
                            '2.1 Ferramentas e aplicativos comerciais de navegação, de correio eletrônico, de grupos de discussão, de busca, de pesquisa, de redes sociais e ferramentas colaborativas.',
                            '2.2 Noções de sistema operacional (ambiente Windows).',
                            '2.3 Acesso a distância a computadores, transferência de informação e arquivos, aplicativos de áudio, vídeo e multimídia.',
                            '3 Transformação digital.',
                            '3.1 Internet das coisas (IoT).',
                            '3.2 Big data.',
                            '3.3 Inteligência artificial.',
                            '4 Conceitos de proteção e segurança.',
                            '4.1 Noções de vírus, worms, phishing e pragas virtuais.',
                            '4.2 Aplicativos para segurança (antivírus, firewall, anti-spyware, VPN, etc.).',
                            '5 Computação na nuvem (cloud computing).',
                        ],
                    ],
                    [
                        'name' => 'FÍSICA',
                        'topics' => [
                            '1 Cinemática escalar, cinemática vetorial.',
                            '2 Movimento circular.',
                            '3 Leis de Newton e suas aplicações.',
                            '4 Trabalho.',
                            '5 Potência.',
                            '6 Energia cinética, energia potencial, atrito.',
                            '7 Conservação de energia e suas transformações.',
                            '8 Quantidade de movimento e conservação da quantidade de movimento, impulso.',
                            '9 Colisões.',
                        ],
                    ],
                    [
                        'name' => 'ÉTICA E CIDADANIA',
                        'topics' => [
                            '1 Ética e moral.',
                            '2 Ética, princípios e valores.',
                            '3 Ética e função pública: integridade.',
                            '4. Ética no setor público.',
                            '4.1 Princípios da Administração Pública: moralidade (art. 37 da CF).',
                            '4.2. Deveres dos servidores públicos: moralidade administrativa (Lei nº 8.112/1990, art. 116, IX).',
                            '4.3 Política de governança da administração pública federal (Decreto nº 9.203/2017).',
                            '4.4. Promoção da ética e de regras de conduta para servidores.',
                            '4.4.1. Código de Ética Profissional do Servidor Público Civil do Poder Executivo Federal (Decreto nº 1.171/1994).',
                            '4.4.2 Sistema de Gestão da Ética do Poder Executivo Federal e Comissões de Ética (Decreto nº 6.029/2007).',
                            '4.4.3 Código de Conduta da Alta Administração Federal (Exposição de Motivos nº 37/2000).',
                            '5 Ética e democracia: exercício da cidadania.',
                            '5.1 Promoção da transparência ativa e do acesso à informação (Lei nº 12.527/2011 e Decreto nº 7.724/2012).',
                            '5.2. Tratamento de conflitos de interesses e nepotismo (Lei nº 12.813/2013 e Decreto nº 7.203/2010).',
                        ],
                    ],
                    [
                        'name' => 'GEOPOLÍTICA',
                        'topics' => [
                            '1 O Brasil político: nação e território.',
                            '2 Organização do Estado Brasileiro.',
                            '3 A divisão interregional do trabalho e da produção no Brasil.',
                            '4 A estrutura urbana brasileira e as grandes metrópoles.',
                            '5 Distribuição espacial da população no Brasil e movimentos migratórios internos.',
                            '6 Integração entre indústria e estrutura urbana e setor agrícola no Brasil.',
                            '7 Rede de transporte no Brasil: modais e principais infraestruturas',
                            '8 A integração do Brasil ao processo de internacionalização da economia.',
                            '9 Geografia e gestão ambiental.',
                            '10 Macrodivisão natural do espaço brasileiro: biomas, domínios e ecossistemas. ',
                        ],
                    ],
                    [
                        'name' => 'I LÍNGUA INGLESA',
                        'topics' => [
                            '1 Compreensão de texto escrito em língua inglesa.',
                            '2 Itens gramaticais relevantes para a compreensão dos conteúdos semânticos (língua inglesa).',
                        ],
                    ],
                    [
                        'name' => 'II LÍNGUA ESPANHOLA',
                        'topics' => [
                            '1 Compreensão de texto escrito em língua espanhola.',
                            '2 Itens gramaticais relevantes para a compreensão dos conteúdos semânticos (língua espanhola).',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'BLOCO II',
                'disciplines' => [
                    [
                        'name' => 'LEGISLAÇÃO DE TRÂNSITO',
                        'topics' => [
                            '1 Lei nº 9.503/1997 (Código de Trânsito Brasileiro) e suas alterações, inclusive as da Lei nº 14.071/2020.',
                            '2 Lei nº 5.970/1973.',
                            ') 3 Resoluções do Conselho Nacional de Trânsito (CONTRAN) e suas alterações: 04/1998; 14/1998; 24/1998; 36/1998; 92/1999, exceto os anexos; 110/2000; 160/2004; 210/2006; 211/2006; 216/2006; 227/2007, exceto os anexos; 253/2007; 254/2007; 290/2008; 349/2010; 360/2010; 432/2013; 441/2013; 471/2013; 508/2014; 520/2015; 525/2015; 552/2015, exceto os anexos; 561/2015, exceto as fichas; 667/2017, exceto os anexos; 735/2018, exceto os anexos; 740/2018; 780/2019; 789/2020, Anexo I; 798/2020; 803/2020; 806/2020; 809/2020; 810/2020.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'BLOCO III',
                'disciplines' => [
                    [
                        'name' => 'DIREITO ADMINISTRATIVO',
                        'topics' => [
                            '1 Noções de organização administrativa.',
                            '1.1 Centralização, descentralização, concentração e desconcentração.',
                            '1.2 Administração direta e indireta.',
                            '1.3 Autarquias, fundações, empresas públicas e sociedades de economia mista.',
                            '2 Ato administrativo.',
                            '2.1 Conceito, requisitos, atributos, classificação e espécies.',
                            '3 Agentes públicos.',
                            '3.1 Legislação pertinente.',
                            '3.1.1 Lei nº 8.112/1990 e suas alterações.',
                            '3.1.2 Disposições constitucionais aplicáveis.',
                            '3.2 Disposições doutrinárias.',
                            '3.2.1 Conceito.',
                            '3.2.2 Espécies.',
                            '3.2.3 Cargo, emprego e função pública.',
                            '3.3 Carreira de policial rodoviário federal.',
                            '3.3.1 Lei nº 9.654/1998 e suas alterações (carreira de PRF).',
                            '3.3.2 Lei nº 12.855/2013 (indenização fronteiras).',
                            '3.3.3 Lei nº 13.712/2018 (indenização PRF).',
                            '3.3.4 Decreto nº 8.282/2014 (carreira de PRF).',
                            '4 Poderes administrativos.',
                            '4.1 Hierárquico, disciplinar, regulamentar e de polícia.',
                            '4.2 Uso e abuso do poder.',
                            '5 Licitação.',
                            '5.1 Princípios.',
                            '5.2 Contratação direta: dispensa e inexigibilidade.',
                            '5.3 Modalidades.',
                            '5.4 Tipos.',
                            '5.5 Procedimento.',
                            '6 Controle da Administração Pública.',
                            '6.1 Controle exercido pela Administração Pública.',
                            '6.2 Controle judicial.',
                            '6.3 Controle legislativo.',
                            '7 Responsabilidade civil do Estado.',
                            '7.1 Responsabilidade civil do Estado no direito brasileiro.',
                            '7.1.1 Responsabilidade por ato comissivo do Estado.',
                            '7.1.2 Responsabilidade por omissão do Estado.',
                            '7.2 Requisitos para a demonstração da responsabilidade do Estado.',
                            '7.3 Causas excludentes e atenuantes da responsabilidade do Estado.',
                            '8 Regime jurídico-administrativo.',
                            '8.1 Conceito.',
                            '8.2 Princípios expressos e implícitos da Administração Pública.',
                        ],
                    ],
                    [
                        'name' => 'DIREITO CONSTITUCIONAL',
                        'topics' => [
                            '1 Poder constituinte.',
                            '1.1 Fundamentos do poder constituinte.',
                            '1.2 Poder constituinte originário e derivado.',
                            '1.3 Reforma e revisão constitucionais.',
                            '1.4 Limitação do poder de revisão.',
                            '1.5 Emendas à Constituição.',
                            '2 Fundamentos constitucionais dos direitos e deveres fundamentais.',
                            '2.1 Direitos e deveres individuais e coletivos.',
                            '2.2 Direito à vida, à liberdade, à igualdade, à segurança e à propriedade.',
                            '2.3 Direitos sociais, nacionalidade, cidadania e direitos políticos.',
                            '2.4 Garantias constitucionais individuais.',
                            '2.5 Garantias dos direitos coletivos, sociais e políticos.',
                            '2.6 Remédios constitucionais.',
                            '3 Poder Executivo.',
                            '3.1 Forma e sistema de governo.',
                            '3.2 Chefia de Estado e chefia de governo.',
                            '3.3 Atribuições e responsabilidades do presidente da República.',
                            '3.4 Da União: bens e competências (arts. 20 a 24 da CF).',
                            '4 Defesa do Estado e das instituições democráticas.',
                            '4.1 Forças Armadas (art. 142, CF).',
                            '4.2 Segurança pública (art. 144 da CF).',
                            '4.3 Organização da segurança pública.',
                            '4.4 Atribuições constitucionais da Polícia Rodoviária Federal.',
                            '5 Ordem social.',
                            '5.1 Base e objetivos da ordem social.',
                            '5.2 Seguridade social.',
                            '5.3 Meio ambiente.',
                            '5.4 Família, criança, adolescente, idoso, índio.',
                        ],
                    ],
                    [
                        'name' => 'DIREITO PENAL',
                        'topics' => [
                            '1 Princípios básicos.',
                            '2 Aplicação da lei penal.',
                            '2.1 Lei penal no tempo.',
                            '2.1.1 Tempo do crime.',
                            '2.1.2 Conflito de leis penais no tempo.',
                            '2.2 Lei penal no espaço.',
                            '2.2.1 Lugar do crime.',
                            '2.2.2 Territorialidade. ',
                            '2.2.3 Extraterritorialidade.',
                            '3 Tipicidade.',
                            '3.1 Crime doloso e crime culposo.',
                            '3.2 Erro de tipo.',
                            '3.3 Crime consumado e tentado.',
                            '3.4 Crime impossível.',
                            '3.5 Punibilidade e causas de extinção.',
                            '4 Ilicitude.',
                            '4.1 Causas de exclusão da ilicitude.',
                            '4.2 Excesso punível.',
                            '5 Culpabilidade.',
                            '5.1 Causas de exclusão da culpabilidade.',
                            '5.2 Imputabilidade.',
                            '5.3 Erro de proibição.',
                            '6 Crimes.',
                            '6.1 Crimes contra a pessoa.',
                            '6.2 Crimes contra o patrimônio.',
                            '6.3 Crimes contra a dignidade sexual.',
                            '6.4 Crimes contra a incolumidade pública.',
                            '6.5 Crimes contra a fé pública.',
                            '6.6 Crimes contra a Administração Pública.',
                        ],
                    ],
                    [
                        'name' => 'DIREITO PROCESSUAL PENAL',
                        'topics' => [
                            '1 Ação penal.',
                            '1.1 Conceito.',
                            '1.2 Características.',
                            '1.3 Espécies.',
                            '1.4 Condições.',
                            '2 Termo Circunstanciado de Ocorrência (Lei nº 9.099/1995).',
                            '2.1 Atos processuais: forma, lugar e tempo.',
                            '3 Prova.',
                            '3.1 Conceito, objeto, classificação.',
                            '3.2 Preservação de local de crime.',
                            '3.3 Requisitos e ônus da prova.',
                            '3.4 Provas ilícitas.',
                            '3.5 Meios de prova: pericial, interrogatório, confissão, perguntas ao ofendido, testemunhas, reconhecimento de pessoas e coisas, acareação, documentos, indícios.',
                            '3.6 Busca e apreensão: pessoal, domiciliar, requisitos, restrições, horários.',
                            '4 Prisão.',
                            '4.1 Conceito, formalidades, espécies e mandado de prisão e cumprimento.',
                            '4.2 Prisão em flagrante.',
                            '5 Identificação Criminal (art. 5º, LVIII, da Constituição Federal e art. 3º da Lei nº 12.037/2009).',
                            '6 Diligências Investigatórias (art. 6º e 13 do CPP).',
                        ],
                    ],
                    [
                        'name' => 'LEGISLAÇÃO ESPECIAL',
                        'topics' => [
                            '1 Lei nº 5.553/1968 e Lei nº 12.037/2009.',
                            '2 Lei nº 8.069/1990 e suas alterações.',
                            '3 Lei nº 8.072/1990 e suas alterações.',
                            '4 Decreto nº 1.655/1995 e art. 47 do Decreto nº 9.662/2019.',
                            '5 Lei nº 9.099/1995 e suas alterações.',
                            '6 Lei nº 9.455/1997 e suas alterações.',
                            '7 Lei nº 9.605/1998 e suas alterações: Capítulos III e V.',
                            '8 Lei nº 10.826/2003 e suas alterações: Capítulo IV.',
                            '9 Lei nº 11.343/2006 e suas alterações.',
                            '10 Lei nº 12.850/2013 e suas alterações.',
                            '11 Lei nº 13.675/2018. 12 Lei nº 13.869/2019.',
                        ],
                    ],
                    [
                        'name' => 'DIREITOS HUMANOS',
                        'topics' => [
                            '1 Direitos humanos na Constituição Federal.',
                            '1.1 A Constituição Federal e os tratados internacionais de direitos humanos.',
                            '2 Declaração Universal dos Direitos Humanos.',
                            '3 Convenção Americana sobre Direitos Humanos (Decreto nº 678/1992).',
                        ],
                    ],
                ],
            ],
        ];

        // Segurança e idempotência
        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();

        try {
            \App\Models\Review::truncate();
            StudyItem::truncate();
            Topic::truncate();
            Discipline::truncate();
            Block::truncate();

            foreach ($structure as $blockOrder => $blockData) {
                $block = Block::updateOrCreate(
                    ['name' => $blockData['name']],
                    [
                        'description' => $blockData['name'],
                        'order'       => $blockOrder + 1,
                        'color'       => $blockData['color'] ?? null,
                    ]
                );

                foreach ($blockData['disciplines'] as $disciplineOrder => $disciplineData) {
                    $firstTopic = !empty($disciplineData['topics']) ? Arr::first($disciplineData['topics']) : null;

                    $discipline = Discipline::updateOrCreate(
                        ['block_id' => $block->id, 'name' => $disciplineData['name']],
                        [
                            'description' => $firstTopic,
                            'order'       => $disciplineOrder + 1,
                        ]
                    );

                    foreach ($disciplineData['topics'] as $topicName) {
                        $topic = Topic::updateOrCreate(
                            ['discipline_id' => $discipline->id, 'name' => $topicName],
                            [
                                'block_id' => $block->id,
                                'status'   => 'PLANNED',
                            ]
                        );

                        StudyItem::updateOrCreate(
                            [
                                'topic_id' => $topic->id,
                                'title'    => 'Resumo: ' . $topicName,
                            ],
                            [
                                'notes'    => 'Conteúdo programático conforme edital PRF: ' . $topicName,
                                'kind'     => 'CONCEPT',
                                'status'   => 'NEW',
                                'metadata' => [
                                    'fonte'      => 'Edital Verticalizado — PRF',
                                    'bloco'      => $blockData['name'],
                                    'disciplina' => $disciplineData['name'],
                                ],
                            ]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
