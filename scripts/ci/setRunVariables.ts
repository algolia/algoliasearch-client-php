/* eslint-disable no-console */
import crypto from 'crypto';

import { hashElement } from 'folder-hash';

import { toAbsolutePath } from '../common';

import { getNbGitDiff } from './utils';

const JS_CLIENT_FOLDER = 'clients/algoliasearch-client-javascript';

/**
 * Exhaustive list of output variables to use in the CI.
 *
 * Those variables are used to determine if jobs should run, based on the changes
 * made in their respective `path`s.
 *
 * Negative paths should start with `:!`.
 *
 * The variable will be accessible in the CI via `steps.diff.outputs.<name>`.
 */
const VARIABLES_TO_CHECK = [
  {
    name: 'GITHUB_ACTIONS_CHANGED',
    path: ['.github/actions', '.github/workflows', '.github/.cache_version'],
  },
  {
    name: 'SPECS_CHANGED',
    path: ['specs', ':!specs/bundled'],
  },
  {
    name: 'COMMON_SPECS_CHANGED',
    path: ['specs/common'],
  },
  {
    name: 'TESTS_CHANGED',
    path: ['tests'],
  },
  {
    name: 'SCRIPTS_CHANGED',
    path: ['scripts'],
  },
  {
    name: 'GENERATORS_CHANGED',
    path: ['generators'],
  },
  {
    name: 'JS_CLIENT_CHANGED',
    path: [
      JS_CLIENT_FOLDER,
      `:!${JS_CLIENT_FOLDER}/.github`,
      `:!${JS_CLIENT_FOLDER}/README.md`,
    ],
  },
  {
    name: 'JS_ALGOLIASEARCH_CHANGED',
    path: [
      `${JS_CLIENT_FOLDER}/packages/algoliasearch`,
      `${JS_CLIENT_FOLDER}/packages/client-search`,
      `${JS_CLIENT_FOLDER}/packages/client-analytics`,
      `${JS_CLIENT_FOLDER}/packages/client-personalization`,
    ],
  },
  {
    name: 'JS_COMMON_CHANGED',
    path: [
      `${JS_CLIENT_FOLDER}/packages/client-common`,
      `${JS_CLIENT_FOLDER}/packages/requester-browser-xhr`,
      `${JS_CLIENT_FOLDER}/packages/requester-node-http`,
    ],
  },
  {
    name: 'JS_COMMON_TESTS_CHANGED',
    path: [`${JS_CLIENT_FOLDER}/packages/client-common/src/__tests__`],
  },
  {
    name: 'JS_TEMPLATE_CHANGED',
    path: ['templates/javascript'],
  },
  {
    name: 'JAVA_CLIENT_CHANGED',
    path: ['clients/algoliasearch-client-java-2'],
  },
  {
    name: 'JAVA_TEMPLATE_CHANGED',
    path: ['templates/java'],
  },
  {
    name: 'PHP_CLIENT_CHANGED',
    path: ['clients/algoliasearch-client-php'],
  },
  {
    name: 'PHP_TEMPLATE_CHANGED',
    path: ['templates/php'],
  },
];

async function computeCommonHash(): Promise<string> {
  const hashGA = await hashElement(toAbsolutePath('.github'), {
    encoding: 'hex',
    folders: { exclude: ['ISSUE_TEMPLATE'] },
    files: { include: ['*.yml', '.cache_version'] },
  });
  const hashScripts = await hashElement(toAbsolutePath('scripts'), {
    encoding: 'hex',
    folders: { exclude: ['docker', '__tests__'] },
  });
  const hashConfig = await hashElement(toAbsolutePath('.'), {
    encoding: 'hex',
    folders: { include: ['config'] },
    files: { include: ['openapitools.json', 'clients.config.json'] },
  });

  return crypto
    .createHash('sha256')
    .update(`${hashGA.hash}-${hashScripts.hash}-${hashConfig.hash}`)
    .digest('hex');
}

/**
 * Outputs variables used in the CI to determine if a job should run.
 */
async function setRunVariables({
  originBranch,
}: {
  originBranch: string;
}): Promise<void> {
  console.log(`Checking diff between ${originBranch} and HEAD`);

  for (const check of VARIABLES_TO_CHECK) {
    const diff = await getNbGitDiff({
      branch: originBranch,
      path: check.path.join(' '),
    });

    console.log(`Found ${diff} changes for '${check.name}'`);
    console.log(`::set-output name=${check.name}::${diff}`);
  }

  console.log(`::set-output name=COMMON_HASH::${await computeCommonHash()}`);

  console.log(`::set-output name=ORIGIN_BRANCH::${originBranch}`);
}

if (require.main === module) {
  const [originBranch] = process.argv.slice(2);

  if (!originBranch) {
    throw new Error(
      `Unable to retrieve the origin branch: ${JSON.stringify(originBranch)}`
    );
  }

  setRunVariables({ originBranch });
}
