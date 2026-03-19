// @ts-check

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Zaak Afhandel App',
  tagline: '⚠️ Deprecated — use Procest or Pipelinq instead',
  url: 'https://zaakafhandelapp.app',
  baseUrl: '/',

  organizationName: 'ConductionNL',
  projectName: 'zaakafhandelapp',
  trailingSlash: false,

  onBrokenLinks: 'warn',
  onBrokenMarkdownLinks: 'warn',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          path: './',
          exclude: ['**/node_modules/**'],
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl:
            'https://github.com/ConductionNL/zaakafhandelapp/tree/main/docs/',
        },
        blog: false,
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      announcementBar: {
        id: 'deprecated',
        content:
          '⚠️ This app is deprecated. Use <a href="https://procest.app"><strong>Procest</strong></a> or <a href="https://pipelinq.app"><strong>Pipelinq</strong></a> instead.',
        backgroundColor: '#fff3cd',
        textColor: '#856404',
        isCloseable: false,
      },
      navbar: {
        title: 'Zaak Afhandel App',
        logo: {
          alt: 'Zaak Afhandel App Logo',
          src: 'img/logo.svg',
        },
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
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Documentation',
                to: '/docs/FEATURES',
              },
            ],
          },
          {
            title: 'Replacements',
            items: [
              {
                label: 'Procest',
                href: 'https://procest.app',
              },
              {
                label: 'Pipelinq',
                href: 'https://pipelinq.app',
              },
            ],
          },
          {
            title: 'Community',
            items: [
              {
                label: 'GitHub',
                href: 'https://github.com/ConductionNL/zaakafhandelapp',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} for <a href="https://openwebconcept.nl">Open Webconcept</a> by <a href="https://conduction.nl">Conduction B.V.</a>`,
      },
      prism: {
        theme: require('prism-react-renderer/themes/github'),
        darkTheme: require('prism-react-renderer/themes/dracula'),
      },
      mermaid: {
        theme: { light: 'default', dark: 'dark' },
      },
    }),
  markdown: {
    mermaid: true,
  },
  themes: ['@docusaurus/theme-mermaid'],
};

module.exports = config;
