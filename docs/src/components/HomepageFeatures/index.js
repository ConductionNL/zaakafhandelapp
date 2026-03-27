import React from 'react';
import clsx from 'clsx';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: 'Case Management (ZGW)',
    description: (
      <>
        Structured case handling using Dutch government ZGW patterns. Manage case types, metadata, roles, tasks, and the full case lifecycle.
      </>
    ),
  },
  {
    title: 'Gateway & Service Bus',
    description: (
      <>
        Synchronize data sources, send cloud events, and translate API calls between systems. Central integration hub for government workflows.
      </>
    ),
  },
  {
    title: '⚠️ Deprecated',
    description: (
      <>
        This app is no longer maintained. Use <a href="https://procest.app">Procest</a> for process management or <a href="https://pipelinq.app">Pipelinq</a> for pipeline management.
      </>
    ),
  },
];

function Feature({title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
