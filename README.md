# Content Provenance Demo

A TYPO3 site that shows the [Content Provenance Specification](https://github.com/dkd-dobberkau/content-provenance-spec)
at work: every page carries a signed provenance record, statements in the text
are marked as facts with a source, and the audit endpoint lists all documents.
The site reaches conformance level **L4**.

The demo content is a short history of collecting, from the earliest known
museum at Ur to provenance research today.

> **Status: experimental, pre-release.** The extensions are installed from
> their `main` branches. Nothing here is meant for production use.

## Start

Requirements: [DDEV](https://ddev.com/) and Docker.

```bash
git clone https://github.com/dkd-dobberkau/typo3-provenance-demo.git
cd typo3-provenance-demo
ddev start
ddev launch
```

The first `ddev start` takes a few minutes. It installs TYPO3 14 and the four
provenance extensions, creates the demo pages, generates the site's Ed25519
key and the provenance records. Later starts skip the steps that are done.

The backend is at <https://typo3-provenance-demo.ddev.site/typo3>. User name
and password are written to `.ddev/admin-credentials.txt`.

## What to look at

- **The record panel** next to each page shows the conformance level, the
  signature and the facts on the page. It reads the page's own JSON-LD record
  in `<head>`.
- **Facts** are highlighted in the text. For each one, the JSON-LD record
  states its subject, what it claims, its source and how certain it is.
- **The audit endpoint** lists every document of the site:
  <https://typo3-provenance-demo.ddev.site/.well-known/content-provenance>
- **The site key** used for signatures:
  <https://typo3-provenance-demo.ddev.site/.well-known/content-provenance/keys>

## Verify the signatures

The [conformance suite](https://github.com/dkd-dobberkau/content-provenance-conformance)
checks every signed page independently of TYPO3:

```bash
git clone https://github.com/dkd-dobberkau/content-provenance-conformance.git
cd content-provenance-conformance/reference/python
uv run python verify_site.py --insecure https://typo3-provenance-demo.ddev.site
```

`--insecure` is only needed for DDEV's local certificate.

## Start over

```bash
ddev delete --omit-snapshot --yes
rm -rf vendor public var config/system/settings.php .ddev/admin-credentials.txt
ddev start
```

## What is in this repository

| Path | Content |
| --- | --- |
| `.ddev/config.yaml` | DDEV project; its post-start hook runs `Build/demo-setup.sh` |
| `Build/demo-setup.sh` | First-start setup: Composer, TYPO3, demo content, site key, records |
| `config/sites/main/` | Site configuration with the provenance site sets |
| `config/system/additional.php` | DDEV database and trusted hosts settings |
| `packages/provenance-demo-site/` | Site package: page template, styles, record panel, `provenance-demo:import` command and the demo content |

## The extensions

| Extension | Repository |
| --- | --- |
| `provenance` | [typo3-provenance](https://github.com/dkd-dobberkau/typo3-provenance) (also hosts the manual) |
| `provenance_signing` | [typo3-provenance-signing](https://github.com/dkd-dobberkau/typo3-provenance-signing) |
| `provenance_audit` | [typo3-provenance-audit](https://github.com/dkd-dobberkau/typo3-provenance-audit) |
| `provenance_facts` | [typo3-provenance-facts](https://github.com/dkd-dobberkau/typo3-provenance-facts) |

## Links

- Project site: <https://cms-provenance.org/>
- Specification: <https://github.com/dkd-dobberkau/content-provenance-spec>

## License

GPL-2.0-or-later, see [LICENSE](LICENSE). The demo texts summarise the cited
Wikipedia articles; each fact names its source. The bundled fonts Fraunces,
Inter Tight and JetBrains Mono are licensed under the
[SIL Open Font License 1.1](https://openfontlicense.org/).
