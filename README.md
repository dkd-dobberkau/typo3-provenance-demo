# Content Provenance Demo

A TYPO3 site that shows the [Content Provenance Specification](https://github.com/dkd-dobberkau/content-provenance-spec)
at work: every page carries a signed provenance record, statements in the text
are marked as facts with a source, and the audit endpoint lists all documents.
The site reaches conformance level **L4**.

The demo content is a short history of collecting, from the earliest known
museum at Ur to provenance research today. Every page has a complete record:
an author with a public profile (the page *Editorial team*) and the Wikipedia
articles it cites as sources.

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

The first `ddev start` takes a few minutes. It installs TYPO3 14 and the five
provenance extensions, creates the demo pages, generates the site's Ed25519
key and the provenance records. Later starts skip the steps that are done.

The backend is at <https://typo3-provenance-demo.ddev.site/typo3>. User name
and password are shown at the end of `ddev start` and written to
`.ddev/admin-credentials.txt` (`cat .ddev/admin-credentials.txt`). The
password is generated when the demo is installed, so after
[Start over](#start-over) there is a new one.

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
- **The XML sitemap** and the canonical URL of each page, which level L1
  requires: <https://typo3-provenance-demo.ddev.site/sitemap.xml>
- **MX meta tags** (`<meta name="mx:…">`) in the head of every page, for
  the Machine Experience vocabulary of The Gathering. They say what the
  provenance record says; `check_mx.py` in the conformance suite shows the
  MX level each page reaches.
- **The backend module Content > Provenance**: "Pages" shows the level of
  each page's record and what it lacks, "Facts" lists all facts with search
  and filters.
- **Facts in the backend**: open a text element, select words in the rich
  text editor and press **Fact**. The dialog can look the fact up in
  Wikidata (`provenance_wikidata`) and fill subject, predicate, value and
  evidence from a statement.

## Verify the signatures

The [conformance suite](https://github.com/dkd-dobberkau/content-provenance-conformance)
checks every signed page independently of TYPO3:

```bash
git clone https://github.com/dkd-dobberkau/content-provenance-conformance.git
cd content-provenance-conformance/reference/python
uv run python verify_site.py --insecure https://typo3-provenance-demo.ddev.site
```

`--insecure` is only needed for DDEV's local certificate. `check_site.py`
from the same directory reports the level each page reaches, from L1 to L4;
`check_endpoint.py` and `check_facts.py` give the details of the audit
endpoint and the facts. All take the same arguments.

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
| `provenance_wikidata` | [typo3-provenance-wikidata](https://github.com/dkd-dobberkau/typo3-provenance-wikidata) (editorial aid, optional) |

## Links

- Project site: <https://cms-provenance.org/>
- Specification: <https://github.com/dkd-dobberkau/content-provenance-spec>

## License

GPL-2.0-or-later, see [LICENSE](LICENSE). The demo texts summarise the cited
Wikipedia articles; each fact names its source. The bundled fonts Fraunces,
Inter Tight and JetBrains Mono are licensed under the
[SIL Open Font License 1.1](https://openfontlicense.org/).
