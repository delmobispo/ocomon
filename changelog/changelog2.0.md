Changelog versão 2.0
--------------------

1. Até a versão 2.0RC6, o OcoMon utilizava a codificação iso-8859-1 em seus scripts, o que gerava a necessidade de algumas configurações extras nos arquivos de configuração do Apache e do PHP. Na versão 2.0 o sistema todo utiliza a codificação utf-8, desta forma não é mais necessário nenhuma configuração extra para que os caracteres sejam exibidos corretamente na interface do sistema.

2. Agora o sistema é compatível com a versão 7 do PHP.

3. O layout sofreu diversas melhorias e adaptações. Entre elas:

    - a página principal não utiliza mais tabelas para montar o layout.
    - a tela de login foi refeita.
    - o estilo para os campos de formulário foi modificado.
    

4. Agora o sistema permite anexos de arquivos das versões mais recentes do pacote de escritório MS Office.

5. Vários componentes internos foram atualizados para suas versões mais recentes.

6. Foram adicionados dois novos campos para a configuração de envio de e-mails no sistema:
    
    - porta
    - tipo de segurança

7. Alteração de algumas nomenclaturas como:

    - "local" e "setor" passaram a ser "departamento"
    - "ramal" passou a ser "telefone"

8. Ajuste na função de administração de usuários para não permitir que usuários de nível somente-abertura possam ser vinculados à áreas que prestam atendimento.

9. A listagem de relatórios, tanto do módulo de ocorrências quanto do módulo de inventário, agora está ordenada por colunas.

10. A configuração de carga horária agora está mais flexível e permite configurações como 24/7.

10. Diversas correções de bugs e aprimoramentos internos.

