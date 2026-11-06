# Contexto tecnico do novo frontend WYD CDK

**Base escolhida:** `Backup\Tools\Site 7.54`  
**Destino de integracao:** checkout Git local `Site\`, repositorio/branch
Frontend-WYD-CdK.  
**Estado:** os arquivos da base foram integrados ao checkout `Site`; a arvore
anterior foi preservada em `Backup\Site-live-before-7.54`. O IIS da VPS serve
essa base; em 06/11 foram publicados os ajustes de falha fechada de cadastro e
guildmark, mantendo cópias de rollback em `C:\WYDCDK\Deploy`.

O usuario definiu esta versao como o novo frontend e informa que ela cobre as
funcoes do site live e acrescenta recursos. O trabalho deve partir desta source,
preservar os contratos atuais e trazer as novas integracoes. Nao voltar a tratar
essa escolha como uma comparacao entre dois candidatos.

## Fluxo de integracao

1. Concluido: confirmar `master`, `origin` e working tree limpa antes da troca.
2. Concluido: mover a arvore anterior para `Backup\Site-live-before-7.54` e
   conferir os hashes SHA-256 dos 421 arquivos preservados.
3. Concluido: copiar os 75 arquivos da base 7.54 para `Site`, sem copiar `.git`;
   manter `.gitignore`, `LICENSE`, branch e remote do checkout existente.
4. Parcial: rotas, schema de contas, autenticação e guildmark foram revisados;
   os fluxos atuais usam `OpenAccountDatabase()` e o segredo runtime
   `Server\secrets\site-db.json`. O checkout não mantém credenciais.
5. Concluído para o bloco publicado: PHP 8.5.10/PDO MySQL, sintaxe local,
   quatro regressões de falha fechada, IIS/rotas e probe read-only de login com
   conta sintética inexistente. Sem inserir conta ou alterar schema/dados.
6. Concluído para o bloco publicado: diff revisado, cópias anteriores dos três
   PHPs guardadas na VPS e hashes dos arquivos publicados conferidos. A base
   anterior continua em `C:\WYDCDK\Deploy\site-backup-20261106-eb1832de`.
7. Pendente: comparar downloads, launcher/news/status e completar o E2E de
   cadastro/login com uma conta isolada. Os links finais do cliente ainda
   dependem dos artefatos e URLs da release.

## Mapa inicial de componentes

| Area | Estado |
|---|---|
| Cadastro/conta | Schema e autenticação via IIS verificados; cadastro real não foi executado para evitar escrita na base ativa. |
| Guildmark | Falha fechada, CSRF e regra de líder testados; upload/autorização E2E de conta real pendentes. |
| Ranking/download | Rotas retornam HTTP 200; conferir origem completa, URLs e estados vazios. |
| Launcher/status | `servtest.htm` segue publicado pelo server; validar links e contrato com os artefatos finais. |
| Configuração | IIS usa leitura no segredo compartilhado; segredos permanecem fora de Git e da release. |

As credenciais preexistentes foram preservadas em `Backup\secrets`, fora do Git;
as ACLs da pasta foram restringidas ao usuario local, Administradores e SYSTEM.
Nunca exibir ou sincronizar esses arquivos, nem incluir `Backup` em releases.
Os testes de regressão são locais e no staging isolado. O probe IIS usou apenas
um login inválido aleatório, que fez leitura e não alterou dados.
