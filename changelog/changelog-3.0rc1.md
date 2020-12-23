# Changelog OcoMon 3.0rc1 (*lançamento em 15/12/2020*)

## Modificações gerais

+ Front-end reescrito
    - Novo menu de navegação
    - Bootstrap 4.5
    - Font-awesome
    - Datatables
    - Responsividade
    
+ Utilização de gráficos ilustrativos com a bibilioteca ChartJS

+ Utilização do PDO para conexão com o banco

+ Nova classe para cálculo de tempo válido
    - A principal diferença para o modelo da nova classe e a classe antiga é que a classe antiga somava todo o período integralmente e descontava os tempos inválidos só no final do período, ou seja, só era possível saber o tempo filtrado atualizado do chamado após o mesmo estar encerrado no sistema. A nova classe trabalha com períodos de tempo, somando cada período em tempo real de acordo com o status da ocorrência e com a jornada de trabalho, mantendo o tempo total atualizado independentemente de quando acontece a parada.
    
+ Paradas de relógio
    - Em função do status do chamado
    - Em função dos horários não cobertos pela jornada de trabalho associada ao chamado
    
+ Melhor controle sobre os SLAs
    
+ Melhorias no sistema de login

+ Muitas melhorias internas (onde quase ninguém nota)
    - CSRF
    - Melhor tratamento dos inputs
    - Melhor processamento das informações
    - Limpeza e otimização dos códigos
    - Refatoração
    
+ Melhor experiência de uso
    - Melhor sistema de mensagens de retorno para o usuário
    - Melhor navegabilidade
    - Melhorias em consistências em diversos formulários
    
+ **Importante**
    - O campo de etiqueta, agora permite a utilização de caracteres alfanuméricos.
    - Mudanças de diversas nomenclaturas para serem mais claras sobre seu significado e função
    
    
    
## Módulo de Ocorrências

+ Sistema avançado de filtros
    - Múltiplas seleções
    - Diversas combinações possíveis
    - Visibilidade de colunas configurável
    - Reorganização das colunas
    - Exportação em diversos formatos
        - PDF
        - Excel
        - CSV
        - Opção de impressão

+ Novo sistema de mural de avisos
    - Agora os avisos aparecem como notificações nas telas de filas
    - Controle de data de validade para os avisos
    - Controle para exibir apenas uma vez para cada usuário destino

+ Melhorias no sistema de upload de arquivos

+ Abertura de chamados
    - auto completar para o nome de contato com base nos chamados já existentes para a área do operador
    - Novo campo: email de contato
    - Agora é possível enviar e-mails de forma automática para os usuários mesmo quando o chamado tiver sido aberto por um operador técnico
    - Nova opção de impressão da ocorrência

+ Dashboard completo para o módulo de ocorrências
    - Sistema de cards informativos
    - Gráficos estatísticos

+ Registro de modificações dos chamados

+ Opção específica na tela de detalhes das Ocorrências para agendamento do chamado

+ Nova opção para informações sobre o SLA na tela de detalhes das Ocorrências

+ Melhoria no layout de impressão de ocorrências

+ Melhorias na função de roteiros de atendimento (antigo scripts de atendimento)

+ Função de permitir inserção de comentários ao chamado pelo usuário de nível somente-abertura após o chamado já estar aberto.

+ Melhorias significativas nos relatórios do módulo de ocorrências
    
    
## Módulo de Administração

+ Perfis de Jornadas de Trabalho

+ Nova opção de configuração para tolerância para SLAs
    - indica quanto tempo (em percentual) após o vencimento do SLA será considerado indicador intermediário antes de ser considerado estourado.

+ Melhoria no sistema de seleção de campos nos perfis de tela de abertura

+ Na listagem de perfis de tela de abertura, agora são informadas as áreas em cada perfil está sendo aplicado

+ Na listagem das áreas de atendimento, agora também é informado o perfil de jornada associado e também os módulos de acesso

+ Agora todas as configurações relacionadas às áreas de atendimento estão integradas em apenas uma área de configuração

+ Agora cada Status pode ser configurado quanto a gerar parada de relógio ou não

+ Registro da data e horário do último login do usuário
    
    
## Módulo de Inventário

+ O módulo de inventário ainda não foi reescrito, portanto, ainda é o mesmo da versão 2.0 (à excessão da tela inicial, listagem de equipamentos cadastrados e a tela de detalhes dos equipamentos que sofreram ajustes)

+ Esse módulo será priorizado tão logo a versão 3.0 final seja lançada. 

---

# Itens removidos de versões anteriores

+ Replicação de chamados
+ Personalização da aparência e criação de temas via interface do sistema

+ Tempo de documentação
    - Função muito onerosa e de pouca utilidade

+ As dependências de status não são mais utilizadas para cálculo de tempo válido

+ Não é mais possível alterar a descrição dos chamados

+ Não é mais possível alterar as datas dos chamados

+ Usuários administradores de áreas de atendimento agora só terão acesso extra para gerenciamento de usuários (da própria área)

+ Suporte para autenticação via LDAP
    - O suporte para esse tipo de autenticação já existiu em versões antigas mas com o tempo de inatividade do projeto e a falta de ambiente adequado para testes resolvi removê-lo por completo. A intenção é voltar a isso em versões futuras.
    
    
## **Importante saber** (em caso de atualização):
    
- Os tempos de SLAs atingidos pelos chamados pré-existentes à atualização poderão sofrer mudanças pois as regras para cálculo foram modificadas;

- A nova versão do OcoMon não considera mais, para fins de cálculo de SLA, os níveis de dependência configurados para cada status. Agora o que irá influenciar para desconto de tempos será a configuração dos status quanto à parada de relógio (**deve ser configurado**) em conjunto com a cobertura de tempo das Jornadas de Trabalho.

- Chamados pré-existentes à atualização do sistema não terão contabilizados o tempo em que estiveram em cada status. Para estes, o cálculo do SLA utilizará como filtro apenas as Jornadas de trabalho associadas.

- As configurações de carga horária que existiam no arquivo config.inc.php não serão mais utilizadas. A partir de agora as mesmas devem ser realizadas por meio da criação de  Perfis de Jornadas de Trabalho no menu de administração em [*Admin :: Configurações Gerais :: Perfis de Jornada*] e associá-los às áreas de atendimento em [*Admin :: Configurações Gerais :: Áreas de Atendimento*]

- As novas funcionalidades de parada de relógio só serão aplicadas para ações realizadas após a atualização da versão.

- A máscara de formatação de data e hora sofreu mudança no formato, portanto, é necessário ajustar em [*Admin :: Configurações Gerais :: Configurações básicas :: Formato de data*]

- Nesse primeiro momento apenas o idioma Português está disponível.

- Algumas variáveis de ambiente foram renomeadas para se adequarem ao seu real sentido. Portanto, será necessário revisar os modelos de mensagens configurados em [*Admin :: Configurações Gerais :: E-mail - Mensagens padrão*] para atualizar as variáveis utilizadas.

---


### Fique por dentro das novidades
[Site oficial: https://ocomonphp.sourceforge.io/](https://ocomonphp.sourceforge.io/)

[Twitter: https://twitter.com/OcomonOficial](https://twitter.com/OcomonOficial)

[Youtube: https://www.youtube.com/channel/UCFikgr9Xk2bE__snw1_RYtQ](https://www.youtube.com/channel/UCFikgr9Xk2bE__snw1_RYtQ)

### Entre em contato:
[ocomon.oficial@gmail.com](ocomon.oficial@gmail.com)
