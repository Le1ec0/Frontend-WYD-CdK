# Contexto tecnico do novo frontend WYD CDK

**Base escolhida:** `Backup\Tools\Site 7.54`  
**Destino de integracao:** checkout Git local `Site\`, repositorio/branch
Frontend-WYD-CdK.  
**Estado:** os arquivos da base foram integrados ao checkout `Site`; a arvore
anterior foi preservada em `Backup\Site-live-before-7.54`. Revisao funcional,
validacao e republicacao continuam pendentes.

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
4. Pendente: comparar rotas, consultas/schema, autenticacao, guildmarks,
   downloads, launcher/news e status do servidor; mapear o que e funcionalidade
   herdada ou nova, sem assumir equivalencia apenas pela descricao da base.
5. Sintaxe PHP validada nos 10 arquivos presentes. Pendente: revisar
   dependencias/runtime e testar fluxos com banco isolado; conferir IIS/URLs
   apenas antes de uma republicacao autorizada.
6. Pendente: revisar diff e rollback. Nao publicar sem pedido explicito.

## Mapa inicial de componentes

| Area | Verificar na integracao |
|---|---|
| Cadastro/conta | hash, sessoes, CSRF, limites e schema `accounts` |
| Guildmark | autorizacao, blob por mundo, validacao de imagem e destinos |
| Ranking/download | origem dos dados, URLs, escaping e estados vazios |
| Launcher/status | `index.html`, `update1.htm`, `servtest.htm` e contrato do cliente |
| Configuracao | paths por ambiente, extensoes PHP, IIS, permissoes e segredos |

As credenciais preexistentes foram preservadas em `Backup\secrets`, fora do Git;
as ACLs da pasta foram restringidas ao usuario local, Administradores e SYSTEM.
Nunca exibir ou sincronizar esses arquivos, nem incluir `Backup` em releases.
Testes de integracao usam ambiente local/sandbox, nao banco nem IIS live.
