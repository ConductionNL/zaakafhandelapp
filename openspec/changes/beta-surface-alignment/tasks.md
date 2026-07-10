# Tasks: beta-surface-alignment

- [x] Fix `appinfo/info.xml` `<licence>` from `agpl` to `EUPL-1.2` (matches
      LICENSE file, README badge, publiccode.yml).
- [x] Add opschorting/verlenging bullet to `appinfo/info.xml` description
      (EN + NL) — real feature, previously undocumented on this surface.
- [x] Rewrite `conduction-website/src/pages/apps/zaakafhandelapp.mdx`: remove
      citizen-portal/DigiD/eIDAS/Calendar/Deck/gateway/TMLO-certification/
      DocuDesk-integration/workflow-engine claims; fix version (v0.2.7) and
      status (Beta) to match info.xml; rebuild FeatureList, RotatingCards,
      WidgetShelf, Showcase and PairRow around the verified feature list.
- [x] Apply the same rewrite to the Dutch product page
      `conduction-website/i18n/nl/docusaurus-plugin-content-pages/apps/zaakafhandelapp.mdx`.
- [x] Remove the fabricated "gateway and service-bus" claim from
      `docs/intro.md` and `docs/FEATURES.md`; replace with the real ZGW REST
      API surface claim; add opschorting/verlenging mention.
- [x] Fix `publiccode.yml`: bump `softwareVersion` to `0.2.7`, remove the
      "integrates with Open Zaak" claim (false — `OpenZakenWidget` is an
      unrelated "open cases" dashboard widget), replace with the real ZGW
      REST API surface claim (EN + NL).
- [x] Confirm `src/manifest.json` nav/menu labels already match the shipped
      controllers — no changes needed (already the source of truth).
- [x] Confirm `img/app.svg` — white-fill 24×24 briefcase glyph — already
      matches brand convention; no change needed.
- [x] Author this change's proposal/tasks/spec documenting the canonical
      feature list and every reconciliation.
