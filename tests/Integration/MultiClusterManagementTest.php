<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\SyncClient;
use DateTime;

class MultiClusterManagementTest extends AlgoliaIntegrationTestCase
{
    /** @var SearchClient */
    private $mcmClient;

    /** @var string */
    private $clusterName = '';

    /** @var string */
    private $mcmUserId0;

    /** @var string */
    private $mcmUserId1;

    /** @var string */
    private $mcmUserId2;

    protected function setUp()
    {
        parent::setUp();

        $config = array(
            'appId' => getenv('ALGOLIA_APPLICATION_ID_MCM'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_MCM'),
        );

        $this->mcmClient = SearchClient::createWithConfig(new SearchConfig($config));
        $this->mcmUserId0 = $this->createMcmUserId('0');
        $this->mcmUserId1 = $this->createMcmUserId('1');
        $this->mcmUserId2 = $this->createMcmUserId('2');
        $clusterList = $this->mcmClient->listClusters();
        $this->clusterName = $clusterList['clusters'][0]['clusterName'];
    }

    public function testListClusters()
    {
        $clusterList = $this->mcmClient->listClusters();
        $this->assertTrue(count($clusterList['clusters']) >= 2);
    }

    public function testMultiClusterManagement()
    {
        $response = $this->mcmClient->assignUserId($this->mcmUserId0, $this->clusterName);
        $this->assertArrayHasKey('createdAt', $response);

        $response = $this->autoRetryGetUserId($this->mcmUserId0);
        $this->assertArrayHasKey('userID', $response);
        $this->assertEquals($response['userID'], $this->mcmUserId0);
        $this->assertEquals($response['clusterName'], $this->clusterName);

        $response = $this->mcmClient->assignUserIds(array($this->mcmUserId1, $this->mcmUserId2), $this->clusterName);
        $this->assertArrayHasKey('createdAt', $response);

        $response = $this->autoRetryGetUserId($this->mcmUserId1);
        $this->assertArrayHasKey('userID', $response);
        $this->assertEquals($response['userID'], $this->mcmUserId1);
        $this->assertEquals($response['clusterName'], $this->clusterName);

        $response = $this->autoRetryGetUserId($this->mcmUserId2);
        $this->assertArrayHasKey('userID', $response);
        $this->assertEquals($response['userID'], $this->mcmUserId2);
        $this->assertEquals($response['clusterName'], $this->clusterName);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId0);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId0);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId1);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId1);

        $response = $this->mcmClient->searchUserIds($this->mcmUserId2);
        $this->assertTrue($response['nbHits'] > 0);
        $this->assertEquals($response['hits'][0]['userID'], $this->mcmUserId2);

        $response = $this->mcmClient->listUserIds();
        $this->assertNotEmpty($response['userIDs']);

        $result0 = '';
        foreach ($response['userIDs'] as $userIDset) {
            if ($userIDset['userID'] === $this->mcmUserId0) {
                $result0 = $userIDset['userID'];
            }
        }
        $this->assertEquals($result0, $this->mcmUserId0);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId0);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId1);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->autoRetryRemoveUserId($this->mcmUserId2);
        $this->assertArrayHasKey('deletedAt', $response);

        $response = $this->mcmClient->listUserIds();
        $yesterday =  date('Y-m-d',(strtotime ( '-1 day')));
        foreach ($response['userIDs'] as $userIDset) {
            if (strpos($userIDset['userID'], "php-${yesterday}") === 0) {
                $this->autoRetryRemoveUserId($userIDset['userID']);
            }
        }
    }

    private function autoRetryGetUserId($userID)
    {
        $retry = 0;
        $response = array();
        do {
            try {
                $response = $this->mcmClient->getUserId($userID);
            } catch (\Exception $e) {
                sleep(1);
                $retry++;
            }
        } while (!array_key_exists('userID', $response) && $retry < 10);

        return $response;
    }

    private function autoRetryRemoveUserId($userID)
    {
        $retry = 0;
        $response = array();
        do {
            try {
                $response = $this->mcmClient->removeUserId($userID);
            } catch (\Exception $e) {
                sleep(1);
                $retry++;
            }
        } while (!array_key_exists('deletedAt', $response) && $retry < 10);

        return $response;
    }

    private function createMcmUserId($name)
    {
        return static::safeUserName($name);
    }
}
