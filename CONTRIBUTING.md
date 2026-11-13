# Contributing

Thanks for helping with the demo of the Content Provenance Specification.

## Where things go

- **Specification changes** belong in the [specification repository](https://github.com/dkd-dobberkau/content-provenance-spec).
- **Extension behaviour** (records, signing, facts, audit endpoint) belongs in
  the repository of the extension concerned, see the README.
- **This repository**: the DDEV setup, the site package and the demo content.

## Workflow

1. Open an issue first for anything beyond a small fix.
2. Fork, create a topic branch, keep changes focused.
3. Use [Conventional Commits](https://www.conventionalcommits.org/) for commit
   messages (`feat:`, `fix:`, `docs:` …).
4. Check your change from a cold start: `ddev delete --omit-snapshot --yes`,
   remove `vendor/`, `public/`, `var/` and `config/system/settings.php`, then
   `ddev start`.
5. Every fact in the demo content needs a source that supports it.
6. Update `CHANGELOG.md` under *Unreleased*.

## Licensing of contributions

By contributing you agree that your contribution is licensed under
GPL-2.0-or-later, the license of this repository.
