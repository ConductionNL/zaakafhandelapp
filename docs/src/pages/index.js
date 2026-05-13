/**
 * Zaak Afhandel App landing page.
 *
 * Composes the brand <DetailHero> + <WidgetShelf> from
 * @conduction/docusaurus-preset/components, mirroring the OpenRegister
 * landing page at openregister.conduction.nl (docs/src/pages/index.js)
 * and the decidesk landing page.
 *
 * Written as .js (not .mdx) because the docs site has the docs plugin
 * pointed at `path: './'`, and an MDX file in src/pages/ trips the
 * MDX-ESM parser even with the docs plugin's `src/**` exclude — a
 * quirk of how mdx-loader's micromark stack reuses parser state across
 * files in this Docusaurus 3 + this preset combination. Authoring the
 * page in JSX keeps the same component composition.
 */

import React from 'react';
import Layout from '@theme/Layout';
import {
  DetailHero,
  WidgetShelf,
  AppMock,
} from '@conduction/docusaurus-preset/components';

/* Suitcase / briefcase glyph — stroke line-icon (the preset forces
   `fill:none; stroke:currentColor` on `.titleIcon svg`, so the filled
   zaakafhandelapp/img/app.svg path can't be reused verbatim). Read it
   as the case file the worker carries from intake to closure. */
const ZAA_ICON = (
  <svg viewBox="0 0 24 24">
    <rect x="3" y="7" width="18" height="13" rx="2" />
    <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
    <path d="M3 12h18" />
    <path d="M12 11v3" />
  </svg>
);

const TAGLINE = (
  <>
    Zaak Afhandel App brings ZGW-aligned case handling into Nextcloud:
    intake to closure for zaken, defined status workflows, the work
    queue, tasks for staff and citizens, attached documents, and a
    record of every decision (besluit) — no separate case system, no
    second login.
  </>
);

function OpenCasesPanel() {
  const rows = [
    { tone: 'var(--c-cobalt-300)', stage: 'INTAKE' },
    { tone: 'var(--c-lavender-300)', stage: 'IN BEH.' },
    { tone: 'var(--c-mint-500)', stage: 'IN BEH.' },
    { tone: 'var(--c-orange-knvb)', stage: 'WACHT' },
  ];
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      {rows.map((row, i) => (
        <div
          key={i}
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: 8,
            padding: '4px 0',
            borderBottom:
              i < rows.length - 1 ? '1px solid var(--c-cobalt-50)' : 'none',
          }}
        >
          <span
            style={{
              width: 12,
              height: 14,
              clipPath: 'var(--hex-pointy-top)',
              background: row.tone,
              flexShrink: 0,
            }}
          />
          <div
            style={{
              flex: 1,
              display: 'flex',
              flexDirection: 'column',
              gap: 2,
            }}
          >
            <div
              style={{
                height: 4,
                width: '70%',
                background: 'var(--c-cobalt-700)',
                borderRadius: 1,
              }}
            />
            <div
              style={{
                height: 3,
                width: '50%',
                background: 'var(--c-cobalt-200)',
                borderRadius: 1,
              }}
            />
          </div>
          <div
            style={{
              fontFamily: 'var(--conduction-typography-font-family-code)',
              fontSize: 8,
              letterSpacing: '0.05em',
              textTransform: 'uppercase',
              color: 'var(--c-cobalt-500)',
            }}
          >
            {row.stage}
          </div>
        </div>
      ))}
    </div>
  );
}

function MyTasksPanel() {
  const rows = [
    { tone: 'var(--c-orange-knvb)', due: 'te laat' },
    { tone: 'var(--c-mint-500)', due: 'vandaag' },
    { tone: 'var(--c-lavender-300)', due: 'vr' },
    { tone: 'var(--c-cobalt-300)', due: 'volg. wk' },
    { tone: 'var(--c-forest-300)', due: 'volg. wk' },
  ];
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      {rows.map((row, i) => (
        <div
          key={i}
          style={{ display: 'flex', alignItems: 'center', gap: 8 }}
        >
          <span
            style={{
              width: 18,
              height: 18,
              borderRadius: 2,
              background: row.tone,
              flexShrink: 0,
            }}
          />
          <div
            style={{
              flex: 1,
              display: 'flex',
              flexDirection: 'column',
              gap: 3,
            }}
          >
            <div
              style={{
                height: 4,
                width: '70%',
                background: 'var(--c-cobalt-700)',
                borderRadius: 1,
              }}
            />
            <div
              style={{
                height: 3,
                width: '50%',
                background: 'var(--c-cobalt-200)',
                borderRadius: 1,
              }}
            />
          </div>
          <div
            style={{
              fontFamily: 'var(--conduction-typography-font-family-code)',
              fontSize: 8,
              letterSpacing: '0.05em',
              textTransform: 'uppercase',
              color: 'var(--c-cobalt-500)',
            }}
          >
            {row.due}
          </div>
        </div>
      ))}
    </div>
  );
}

function RecentDecisionsPanel() {
  const rows = [
    { tone: 'var(--c-mint-500)', label: 'TOEGEKEND', w: '80%' },
    { tone: 'var(--c-cobalt-300)', label: 'CONCEPT', w: '60%' },
    { tone: 'var(--c-orange-knvb)', label: 'AFGEWEZEN', w: '50%' },
    { tone: 'var(--c-lavender-300)', label: 'TOEGEKEND', w: '70%' },
  ];
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      {rows.map((row, i) => (
        <div
          key={i}
          style={{ display: 'flex', alignItems: 'center', gap: 8 }}
        >
          <span
            style={{
              width: 14,
              height: 16,
              clipPath: 'var(--hex-pointy-top)',
              background: row.tone,
              flexShrink: 0,
            }}
          />
          <div
            style={{
              fontFamily: 'var(--conduction-typography-font-family-code)',
              fontSize: 8,
              fontWeight: 700,
              letterSpacing: '0.05em',
              color: 'var(--c-cobalt-700)',
              minWidth: 64,
            }}
          >
            {row.label}
          </div>
          <div
            style={{
              flex: 1,
              height: 6,
              background: 'var(--c-cobalt-50)',
              borderRadius: 1,
              overflow: 'hidden',
            }}
          >
            <div
              style={{
                height: '100%',
                width: row.w,
                background: 'var(--c-cobalt-300)',
                borderRadius: 1,
              }}
            />
          </div>
        </div>
      ))}
    </div>
  );
}

const WIDGETS = [
  {
    title: 'Open zaken',
    desc: 'The cases still in flight — by status, from intake through "in behandeling" to closure. The work queue you already open, with the next case one click away.',
    panel: <OpenCasesPanel />,
  },
  {
    title: 'My tasks',
    desc: 'Every task assigned to you out of a case, sorted by due date — work items for staff and follow-ups for citizens. Follow each one through to completion.',
    panel: <MyTasksPanel />,
  },
  {
    title: 'Recent besluiten',
    desc: 'The latest formal decisions and case outcomes: granted, draft, refused. A complete, audited record of who decided what, and why.',
    panel: <RecentDecisionsPanel />,
  },
];

export default function Home() {
  return (
    <Layout
      title="Zaak Afhandel App"
      description="Zaak Afhandel App brings ZGW-aligned case handling into Nextcloud — intake to closure for zaken, defined status workflows, the work queue, tasks, attached documents, and a record of every decision."
    >
      <main className="marketing-page">
        <DetailHero
          background="cobalt"
          appId="zaakafhandelapp"
          /* status is kept explicit because 'Deprecated' is a
             lifecycle decision, not a SemVer-derivable value.
             deriveStability() in preset 2.10+ only maps SemVer
             pre-release tags (Stable / Beta / RC / Alpha); for
             archived or sunset apps the site keeps the override.
             version is dropped — preset auto-derives from
             appinfo/info.xml so the chrome pill and the badge row
             stay in sync. */
          status={{ label: 'Deprecated', color: 'var(--c-cobalt-400)' }}
          locales="NL · EN"
          title="Zaak Afhandel App"
          tagline={TAGLINE}
          primaryCta={{
            label: 'Install from app store',
            href: 'https://apps.nextcloud.com/apps/zaakafhandelapp',
            tone: 'orange',
          }}
          secondaryCta={{ label: 'Read the docs', href: '/docs/intro' }}
          tertiaryCta={{
            label: 'View on GitHub',
            href: 'https://github.com/ConductionNL/zaakafhandelapp',
          }}
          iconColor="var(--c-orange-knvb)"
          icon={ZAA_ICON}
          illustration={<AppMock app="zaakafhandelapp" />}
        />

        <WidgetShelf
          eyebrow="Widgets we ship"
          title="The casework follows you to the dashboard."
          lede="Install Zaak Afhandel App and the home screen surfaces the cases still open, the tasks assigned to you, and the decisions recently taken."
          widgets={WIDGETS}
        />
      </main>
    </Layout>
  );
}
