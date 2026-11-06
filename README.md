# WYD CDK Site

`Site/` is the active frontend checkout (`Frontend-WYD-CdK`, branch `master`).
It now contains the user-selected Site base, adapted for the 7.69/7670 release,
copied from
`Backup/Tools/Site 7.54`; the previous Site tree is preserved at
`Backup/Site-live-before-7.54`. The user identifies 7.54 as containing the live
site's functionality and more; functional equivalence is still to be reviewed.

The `.git` metadata, remote, and branch were preserved. The client release is
the locally built 7.69 source with `ClientVersion=7670`; the client archive is
published separately from this Git repository because it is too large for the
frontend checkout.

## Local development

With PHP installed, run the development server against this active checkout:

```powershell
php -S 127.0.0.1:8080 -t "D:\Jogos\WYD CDK\Site"
```

`lib/db.php` accepts `WYD_SITE_DB_SECRET` first, then the runtime
`Server/secrets/site-db.json`, then the local-only `Backup/secrets/site-db.json`
fallback. Never copy `Backup/` to a release or remote host. Do not use live
credentials for local tests.

## Runtime behavior and validation

Account creation, password changes, and guildmark login/save require the
configured MariaDB database. They fail closed when the database configuration
or connection is unavailable. Guildmark saves also require the selected guild
to appear in the authenticated account's world state; an empty leader list does
not grant access.

Run the database-unavailable regressions with:

```powershell
php tests/database-unavailable-fail-closed.php
```

Validation for the current Site change: PHP syntax checks passed for all 11 PHP
files, and the four database-unavailable/leader-policy regressions passed both
locally and in the isolated VPS staging copy. The VPS uses PHP 8.5.10 with
`pdo_mysql`; read-only schema checks confirmed the columns required by account
registration, login, password change, and world-state guild lookup. The live
IIS routes returned HTTP 200, and a synthetic nonexistent-account login reached
MariaDB and received the normal invalid-credentials response. The IIS app pool
has read-only access to the same runtime secret file used by the game server.
No live database rows or schema were changed. The donate-shop migration remains
separate and was not applied.

The launcher client download links still need release artifacts and verified
URLs before being advertised as a public client release. See
[CONTEXTO_TECNICO.md](CONTEXTO_TECNICO.md) for the module's integration
inventory and validation criteria, and the root [`AGENTS.md`](../AGENTS.md),
[`BACKLOG.md`](../BACKLOG.md), and [`PROGRESSO.md`](../PROGRESSO.md) for
project-level direction.
