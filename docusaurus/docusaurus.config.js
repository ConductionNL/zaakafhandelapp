// @ts-check

/**
 * ZaakAfhandelApp documentation site.
 *
 * Built on @conduction/docusaurus-preset for brand defaults (tokens,
 * theme swizzles for Navbar / Footer, KvK / BTW copyright, canal
 * footer). Site-specific overrides — locales, sidebar path, mermaid
 * theme, custom prism themes, ZaakAfhandelApp-only navbar items —
 * are passed through createConfig() opts.
 *
 * The repo splits config (here, under docusaurus/) from content
 * (under ../docs), so the preset's default presets[] block is
 * overridden to keep docs.path pointing at '../docs'.
 */

const { createConfig, baseFooterLinks } = require('@conduction/docusaurus-preset');

/* createConfig replaces themes wholesale when `themes:` is passed, so
   we re-include the brand theme plugin alongside @docusaurus/theme-mermaid.
   Without the brand theme entry the Navbar/Footer swizzles and
   brand.css auto-load would silently drop. */
const BRAND_THEME = require.resolve('@conduction/docusaurus-preset/theme');

const config = createConfig({
  title: 'ZaakAfhandelApp',
  tagline: 'The citizen-facing case-status portal on your Nextcloud. ZGW APIs, archief-koppelvlakken, audit trail.',
  url: 'https://zaakafhandelapp.conduction.nl',
  baseUrl: '/',

  organizationName: 'ConductionNL',
  projectName: 'zaakafhandelapp',
  trailingSlash: false,

  onBrokenLinks: 'warn',
  onBrokenMarkdownLinks: 'warn',

  /* English-only for now. NL/DE/FR will be added once translated
     markdown is in place; the brand preset's default i18n block
     (nl/en/de/fr) is replaced wholesale here. */
  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
    localeConfigs: {
      en: { label: 'English' },
    },
  },

  /* Content lives at ../docs (sibling of this docusaurus/ folder),
     not under a co-located docs/ subfolder, so we override the
     preset's default presets[] block to point docs.path at '../docs'.
     customCss carries ZaakAfhandelApp-specific CSS only — brand
     tokens and the theme swizzles are auto-loaded by the brand theme
     entry in `themes:` below. */
  presets: [
    [
      'classic',
      {
        docs: {
          path: '../docs',
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl:
            'https://github.com/ConductionNL/zaakafhandelapp/tree/development/docs/',
        },
        blog: false,
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],

  themes: [BRAND_THEME, '@docusaurus/theme-mermaid'],

  /* Brand navbar provides locale dropdown + GitHub by default; we
     replace items[] with ZaakAfhandelApp's own (Documentation
     sidebar link, project GitHub link). Object.assign in
     createConfig is shallow, so items: replaces wholesale. */
  navbar: {
    items: [
      {
        type: 'docSidebar',
        sidebarId: 'tutorialSidebar',
        position: 'left',
        label: 'Documentation',
      },
      {
        href: 'https://github.com/ConductionNL/zaakafhandelapp',
        label: 'GitHub',
        position: 'right',
      },
    ],
  },

  /* Per-property footer override (preset 1.2.0+): pass `links` only
     so the brand `style: 'dark'` and the brand KvK/BTW/IBAN/address
     copyright string both inherit unchanged. Single column: the
     brand "Conduction" anchor. */
  footer: {
    links: [
      ...baseFooterLinks().filter((column) => column.title === 'Conduction'),
    ],
  },

  /* Drop the canal-footer's boat-sinking + kade-cyclist mini-games
     on this product-page footer (preset 1.3.0+). The static skyline
     + canal decoration are kept; the interactive layer goes away. */
  minigames: false,

  /* themeConfig is shallow-merged into the preset's defaults
     (colorMode + navbar + footer). prism + mermaid land alongside. */
  themeConfig: {
    prism: {
      theme: require('prism-react-renderer/themes/github'),
      darkTheme: require('prism-react-renderer/themes/dracula'),
    },
    mermaid: {
      theme: { light: 'default', dark: 'dark' },
    },
  },
});

/* createConfig doesn't pass-through arbitrary top-level fields;
   assign markdown directly so it makes it into the final
   Docusaurus config. */
config.markdown = {
  mermaid: true,
};

module.exports = config;
