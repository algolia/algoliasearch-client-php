import { algoliasearchLiteClient } from '@experimental-api-clients-automation/algoliasearch-lite';
import type { EchoResponse } from '@experimental-api-clients-automation/client-common';
import { echoRequester } from '@experimental-api-clients-automation/requester-node-http';

const appId = process.env.ALGOLIA_APPLICATION_ID || 'test_app_id';
const apiKey = process.env.ALGOLIA_SEARCH_KEY || 'test_api_key';

const client = algoliasearchLiteClient(appId, apiKey, {
  requester: echoRequester(),
});

describe('multipleQueries', () => {
  test('multipleQueries for a single request with minimal parameters', async () => {
    const req = (await client.multipleQueries({
      requests: [{ indexName: 'theIndexName' }],
      strategy: 'stopIfEnoughMatches',
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/*/queries');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({
      requests: [{ indexName: 'theIndexName' }],
      strategy: 'stopIfEnoughMatches',
    });
    expect(req.searchParams).toEqual(undefined);
  });

  test('multipleQueries for multiple requests with all parameters', async () => {
    const req = (await client.multipleQueries({
      requests: [
        {
          indexName: 'theIndexName',
          query: 'test',
          type: 'facet',
          facet: 'theFacet',
          params: 'testParam',
        },
        {
          indexName: 'theIndexName',
          query: 'test',
          type: 'default',
          params: 'testParam',
        },
      ],
      strategy: 'stopIfEnoughMatches',
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/*/queries');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({
      requests: [
        {
          indexName: 'theIndexName',
          query: 'test',
          type: 'facet',
          facet: 'theFacet',
          params: 'testParam',
        },
        {
          indexName: 'theIndexName',
          query: 'test',
          type: 'default',
          params: 'testParam',
        },
      ],
      strategy: 'stopIfEnoughMatches',
    });
    expect(req.searchParams).toEqual(undefined);
  });
});

describe('post', () => {
  test('allow post method for a custom path with minimal parameters', async () => {
    const req = (await client.post({
      path: '/test/minimal',
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/test/minimal');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual(undefined);
    expect(req.searchParams).toEqual(undefined);
  });

  test('allow post method for a custom path with all parameters', async () => {
    const req = (await client.post({
      path: '/test/all',
      parameters: { query: 'parameters' },
      body: { body: 'parameters' },
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/test/all');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({ body: 'parameters' });
    expect(req.searchParams).toEqual({ query: 'parameters' });
  });
});

describe('search', () => {
  test('search with minimal parameters', async () => {
    const req = (await client.search({
      indexName: 'indexName',
      searchParams: { query: 'myQuery' },
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/indexName/query');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({ query: 'myQuery' });
    expect(req.searchParams).toEqual(undefined);
  });

  test('search with facetFilters', async () => {
    const req = (await client.search({
      indexName: 'indexName',
      searchParams: { query: 'myQuery', facetFilters: ['tags:algolia'] },
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/indexName/query');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({
      query: 'myQuery',
      facetFilters: ['tags:algolia'],
    });
    expect(req.searchParams).toEqual(undefined);
  });
});

describe('searchForFacetValues', () => {
  test('get searchForFacetValues results with minimal parameters', async () => {
    const req = (await client.searchForFacetValues({
      indexName: 'indexName',
      facetName: 'facetName',
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/indexName/facets/facetName/query');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual(undefined);
    expect(req.searchParams).toEqual(undefined);
  });

  test('get searchForFacetValues results with all parameters', async () => {
    const req = (await client.searchForFacetValues({
      indexName: 'indexName',
      facetName: 'facetName',
      searchForFacetValuesRequest: {
        params: "query=foo&facetFilters=['bar']",
        facetQuery: 'foo',
        maxFacetHits: 42,
      },
    })) as unknown as EchoResponse;

    expect(req.path).toEqual('/1/indexes/indexName/facets/facetName/query');
    expect(req.method).toEqual('POST');
    expect(req.data).toEqual({
      params: "query=foo&facetFilters=['bar']",
      facetQuery: 'foo',
      maxFacetHits: 42,
    });
    expect(req.searchParams).toEqual(undefined);
  });
});
