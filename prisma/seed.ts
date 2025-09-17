import { PrismaClient } from '@prisma/client'

const prisma = new PrismaClient()

async function main() {
  console.log('🌱 Iniciando seed do banco de dados...')

  // Limpar dados existentes
  await prisma.studySession.deleteMany()
  await prisma.review.deleteMany()
  await prisma.studyItem.deleteMany()
  await prisma.topic.deleteMany()
  await prisma.block.deleteMany()

  // LÍNGUA PORTUGUESA (BLOCO 1)
  const linguaPortuguesa = await prisma.block.create({
    data: {
      name: 'Língua Portuguesa',
      order: 1,
    },
  })

  const topicosPortugues = [
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
  ]

  for (const topico of topicosPortugues) {
    await prisma.topic.create({
      data: {
        blockId: linguaPortuguesa.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // RACIOCÍNIO LÓGICO-MATEMÁTICO (BLOCO 1)
  const raciocinio = await prisma.block.create({
    data: {
      name: 'Raciocínio Lógico-Matemático',
      order: 2,
    },
  })

  const topicosRaciocinio = [
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
  ]

  for (const topico of topicosRaciocinio) {
    await prisma.topic.create({
      data: {
        blockId: raciocinio.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // INFORMÁTICA (BLOCO 1)
  const informatica = await prisma.block.create({
    data: {
      name: 'Informática',
      order: 3,
    },
  })

  const topicosInformatica = [
    'Conceitos e utilização de tecnologias, ferramentas, aplicativos e internet/intranet',
    'Ferramentas comerciais (navegadores, e-mail, redes sociais, colaboração)',
    'Noções de sistema operacional (Windows)',
    'Transformação digital',
    'IoT, Big Data, Inteligência Artificial',
    'Segurança (vírus, phishing, antivírus, firewall, VPN etc.)',
    'Computação em nuvem',
  ]

  for (const topico of topicosInformatica) {
    await prisma.topic.create({
      data: {
        blockId: informatica.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // NOÇÕES DE FÍSICA (BLOCO 1)
  const fisica = await prisma.block.create({
    data: {
      name: 'Noções de Física',
      order: 4,
    },
  })

  const topicosFisica = [
    'Cinemática escalar e vetorial',
    'Movimento circular',
    'Leis de Newton',
    'Energia mecânica, trabalho e potência',
    'Impulso e quantidade de movimento',
    'Gravitação',
    'Estática dos corpos rígidos',
    'Hidrostática',
  ]

  for (const topico of topicosFisica) {
    await prisma.topic.create({
      data: {
        blockId: fisica.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // ÉTICA NO SERVIÇO PÚBLICO (BLOCO 1)
  const etica = await prisma.block.create({
    data: {
      name: 'Ética no Serviço Público',
      order: 5,
    },
  })

  const topicosEtica = [
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
  ]

  for (const topico of topicosEtica) {
    await prisma.topic.create({
      data: {
        blockId: etica.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // GEOPOLÍTICA BRASILEIRA (BLOCO 1)
  const geopolitica = await prisma.block.create({
    data: {
      name: 'Geopolítica Brasileira',
      order: 6,
    },
  })

  const topicosGeopolitica = [
    'Território brasileiro',
    'Posição geográfica do Brasil',
    'Organização do Estado brasileiro',
    'Divisão política e administrativa',
    'Regionalização',
    'População brasileira',
    'Economia brasileira',
    'Meio ambiente no Brasil',
    'Amazônia',
  ]

  for (const topico of topicosGeopolitica) {
    await prisma.topic.create({
      data: {
        blockId: geopolitica.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // LÍNGUA ESTRANGEIRA (BLOCO 1)
  const linguaEstrangeira = await prisma.block.create({
    data: {
      name: 'Língua Estrangeira',
      order: 7,
    },
  })

  const topicosLinguaEstrangeira = [
    'Inglês: Compreensão de textos escritos em língua inglesa',
    'Inglês: Itens gramaticais relevantes para a compreensão dos conteúdos semânticos',
    'Espanhol: Compreensão de textos escritos em língua espanhola',
    'Espanhol: Itens gramaticais relevantes para a compreensão dos conteúdos semânticos',
  ]

  for (const topico of topicosLinguaEstrangeira) {
    await prisma.topic.create({
      data: {
        blockId: linguaEstrangeira.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // LEGISLAÇÃO DE TRÂNSITO (BLOCO 2)
  const legislacaoTransito = await prisma.block.create({
    data: {
      name: 'Legislação de Trânsito',
      order: 8,
    },
  })

  const topicosLegislacaoTransito = [
    'Lei nº 9.503/1997 (Código de Trânsito Brasileiro) e suas alterações',
    'Lei nº 5.970/1973 e suas alterações',
    'Resolução CONTRAN nº 14/1998',
    'Resolução CONTRAN nº 24/1998',
    'Resolução CONTRAN nº 26/1998',
    'Resolução CONTRAN nº 36/1998',
    'Resolução CONTRAN nº 110/1999',
    'Resolução CONTRAN nº 149/2003',
    'Resolução CONTRAN nº 168/2004',
    'Resolução CONTRAN nº 205/2006',
    'Resolução CONTRAN nº 210/2006',
    'Resolução CONTRAN nº 216/2006',
    'Resolução CONTRAN nº 231/2007',
    'Resolução CONTRAN nº 245/2007',
    'Resolução CONTRAN nº 254/2007',
    'Resolução CONTRAN nº 258/2007',
    'Resolução CONTRAN nº 267/2008',
    'Resolução CONTRAN nº 268/2008',
    'Resolução CONTRAN nº 269/2008',
    'Resolução CONTRAN nº 271/2008',
    'Resolução CONTRAN nº 277/2008',
    'Resolução CONTRAN nº 290/2008',
    'Resolução CONTRAN nº 292/2008',
    'Resolução CONTRAN nº 296/2008',
    'Resolução CONTRAN nº 312/2009',
    'Resolução CONTRAN nº 320/2009',
    'Resolução CONTRAN nº 356/2010',
    'Resolução CONTRAN nº 360/2010',
    'Resolução CONTRAN nº 371/2010',
    'Resolução CONTRAN nº 396/2011',
    'Resolução CONTRAN nº 432/2013',
    'Resolução CONTRAN nº 460/2013',
    'Resolução CONTRAN nº 471/2013',
    'Resolução CONTRAN nº 525/2015',
    'Resolução CONTRAN nº 552/2015',
    'Resolução CONTRAN nº 561/2015',
    'Resolução CONTRAN nº 573/2015',
    'Resolução CONTRAN nº 598/2016',
    'Resolução CONTRAN nº 619/2016',
    'Resolução CONTRAN nº 723/2018',
    'Resolução CONTRAN nº 789/2020',
    'Resolução CONTRAN nº 805/2020',
    'Resolução CONTRAN nº 808/2020',
    'Resolução CONTRAN nº 886/2021',
  ]

  for (const topico of topicosLegislacaoTransito) {
    await prisma.topic.create({
      data: {
        blockId: legislacaoTransito.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // DIREITO ADMINISTRATIVO (BLOCO 3)
  const direitoAdministrativo = await prisma.block.create({
    data: {
      name: 'Direito Administrativo',
      order: 9,
    },
  })

  const topicosDireitoAdministrativo = [
    'Estado, governo e administração pública',
    'Direito administrativo',
    'Princípios do direito administrativo',
    'Organização administrativa',
    'Administração direta e indireta',
    'Centralizada e descentralizada',
    'Autarquias, fundações, empresas públicas e sociedades de economia mista',
    'Ato administrativo',
    'Conceito, requisitos, atributos, classificação e espécies',
    'Invalidação, anulação e revogação',
    'Prescrição',
    'Agentes administrativos',
    'Investidura e exercício da função pública',
    'Direitos e deveres dos funcionários públicos',
    'Regimes jurídicos',
    'Processo administrativo',
    'Poderes da administração',
    'Hierárquico, disciplinar, regulamentar e de polícia',
    'Uso e abuso do poder',
    'Licitação',
    'Controle da administração pública',
  ]

  for (const topico of topicosDireitoAdministrativo) {
    await prisma.topic.create({
      data: {
        blockId: direitoAdministrativo.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // DIREITO CONSTITUCIONAL (BLOCO 3)
  const direitoConstitucional = await prisma.block.create({
    data: {
      name: 'Direito Constitucional',
      order: 10,
    },
  })

  const topicosDireitoConstitucional = [
    'Direito constitucional',
    'Constituição',
    'Poder constituinte',
    'Direitos e garantias fundamentais',
    'Direitos e deveres individuais e coletivos',
    'Direitos sociais',
    'Direitos políticos',
    'Partidos políticos',
    'Nacionalidade',
    'Organização do Estado',
    'Organização político-administrativa',
    'Estado federal brasileiro',
    'União',
    'Estados federados',
    'Municípios',
    'Distrito Federal',
    'Territórios',
    'Intervenção',
    'Administração pública',
    'Disposições gerais',
    'Servidores públicos',
    'Organização dos Poderes no Estado brasileiro',
    'Mecanismos de freios e contrapesos',
    'Poder Executivo',
    'Atribuições e responsabilidades do presidente da República',
    'Segurança pública',
    'Disposições constitucionais aplicáveis',
  ]

  for (const topico of topicosDireitoConstitucional) {
    await prisma.topic.create({
      data: {
        blockId: direitoConstitucional.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // DIREITO PENAL (BLOCO 3)
  const direitoPenal = await prisma.block.create({
    data: {
      name: 'Direito Penal',
      order: 11,
    },
  })

  const topicosDireitoPenal = [
    'Aplicação da lei penal',
    'Princípios da legalidade e da anterioridade',
    'Lei penal no tempo e no espaço',
    'Tempo e lugar do crime',
    'Lei penal especial, local e pessoal',
    'Sujeito ativo e sujeito passivo da infração penal',
    'Tipicidade, ilicitude, culpabilidade, punibilidade',
    'Excludentes de ilicitude e de culpabilidade',
    'Erro de tipo',
    'Erro de proibição',
    'Imputabilidade penal',
    'Concurso de pessoas',
    'Crimes contra a pessoa',
    'Crimes contra o patrimônio',
    'Crimes contra a fé pública',
    'Crimes contra a administração pública',
    'Lei nº 8.072/1990 (Lei dos Crimes Hediondos)',
    'Lei nº 9.455/1997 (Lei de Tortura)',
    'Lei nº 10.826/2003 (Estatuto do Desarmamento)',
    'Lei nº 11.343/2006 (Lei de Drogas)',
    'Lei nº 9.605/1998 (Lei dos Crimes Ambientais)',
  ]

  for (const topico of topicosDireitoPenal) {
    await prisma.topic.create({
      data: {
        blockId: direitoPenal.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // DIREITO PROCESSUAL PENAL (BLOCO 3)
  const direitoProcessualPenal = await prisma.block.create({
    data: {
      name: 'Direito Processual Penal',
      order: 12,
    },
  })

  const topicosDireitoProcessualPenal = [
    'Aplicação da lei processual no tempo, no espaço e em relação às pessoas',
    'Disposições preliminares do Código de Processo Penal',
    'Inquérito policial',
    'Histórico, natureza, conceito, finalidade, características, fundamento, titularidade, grau de cognição, valor probatório, formas de instauração, notitia criminis, delatio criminis, procedimentos, indiciamento, garantias do investigado, conclusão e investigação',
    'Ação penal',
    'Provas',
    'Prisões e liberdade provisória',
    'Disposições constitucionais aplicáveis ao direito processual penal',
    'Prisão em flagrante',
    'Diligências investigatórias',
  ]

  for (const topico of topicosDireitoProcessualPenal) {
    await prisma.topic.create({
      data: {
        blockId: direitoProcessualPenal.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // LEGISLAÇÃO ESPECIAL (BLOCO 3)
  const legislacaoEspecial = await prisma.block.create({
    data: {
      name: 'Legislação Especial',
      order: 13,
    },
  })

  const topicosLegislacaoEspecial = [
    'Lei nº 10.357/2001',
    'Lei nº 6.815/1980',
    'Lei nº 7.102/1983',
    'Lei nº 8.069/1990 (Estatuto da Criança e do Adolescente)',
    'Lei nº 10.741/2003 (Estatuto do Idoso)',
    'Lei nº 11.340/2006 (Lei Maria da Penha)',
    'Lei nº 12.037/2009',
    'Lei nº 12.850/2013',
    'Lei nº 13.675/2018',
    'Lei nº 13.869/2019',
    'Decreto-Lei nº 3.688/1941 (Lei das Contravenções Penais)',
    'Lei nº 9.034/1995 (Lei do Crime Organizado)',
  ]

  for (const topico of topicosLegislacaoEspecial) {
    await prisma.topic.create({
      data: {
        blockId: legislacaoEspecial.id,
        name: topico,
        weight: 1,
      },
    })
  }

  // DIREITOS HUMANOS (BLOCO 3)
  const direitosHumanos = await prisma.block.create({
    data: {
      name: 'Direitos Humanos',
      order: 14,
    },
  })

  const topicosDireitosHumanos = [
    'Teoria geral dos direitos humanos',
    'Conceito, terminologia, estrutura normativa, fundamentação',
    'Afirmação histórica dos direitos humanos',
    'Direitos humanos e responsabilidade do Estado',
    'Direitos humanos na Constituição Federal',
    'Política nacional de direitos humanos',
    'Programas nacionais de direitos humanos',
    'Institucionalização dos direitos e garantias fundamentais',
    'Conceito e mecanismos de proteção',
    'Aplicação dos tratados internacionais de proteção dos direitos humanos no direito brasileiro',
    'Estatuto da Igualdade Racial',
    'Código de Ética da Polícia Rodoviária Federal',
  ]

  for (const topico of topicosDireitosHumanos) {
    await prisma.topic.create({
      data: {
        blockId: direitosHumanos.id,
        name: topico,
        weight: 1,
      },
    })
  }

  console.log('✅ Seeds criadas com sucesso!')
  console.log('📚 Disciplinas criadas como blocos principais:')
  console.log('   - Língua Portuguesa (BLOCO 1)')
  console.log('   - Raciocínio Lógico-Matemático (BLOCO 1)')
  console.log('   - Informática (BLOCO 1)')
  console.log('   - Noções de Física (BLOCO 1)')
  console.log('   - Ética no Serviço Público (BLOCO 1)')
  console.log('   - Geopolítica Brasileira (BLOCO 1)')
  console.log('   - Língua Estrangeira (BLOCO 1)')
  console.log('   - Legislação de Trânsito (BLOCO 2)')
  console.log('   - Direito Administrativo (BLOCO 3)')
  console.log('   - Direito Constitucional (BLOCO 3)')
  console.log('   - Direito Penal (BLOCO 3)')
  console.log('   - Direito Processual Penal (BLOCO 3)')
  console.log('   - Legislação Especial (BLOCO 3)')
  console.log('   - Direitos Humanos (BLOCO 3)')
}

main()
  .catch((e) => {
    console.error(e)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })