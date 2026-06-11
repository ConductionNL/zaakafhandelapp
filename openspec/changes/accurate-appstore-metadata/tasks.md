# Tasks — accurate-appstore-metadata

## 1. Rewrite appinfo/info.xml

- [ ] 1.1. Replace `<summary>` with a complete one-line English pitch, e.g.
      "Dutch municipal case handling (zaakgericht werken) for Nextcloud —
      cases, tasks, customer contacts and a ZGW API, built on OpenRegister."
      Fix the current truncation ("…made by ConductionN").
- [ ] 1.2. Add `<summary lang="nl">` with the Dutch equivalent (e.g.
      "Zaakgericht werken voor Nextcloud — zaken, taken, klantcontacten en
      een ZGW-API, gebouwd op OpenRegister.").
- [ ] 1.3. Replace the `<description>` CDATA body. Content:
      - one-paragraph product pitch (case workers manage zaken from intake
        through formal closure with zaaktypen, statussen, rollen, besluiten,
        resultaten; taken; klanten/personen/organisaties with
        contactmomenten; in-case berichten with audit trail; personal
        werkvoorraad dashboard; ZGW ZRC/ZTC/DRC/BRC REST API surface);
      - keep the **Requires: OpenRegister** line with its App Store link;
      - feature bullet list mirroring the README Features headings;
      - remove ALL OpenCatalogi text ("Brinning Gateway and Service bus",
        "federated catalogi", "Synchronize your data sources", "Send cloud
        eventt", "Map and translate api calls").
- [ ] 1.4. Add `<description lang="nl">` with the Dutch equivalent of 1.3.
- [ ] 1.5. Delete the line "**System Cron is currently required for this app
      to work**" (no BackgroundJob classes exist under `lib/`).
- [ ] 1.6. Replace the OpenCatalogi links: requirements link
      (`conduction.gitbook.io/opencatalogi-nextcloud`), roadmap
      (`github.com/orgs/OpenCatalogi/projects/1`), bug report and feature
      request (`github.com/OpenCatalogi/.github/issues`) → the
      zaakafhandelapp repository docs/issues URLs (match the existing
      `<website>`/`<bugs>` host).
- [ ] 1.7. Bump `<version>` patch (0.2.3 → 0.2.4) so the corrected metadata
      ships and caches bust.

## 2. Align README.md

- [ ] 2.1. Change "**Message Audit Trail** — Full history of message edits
      with revert capability" → "**Message Audit Trail** — Full history of
      message edits" (revert is unbuilt; file a separate change if it is ever
      wanted).
- [ ] 2.2. Architecture diagram: remove the `C --> E[Elasticsearch]`,
      `A --> G[Nextcloud Activity]` and `H[Cron] -->|background jobs| C`
      nodes/edges; add/keep an OpenRegister node as the storage + search
      backend.
- [ ] 2.3. Requirements table: remove the "Elasticsearch | optional — for
      full-text case search" row.
- [ ] 2.4. Tech Stack table: change the Search row from "SQL ILIKE (default)
      + Elasticsearch (optional)" to search provided via OpenRegister.

## 3. Validation

- [ ] 3.1. `xmllint --noout appinfo/info.xml` and validate against the
      Nextcloud `info.xsd` (the file's declared schema) — must pass with the
      new `lang="nl"` elements.
- [ ] 3.2. Greps come back empty on the promise surface:
      `grep -riE 'gateway|service bus|cloud event|opencatalogi|federated' appinfo/info.xml`
      and `grep -riE 'elasticsearch|revert capability' appinfo/info.xml README.md`
      and `grep -i 'system cron' appinfo/info.xml`.
- [ ] 3.3. Confirm no `BackgroundJob`/Cron classes were added meanwhile
      (`grep -rn 'BackgroundJob' lib/` empty) — otherwise REQ-META-002 needs
      re-evaluation instead of removal.
- [ ] 3.4. Render the README mermaid diagram (GitHub/Codeberg preview) — no
      broken nodes.

## 4. Specs

- [ ] 4.1. Sync `specs/appstore-metadata/spec.md` (4 ADDED requirements) to
      `openspec/specs/appstore-metadata/spec.md` on archive.

## 5. Out of scope (explicit)

- [ ] 5.1. NO changes to `lib/`, `src/`, `appinfo/routes.php`. The 501 ZGW
      stubs are handled by the `zgw-zrc-drc-completion` change; faceted
      case/task search and Activity integration stay unpromised until built.
