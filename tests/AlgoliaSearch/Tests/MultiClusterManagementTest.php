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


        $this->assertTrue($answer['clusters'] !== null);
        $this->assertTrue(count($answer['clusters']) > 0);
        $this->assertTrue($answer['clusters'][0]['clusterName'] !== null);
        $this->assertTrue($answer['clusters'][0]['nbRecords'] !== null);
        $this->assertTrue($answer['clusters'][0]['nbUserIDs'] !== null);
        $this->assertTrue($answer['clusters'][0]['dataSize'] !== null);
    }

    public function testAssignUserID() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->assignUserID($this->userID, $cluster);

        $this->assertTrue($answer['createdAt'] !== null);
        sleep(2); // Sleep to let the cluster publish the change
    }

    public function testListUserIDs() {
        $answer = $this->client->listUserIDs();

        $this->assertTrue($answer['userIDs'] !== null);
        $this->assertTrue(count($answer['userIDs']) > 0);
        $this->assertTrue($answer['userIDs'][0]['userID'] !== null);
        $this->assertTrue($answer['userIDs'][0]['clusterName'] !== null);
        $this->assertTrue($answer['userIDs'][0]['nbRecords'] !== null);
        $this->assertTrue($answer['userIDs'][0]['dataSize'] !== null);
    }

    public function testGetTopUserID() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->getTopUserID();

        $this->assertTrue($answer['topUsers'] !== null);
        $this->assertTrue(count($answer['topUsers']) > 0);
        $this->assertTrue(count($answer['topUsers'][$cluster]) > 0);
        $this->assertTrue($answer['topUsers'][$cluster][0]['userID'] !== null);
        $this->assertTrue($answer['topUsers'][$cluster][0]['nbRecords'] !== null);
        $this->assertTrue($answer['topUsers'][$cluster][0]['dataSize'] !== null);
    }

    public function testGetUserID() {
        $answer = $this->client->getUserID($this->userID);

        $this->assertTrue($answer['userID'] !== null);
        $this->assertTrue($answer['clusterName'] !== null);
        $this->assertTrue($answer['nbRecords'] !== null);
        $this->assertTrue($answer['dataSize'] !== null);
    }

    public function testSearchUserIDs() {
        $clusters = $this->client->listClusters();
        $cluster = $clusters['clusters'][0]['clusterName'];
        $answer = $this->client->searchUserIDs($this->userID, $cluster, 0, 1000);

        $this->assertTrue($answer['hits'] !== null);
        $this->assertTrue($answer['nbHits'] !== null);
        $this->assertTrue($answer['page'] == 0);
        $this->assertTrue($answer['hitsPerPage'] == 1000);
        $this->assertTrue(count($answer['hits']) > 0);
        $this->assertTrue($answer['hits'][0]['userID'] !== null);
        $this->assertTrue($answer['hits'][0]['clusterName'] !== null);
        $this->assertTrue($answer['hits'][0]['nbRecords'] !== null);
        $this->assertTrue($answer['hits'][0]['dataSize'] !== null);
    }

    public function testRemoveUserID() {
        $answer = $this->client->removeUserID($this->userID);

        $this->assertTrue($answer['deletedAt'] !== null);
    }
}