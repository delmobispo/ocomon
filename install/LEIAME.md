# OcoMon - versão 3.0-rc2
## Data: Dezembro de 2020
## Autor: Flávio Ribeiro (flaviorib@gmail.com)

## Licença: GPL


IMPORTANTE:
-----------

Se você deseja instalar o OcoMon por conta própria, é necessário que saiba o que é um servidor WEB e conheça o processo genérico de instalação de sistemas WEB. 

Para instalar o OcoMon é necessário ter uma conta com permissão de criação de databases no MySQL ou MariaDB e acesso à pasta pública do seu servidor web.

Antes de iniciar o processo de instalação, leia esse arquivo até o final.


REQUISITOS:
-----------

+ Servidor web com PHP + Apache + MySQL (ou MariaDB):
    - PHP a partir da versão **7.x** 
        - Biblioteca **mbstring** (geralmente já instalada)
    - MySQL a partir da versão 5.x:
        - Nas versões mais recentes do MySQL, a diretiva SQL_MODE vem com a chave “ONLY_FULL_GROUP_BY” definida por padrão. Para que o OcoMon funcione adequadamente é necessário remover essa chave da diretiva. Para tanto:

        - Adicione as seguintes linhas ao final do seu my.cnf (arquivo de configuração do MySQL):
            
                [mysqld]
                sql_mode = STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION


INSTALAÇÃO OU ATUALIZAÇÃO EM AMBIENTE DE PRODUÇÃO:
--------------------------------------------------

<br>
### Atualização de versão:

#### A partir da versão 3.0rc1:

+ **NOVO:** Se você já está utilizando a versão **3.0rc1**, basta sobrescrever os scripts da sua pasta do OcoMon pelos scripts da nova versão **3.0rc2**. **NENHUMA AÇÃO DE BANCO DE DADOS É NECESSÁRIA**


#### A partir da versão 2.0 final

+ **IMPORTANTE:** Leia com atenção o arquivo changelog-3.0rc\*.md (*em /changelog*) para conferir novidades e principalmente sobre as **funções removidas de versões anteriores** e algumas novas **configurações necessárias** bem como mudanças de retorno sobre o tempo de SLAs para chamados pré-existentes.

+ Realize o **BACKUP** tanto dos scripts da versão em uso quanto do banco de dados atualmente em uso pelo sistema.

+ O processo de atualização considera que a versão corrente é a 2.0 (release final), portanto, se a sua versão for anterior, atualize-a para a versão 2.0 primeiro.

+ Para atualizar a partir da versão 2.0 (release final), basta sobrescrever os scripts da sua pasta do OcoMon pelos scripts da nova versão e importar para o MySQL o arquivo de atualização: 02-DB-UPDATE-FROM-2.0.sql (em ocomon_2.0/install/3.0/). <br><br>

        Ex via linha de comando:
        mysql -u root -p [database_name] < /caminho/para/o/ocomon_3.0/install/3.0/02-DB-UPDATE-FROM-2.0.sql
    
        Onde: [database_name]: É o nome do banco de dados do OcoMon


### Primeira instalação:

O processo de instalação é bastante simples e pode ser realizado seguindo 3 passos:

1. **Instalar os scripts do sistema:**

    Descompacte o contéudo do pacote do OcoMon_3.0 no diretório público do seu servidor web (*o caminho pode variar dependendo da distribuição ou configuração, mas de modo geral costuma ser **/var/www/html/***).

    As permissões dos arquivos podem ser as padrão do seu servidor.

2. **Criação da base de dados:**<br>

    **SISTEMA HOSPEDADO LOCALMENTE** (**localhost** - Se o sistema será instalado em um servidor externo pule para a seção [SISTEMA EM HOSPEDAGEM EXTERNA]):
    
    Para a criação de toda a base do OcoMon, você precisa importar um único arquivo de instruções SQL:
    
    O arquivo é:
    
        01-DB_OCOMON_3.0-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql (em ocomon_3.0/install/3.0/).

    Linha de comando:
        
        mysql -u root -p < /caminho/para/o/ocomon_3.0/install/3.0/01-DB_OCOMON_3.0-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql
        
    O sistema irá solicitar a senha do usuário root (ou de qualquer outro usuário que tenha sido fornecido ao invés de root no comando acima) do MySQL.

    O comando acima irá criar o usuário "ocomon_3" com a senha padrão "senha_ocomon_mysql", e o banco "ocomon_3".

    **É importante alterar essa senha do usuário "ocomon_3" no MySQL logo após a instalação do sistema.**

    Você também pode realizar a importação do arquivo SQL utilizando qualquer gerenciador de banco de dados de sua preferência.


    Caso queira que a base tenha outro nome (ao invés de "ocomon_3"), edite diretamente no arquivo (*identifique as entradas relacionadas ao nome do banco e também à senha de usuário no início do arquivo*) :

    "01-DB_OCOMON_3.0-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql"

    antes de realizar a importação do mesmo. Utilize essas mesmas informações no arquivo de configurações do sistema (passo **3**).
    
    **Após a importação, é recomendável a exclusão da pasta "install".**<br>


    **SISTEMA EM HOSPEDAGEM EXTERNA:**

    Nesse caso, em função de eventuais limitações de criação para nomenclatura de databases e usuários (geralmente o provedor estipula um prefixo para os databases e usuários), é recomendado utilizar o nome de usuário oferecido pelo próprio serviço de hosting ou então criar um usuário específico (se a sua conta de usuário permitir) diretamente pela sua interface de acesso ao banco de dados. Sendo assim:

    - **Crie** uma database específica para o OcoMon (você define o nome);
    - **Crie** um usuário específico para acesso à database do OcoMon (ou utilize seu usuário padrão);
    - **Altere** o script "01-DB_OCOMON_3.0-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql" **removendo** as seguintes linhas do início do arquivo:

            CREATE DATABASE /*!32312 IF NOT EXISTS*/`ocomon_3` /*!40100 DEFAULT CHARACTER SET utf8 */;

            CREATE USER 'ocomon_3'@'localhost' IDENTIFIED BY 'senha_ocomon_mysql';
            GRANT SELECT , INSERT , UPDATE , DELETE ON `ocomon_3` . * TO 'ocomon_3'@'localhost';
            GRANT Drop ON ocomon_3.* TO 'ocomon_3'@'localhost';
            FLUSH PRIVILEGES;

            USE `ocomon_3`;

    - Após isso basta importar o arquivo alterado e seguir com o processo de instalação.

            mysql -u root -p [database_name] < /caminho/para/o/ocomon_3.0/install/3.0/01-DB_OCOMON_3.0-FRESH_INSTALL_STRUCTURE_AND_BASIC_DATA.sql

        Onde: [database_name] é o nome da database que foi criada manualmente.<br>



3. **Criar o arquivo de configurações:**

    Faça uma cópia do arquivo config.inc.php-dist (*/includes/*) e renomeie para config.inc.php. Nesse novo arquivo, confira as informações relacionadas à conexão com o banco de dados (*nome do banco, tabela, usuário e senha*).<br><br>


VERSÃO PARA TESTES:
-------------------

Caso queira testar o sistema antes de instalar, você pode rodar um container Docker com o sistema já funcionando com alguns dados já populados. Se você já possui o Docker, então basta executar o seguinte comando em seu terminal:

        docker run -it --name ocomon_3 -p 8000:80 flaviorib/ocomon_demo-3.0rc1:1.5 /bin/ocomon

Em seguida basta abrir o seu navegador e acessar pelo seguinte endereço:

        localhost:8000

E pronto! Você já está com uma instalação do OcoMon prontinha para testes com os seguintes usuários cadastrados:<br>


| usuário   | Senha     | Descrição                           |
| :-------- | :-------- | :---------------------------------  |
| admin     | admin     | Nível de administração do sistema   |
| operador  | operador  | Operador padrão – nível 1           |
| operador2 | operador  | Operador padrão – nível 2           |
| abertura  | abertura  | Apenas para abertura de ocorrências |


Caso não tenha o Docker, acesse o site e instale a versão referente ao seu sistema operacional:

[https://docs.docker.com/get-docker/](https://docs.docker.com/get-docker/)<br>



PRIMEIROS PASSOS
----------------

ACESSO

    usuário: admin
    
    senha: admin (Não esqueça de alterar esse senha tão logo tenha acesso ao sistema!!)

Novos usuários podem ser criados no menu [Admin::Usuários]
<br><br>


CONFIGURAÇÕES GERAIS DO SISTEMA
-------------------------------

O OcoMon possui duas áreas para configurações diversas do sistema:

- arquivo de configuração: /includes/config.inc.php
    - nesse arquivo estão as informações de conexão com o banco, e paths padrão.

- As demais configurações do sistema são todas acessíveis por meio do menu de administração diretamente na interface do sistema. 
<br><br>



DOCUMENTAÇÃO:
-------------

Toda a documentação do OcoMon está disponível no site do projeto e no canal no Youtube:

+ Site oficial: [https://ocomonphp.sourceforge.io/](https://ocomonphp.sourceforge.io/)

+ Changelog da versão: [https://ocomonphp.sourceforge.io/changelog-da-versao-3-0rc1/](https://ocomonphp.sourceforge.io/changelog-da-versao-3-0rc1/)

+ Twitter: [https://twitter.com/OcomonOficial](https://twitter.com/OcomonOficial)

+ Canal no Youtube: [https://www.youtube.com/channel/UCFikgr9Xk2bE__snw1_RYtQ](https://www.youtube.com/channel/UCFikgr9Xk2bE__snw1_RYtQ)



### Entre em contato:
+ E-mail: [ocomon.oficial@gmail.com](ocomon.oficial@gmail.com)



<br><br>Tenho convicção de que o OcoMon tem potencial para ser a ferramenta que lhe será indispensável na organização e gerência de sua área de atendimento liberando seu precioso tempo para outras realizações.

Bom uso!! :)

Flávio Ribeiro
[flaviorib@gmail.com](flaviorib@gmail)

