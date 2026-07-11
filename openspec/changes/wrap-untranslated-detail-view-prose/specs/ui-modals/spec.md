# ui-modals — Translatable Prose Delta

**Spec refs**: `ui-modals`, hydra ADR-007 (i18n — English source, `t()` for
all user-facing strings)

## ADDED Requirements

### Requirement: User-facing prose is translatable (REQ-007)

Every user-facing string rendered by a resource modal — labels, empty-state messages, tooltips (including tooltips on icon-only buttons, which double as the button's only visible label), and disabled-action explanations — MUST be sourced through `t('zaakafhandelapp', '<English key>')` (or `n()` for pluralised text), with the English string as the translation key (ADR-007) and a matching Dutch value present in `l10n/nl.json`.
Hardcoding Dutch (or any other non-English) literal text directly into a modal's template is a violation, even when `l10n/en.json`/`l10n/nl.json` otherwise report full key parity — parity checks cannot detect prose that was never wrapped in a translation call in the first place.

#### Scenario: Modal prose follows the selected locale

- **GIVEN** the user's Nextcloud language is set to a non-Dutch supported
  locale (e.g. English)
- **WHEN** the user opens a resource modal (e.g. the contact-moment form)
- **THEN** every label, empty-state message, tooltip and disabled-action
  explanation in the modal renders in that locale, not unconditional Dutch

#### Scenario: Icon-only button tooltip is a translation call

- **GIVEN** a modal renders an icon-only button whose only visible label is
  its `v-tooltip`
- **WHEN** the tooltip text is inspected
- **THEN** it is a `t('zaakafhandelapp', '...')` call, not a raw string
  literal
