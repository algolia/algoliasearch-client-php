import fsp from 'fs/promises';

import yaml from 'js-yaml';

import {
  BUNDLE_WITH_DOC,
  checkForCache,
  exists,
  run,
  toAbsolutePath,
} from './common';
import { createSpinner } from './oraLog';
import type { Spec } from './types';

const ALGOLIASEARCH_LITE_OPERATIONS = [
  'search',
  'multipleQueries',
  'searchForFacetValues',
  'post',
];

async function propagateTagsToOperations({
  bundledPath,
  withDoc,
  clientName,
  alias,
}: {
  bundledPath: string;
  withDoc: boolean;
  clientName: string;
  alias?: string;
}): Promise<void> {
  if (!(await exists(bundledPath))) {
    throw new Error(`Bundled file not found ${bundledPath}.`);
  }

  const bundledSpec = yaml.load(
    await fsp.readFile(bundledPath, 'utf8')
  ) as Spec;

  let bundledDocSpec: Spec | undefined;
  if (withDoc) {
    bundledDocSpec = yaml.load(await fsp.readFile(bundledPath, 'utf8')) as Spec;
  }
  const tagsDefinitions = bundledSpec.tags;

  for (const [pathKey, pathMethods] of Object.entries(bundledSpec.paths)) {
    for (const [method, specMethod] of Object.entries(pathMethods)) {
      // In the main bundle we need to have only the clientName
      // because open-api-generator will use this to determine the name of the client
      specMethod.tags = [clientName];

      if (
        !withDoc ||
        !bundledDocSpec ||
        !bundledDocSpec.paths[pathKey][method].tags
      ) {
        continue;
      }

      // Checks that specified tags are well defined at root level
      for (const tag of bundledDocSpec.paths[pathKey][method].tags) {
        if (tag === clientName || (alias && tag === alias)) {
          return;
        }

        const tagExists = tagsDefinitions
          ? tagsDefinitions.find((t) => t.name === tag)
          : null;
        if (!tagExists) {
          throw new Error(
            `Tag "${tag}" in "client[${clientName}] -> operation[${specMethod.operationId}]" is not defined`
          );
        }
      }
    }
  }

  await fsp.writeFile(
    bundledPath,
    yaml.dump(bundledSpec, {
      noRefs: true,
    })
  );

  if (withDoc) {
    const pathToDoc = bundledPath.replace('.yml', '.doc.yml');
    await fsp.writeFile(
      pathToDoc,
      yaml.dump(bundledDocSpec, {
        noRefs: true,
      })
    );
  }
}

async function lintCommon(verbose: boolean, useCache: boolean): Promise<void> {
  const spinner = createSpinner('linting common spec', verbose).start();

  let hash = '';
  const cacheFile = toAbsolutePath(`specs/dist/common.cache`);
  if (useCache) {
    const { cacheExists, hash: newCache } = await checkForCache({
      folder: toAbsolutePath('specs/'),
      generatedFiles: [],
      filesToCache: ['common'],
      cacheFile,
    });

    if (cacheExists) {
      spinner.succeed("job skipped, cache found for 'common' spec");
      return;
    }

    hash = newCache;
  }

  await run(`yarn specs:lint common`, { verbose });

  if (hash) {
    spinner.text = `storing common spec cache`;
    await fsp.writeFile(cacheFile, hash);
  }

  spinner.succeed();
}

/**
 * Creates a lite search spec with the `ALGOLIASEARCH_LITE_OPERATIONS` methods
 * from the `search` spec.
 */
async function buildLiteSpec({
  spec,
  bundledPath,
  outputFormat,
}: {
  spec: string;
  bundledPath: string;
  outputFormat: string;
}): Promise<void> {
  const parsed = yaml.load(
    await fsp.readFile(toAbsolutePath(bundledPath), 'utf8')
  ) as Spec;

  // Filter methods.
  parsed.paths = Object.entries(parsed.paths).reduce(
    (acc, [path, operations]) => {
      for (const [method, operation] of Object.entries(operations)) {
        if (
          method === 'post' &&
          ALGOLIASEARCH_LITE_OPERATIONS.includes(operation.operationId)
        ) {
          return { ...acc, [path]: { post: operation } };
        }
      }

      return acc;
    },
    {} as Spec['paths']
  );

  const liteBundledPath = `specs/bundled/${spec}.${outputFormat}`;
  await fsp.writeFile(toAbsolutePath(liteBundledPath), yaml.dump(parsed));

  await propagateTagsToOperations({
    bundledPath: toAbsolutePath(liteBundledPath),
    clientName: spec,
    // Lite does not need documentation because it's just a subset
    withDoc: false,
  });
}

/**
 * Build spec file.
 */
async function buildSpec(
  spec: string,
  outputFormat: string,
  verbose: boolean,
  useCache: boolean
): Promise<void> {
  const isLite = spec === 'algoliasearch-lite';
  // In case of lite we use a the `search` spec as a base because only its bundled form exists.
  const specBase = isLite ? 'search' : spec;
  const cacheFile = toAbsolutePath(`specs/dist/${spec}.cache`);
  let hash = '';

  const spinner = createSpinner(`starting '${spec}' spec`, verbose).start();

  if (useCache) {
    spinner.text = `checking cache for '${specBase}'`;
    const generatedFiles = [`bundled/${spec}.yml`];
    if (!isLite && BUNDLE_WITH_DOC) {
      generatedFiles.push(`bundled/${spec}.doc.yml`);
    }

    const { cacheExists, hash: newCache } = await checkForCache({
      folder: toAbsolutePath('specs/'),
      generatedFiles,
      filesToCache: [specBase, 'common'],
      cacheFile,
    });

    if (cacheExists) {
      spinner.succeed(`job skipped, cache found for '${specBase}'`);
      return;
    }

    spinner.text = `cache not found for '${specBase}'`;
    hash = newCache;
  }

  // First linting the base
  spinner.text = `linting '${spec}' spec`;
  await run(`yarn specs:fix ${specBase}`, { verbose });

  // Then bundle the file
  const bundledPath = `specs/bundled/${spec}.${outputFormat}`;
  await run(
    `yarn openapi bundle specs/${specBase}/spec.yml -o ${bundledPath} --ext ${outputFormat}`,
    { verbose }
  );

  // Add the correct tags to be able to generate the proper client
  if (!isLite) {
    await propagateTagsToOperations({
      bundledPath: toAbsolutePath(bundledPath),
      clientName: spec,
      withDoc: BUNDLE_WITH_DOC,
    });
  } else {
    await buildLiteSpec({
      spec,
      bundledPath: toAbsolutePath(bundledPath),
      outputFormat,
    });
  }

  // Validate and lint the final bundle
  spinner.text = `validating '${spec}' bundled spec`;
  await run(`yarn openapi lint specs/bundled/${spec}.${outputFormat}`, {
    verbose,
  });

  spinner.text = `linting '${spec}' bundled spec`;
  await run(`yarn specs:fix bundled/${spec}.${outputFormat}`, { verbose });

  if (hash) {
    spinner.text = `storing '${spec}' spec cache`;
    await fsp.writeFile(cacheFile, hash);
  }

  spinner.succeed(`building complete for '${spec}' spec`);
}

export async function buildSpecs(
  clients: string[],
  outputFormat: 'json' | 'yml',
  verbose: boolean,
  useCache: boolean
): Promise<void> {
  await fsp.mkdir(toAbsolutePath('specs/dist'), { recursive: true });

  await lintCommon(verbose, useCache);

  await Promise.all(
    clients.map((client) => buildSpec(client, outputFormat, verbose, useCache))
  );
}
