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

## Current gate

PHP syntax passed for all 10 PHP files. Route/database-contract comparison,
dependency review, and isolated-database flow tests remain. This documentation
does not authorize IIS/FTP publication or changes to the live site. See
[CONTEXTO_TECNICO.md](CONTEXTO_TECNICO.md) for the module's
integration inventory and validation criteria, and the root
[`AGENTS.md`](../AGENTS.md), [`BACKLOG.md`](../BACKLOG.md), and
[`PROGRESSO.md`](../PROGRESSO.md) for project-level direction.
