/**
 * ZaakAfhandelApp landing page.
 *
 * Composes the brand <DetailHero> + <WidgetShelf> from
 * @conduction/docusaurus-preset/components, mirroring the connext
 * detail page at sites/www/src/pages/apps/zaakafhandelapp.mdx.
 *
 * Authored as .js (not .mdx) for parser stability with the brand
 * preset; see mydash/docs/src/pages/index.js for the same
 * convention.
 */

import React from 'react';
import Layout from '@theme/Layout';
import {
  DetailHero,
  WidgetShelf,
  AppMock,
} from '@conduction/docusaurus-preset/components';

/* Case-folder glyph from sites/www/src/pages/apps/zaakafhandelapp.mdx
   (nested rect inside an outer rounded rect). */
const ZAAKAFHANDEL_ICON = (
  <svg viewBox="0 0 24 24">
    <rect x="4" y="4" width="16" height="16" rx="2" />
    <path d="M9 9h6v6H9z" />
  </svg>
);

const TAGLINE = (
  <>
    The citizen-facing case-status portal on your{' '}
    <span className="next-blue">Nextcloud</span>. Citizens follow their cases,
    request status, upload documents. ZGW APIs, archief-koppelvlakken, audit
    trail. No separate SaaS, no second login for case-workers.
  </>
);

function OpenCasesPanel() {
  const rows = [
    { tone: 'var(--c-orange-knvb)', l1: '60%', l2: '40%', av: 'var(--c-mint-300)' },
    { tone: 'var(--c-lavender-300)', l1: '70%', l2: '50%', av: 'var(--c-lavender-300)' },
    { tone: 'var(--c-red-vermillion)', l1: '55%', l2: '45%', av: 'var(--c-orange-knvb)' },
    { tone: 'var(--c-lavender-300)', l1: '65%', l2: '35%', av: 'var(--c-mint-300)' },
    { tone: 'var(--c-lavender-300)', l1: '75%', l2: '50%', av: 'var(--c-forest-300)' },
  ];
  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        gap: 6,
        fontFamily: 'var(--conduction-typography-font-family-body)',
      }}
    >
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
              flex: 1,
              display: 'flex',
              flexDirection: 'column',
              gap: 3,
            }}
          >
            <div
              style={{
                height: 5,
                width: row.l1,
                background: 'var(--c-cobalt-700)',
                borderRadius: 1,
              }}
            />
            <div
              style={{
                height: 3,
                width: row.l2,
                background: 'var(--c-cobalt-200)',
                borderRadius: 1,
              }}
            />
          </div>
          <span
            style={{
              width: 18,
              height: 18,
              borderRadius: '50%',
              background: row.av,
              flexShrink: 0,
            }}
          />
        </div>
      ))}
    </div>
  );
}

function CitizenMessagesPanel() {
  const rows = [
    { tone: 'var(--c-mint-500)', stage: 'unread' },
    { tone: 'var(--c-cobalt-300)', stage: 'replied' },
    { tone: 'var(--c-mint-500)', stage: 'unread' },
    { tone: 'var(--c-cobalt-300)', stage: 'replied' },
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
              width: 10,
              height: 11,
              clipPath: 'var(--hex-pointy-top)',
              background: row.tone,
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

function DeadlinesPanel() {
  const rows = [
    { w: '70%', accent: true },
    { w: '60%' },
    { w: '55%' },
    { w: '45%' },
  ];
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <div
        style={{
          display: 'flex',
          alignItems: 'baseline',
          gap: 6,
          marginBottom: 4,
        }}
      >
        <div
          style={{
            fontFamily: 'var(--conduction-typography-font-family-code)',
            fontSize: 28,
            fontWeight: 700,
            color: 'var(--c-orange-knvb)',
          }}
        >
          4
        </div>
        <div
          style={{
            fontFamily: 'var(--conduction-typography-font-family-code)',
            fontSize: 11,
            letterSpacing: '0.06em',
            textTransform: 'uppercase',
            color: 'var(--c-cobalt-400)',
          }}
        >
          this week
        </div>
      </div>
      {rows.map((row, i) => (
        <div
          key={i}
          style={{ display: 'flex', alignItems: 'center', gap: 6 }}
        >
          <span
            style={{
              width: 8,
              height: 9,
              clipPath: 'var(--hex-pointy-top)',
              background: row.accent
                ? 'var(--c-orange-knvb)'
                : 'var(--c-cobalt-300)',
            }}
          />
          <div
            style={{
              flex: 1,
              height: 4,
              background: 'var(--c-cobalt-200)',
              borderRadius: 1,
              width: row.w,
            }}
          />
          <div
            style={{
              height: 4,
              width: 30,
              background: 'var(--c-cobalt-100)',
              borderRadius: 1,
            }}
          />
        </div>
      ))}
    </div>
  );
}

const WIDGETS = [
  {
    title: 'My open cases',
    desc: 'Active cases for the logged-in case-worker. Late ones in red, the next stage one click away.',
    panel: <OpenCasesPanel />,
  },
  {
    title: 'Citizen messages',
    desc: 'Inbound messages and document uploads from the citizen portal. Each linked to its case.',
    panel: <CitizenMessagesPanel />,
  },
  {
    title: 'Deadlines this week',
    desc: 'Statutory deadlines across the queue. Pre-warning before the day, escalation after.',
    panel: <DeadlinesPanel />,
  },
];

export default function Home() {
  return (
    <Layout
      title="ZaakAfhandelApp"
      description="The citizen-facing case-status portal on your Nextcloud. ZGW APIs, archief-koppelvlakken, audit trail. No separate SaaS, no second login for case-workers."
    >
      <main className="marketing-page">
        <DetailHero
          appId="zaakafhandelapp"
          status={{ label: 'Stable', color: 'var(--c-mint-500)' }}
          version="v2.0"
          locales="NL · EN"
          title="ZaakAfhandelApp"
          tagline={TAGLINE}
          primaryCta={{
            label: 'Install from app store',
            href: 'https://apps.nextcloud.com/apps/zaakafhandelapp',
            tone: 'orange',
          }}
          secondaryCta={{ label: 'Read the docs', href: '/docs/FEATURES' }}
          tertiaryCta={{
            label: 'View on GitHub',
            href: 'https://github.com/ConductionNL/zaakafhandelapp',
          }}
          iconColor="var(--c-orange-knvb)"
          icon={ZAAKAFHANDEL_ICON}
          illustration={<AppMock app="zaakafhandelapp" />}
        />

        <WidgetShelf
          eyebrow="Widgets we ship"
          title="The case-worker's morning view."
          lede="Install ZaakAfhandelApp and these widgets show up on every case-worker's dashboard. Open cases first, citizen messages next, deadlines below."
          widgets={WIDGETS}
        />
      </main>
    </Layout>
  );
}
