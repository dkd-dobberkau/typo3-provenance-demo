# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- DDEV project that installs itself on the first `ddev start`: TYPO3 14, the
  four provenance extensions, demo content, a site key and provenance records.
- Site package `provenance_demo_site`: page template in the look of
  cms-provenance.org with a record panel (conformance level, signature,
  facts) read from the page's own JSON-LD.
- Demo content about the history of collecting: five pages, ten facts, each
  checked against its cited source.
- `provenance-demo:import` command creating the page tree through DataHandler.
- CI: cold start in DDEV, then `verify_site.py` from the conformance suite
  must report every page as valid; Trivy scan.
