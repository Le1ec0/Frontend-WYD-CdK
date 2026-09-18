# Banco do Donate Shop

`001_donate_shop.sql` define o contrato MariaDB usado pelo site e pela futura camada de persistência do servidor:

- `accounts.donate`: saldo de moeda online por conta;
- `accounts.pix_key`: chave curta enviada pelo wire legado quando aplicável;
- `donate_shop_catalog`: catálogo autoritativo com coordenadas `3 x 5 x 15`, item, preço, estoque e ativação.

O cadastro atual do site usa `accounts.account_name`. A implementação C++ de referência consulta nomes de coluna diferentes em outra instalação; por isso o port não reutiliza SQL nem credenciais da release sem uma confirmação do schema efetivo.

Antes de aplicar:

1. revisar a tabela `accounts` existente;
2. executar em banco local/staging;
3. preencher as 225 posições do catálogo;
4. validar leitura, compra, débito e rollback com conta de teste;
5. somente depois preparar o deploy autorizado.

## Configuracao local do servidor

`mariadb-config.example.json` e apenas um modelo sem credenciais reais. Copie-o
para um caminho fora do repositorio, preencha os valores localmente e inicie o
servidor com:

```text
WydCdk.Server.exe --accounts-root <legacy-account-directory> --donate-db-config <mariadb-config.json>
```

Com `--donate-db-config`, o servidor le o saldo e o catalogo da MariaDB e grava
o debito de compra dentro de uma transacao serializavel. Nao combine essa opcao
com `--donate-catalog`; o catalogo deve vir de uma unica fonte. Sem a opcao, o
comportamento continua usando o arquivo legado e o CSV local.

O projeto ja referencia o provider `MySqlConnector` e o inclui no build do
servidor. A conexao ainda nao foi executada porque nao ha MariaDB local nem
Docker daemon disponivel nesta maquina; a proxima validacao deve ser contra uma
MariaDB local/staging autorizada.

`donate-shop-review.csv` e somente uma lista de revisao humana, baseada nos IDs
existentes na `ItemList.csv` de referencia. Ela nao e lida pelo servidor e nao
libera nenhum item. O catalogo ativo so deve ser preenchido depois de confirmar
o uso de cada gema no fluxo Ancient e os precos/estoques.

## Persistencia de personagens C#

`002_world_character_state.sql` cria o armazenamento sem arquivos legados:

- `wyd_world_account_state`: blob de 7945 bytes por conta e `world_key` (`UP`/`PVP`), mantendo os offsets confirmados de `STRUCT_ACCOUNTFILE`;
- `wyd_character_names`: reserva de nomes por mundo;
- `wyd_account_security`: PIN numerico de seis bytes por conta.

O blob e apenas armazenamento: o listener continua montando as mesmas mensagens
com `STRUCT_MOB`, `ShortSkill`, `affect` e `STRUCT_MOBEXTRA`. O saldo Donate
continua em `accounts.donate`. Sem `--accounts-root`, o servidor usa essas tabelas
e nao acessa arquivos de personagens.

`mariadb-account-config.example.json` usa o mesmo formato, mas liga a
autenticacao do jogo ao cadastro `accounts`: o servidor consulta
`account_name`, `password_hash` e `password_scheme`, verificando os hashes
BCrypt gerados pelo `password_hash()` do site. Use-o com:

```text
WydCdk.Server.Core.exe --world UP --mariadb-account-config <mariadb-account-config.json>
```

MariaDB e a autoridade da senha e do estado de personagem. Uma conta que ainda
nao tenha blob para o `world_key` selecionado autentica, mas recebe uma selecao
vazia ate criar o primeiro personagem.
