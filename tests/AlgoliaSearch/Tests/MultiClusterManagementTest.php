<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class MultiClusterManagementTest extends AlgoliaSearchTestCase
{
    private $client;
    private $userID;


    public function uniq_userID($name)
    {
        if (getenv('TRAVIS') !== 'true') {
            return $name;
        }

        return $name.'-travis-'.getenv('TRAVIS_JOB_NUMBER');
    }

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID_MCM'), getenv('ALGOLIA_API_KEY_MCM'));
        $this->userID = $this->uniq_userID('php-client');
    }

    public function testListClusters() {
        $answer = $this->client->listClusters();


        $this->assertNotNull($answer['clusters']);
        $this->assertGreaterThan(0, count($answer['clusters']));
        $this->assertNotNull($answer['clusters'][0]['clusterName']);
        $this->assertNotNull($answer['clusters'][0]['nbRecords']);
        $this->assertNotNull($answer['clusters'][0]['nbUserIDs']);
        $this->assertNotNull($answer['clusters'][0]['dataSize']);
    }

    public function testAssignUserID() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->assignUserID($this->userID, $cluster);

        $this->assertNotNull($answer['createdAt']);
        sleep(2); // Sleep to let the cluster publish the change
    }

    public function testListUserIDs() {
        $answer = $this->client->listUserIDs();

        $this->assertNotNull($answer['userIDs']);
        $this->assertGreaterThan(0, count($answer['userIDs']));
        $this->assertNotNull($answer['userIDs'][0]['userID']);
        $this->assertNotNull($answer['userIDs'][0]['clusterName']);
        $this->assertNotNull($answer['userIDs'][0]['nbRecords']);
        $this->assertNotNull($answer['userIDs'][0]['dataSize']);
    }

    public function testGetTopUserID() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->getTopUserID();

        $this->assertNotNull($answer['topUsers']);
        $this->assertGreaterThan(0, count($answer['topUsers']));
        $this->assertGreaterThan(0, count($answer['topUsers'][$cluster]));
        $this->assertNotNull($answer['topUsers'][$cluster][0]['userID']);
        $this->assertNotNull($answer['topUsers'][$cluster][0]['nbRecords']);
        $this->assertNotNull($answer['topUsers'][$cluster][0]['dataSize']);
    }

    public function testGetUserID() {
        $answer = $this->client->getUserID($this->userID);

        $this->assertNotNull($answer['userID']);
        $this->assertNotNull($answer['clusterName']);
        $this->assertNotNull($answer['nbRecords']);
        $this->assertNotNull($answer['dataSize']);
    }

    public function testSearchUserIDs() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->searchUserIDs($this->userID, $cluster, 0, 1000);

        $this->assertNotNull($answer['hits']);
        $this->assertNotNull($answer['nbHits']);
        $this->assertEquals(0, $answer['page']);
        $this->assertEquals(1000, $answer['hitsPerPage']);
        $this->assertGreaterThan(0, count($answer['hits']));
        $this->assertNotNull($answer['hits'][0]['userID']);
        $this->assertNotNull($answer['hits'][0]['clusterName']);
        $this->assertNotNull($answer['hits'][0]['nbRecords']);
        $this->assertNotNull($answer['hits'][0]['dataSize']);
    }

    public function testRemoveUserID() {
        $answer = $this->client->removeUserID($this->userID);

        $this->assertNotNull($answer['deletedAt']);
    }
}
