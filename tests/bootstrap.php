<?php

require_once __DIR__.'/../vendor/autoload.php';

$envVarNames = array('ALGOLIA_APP_ID', 'ALGOLIA_API_KEY');

foreach ($envVarNames as $name) {
    if (!getenv($name)) {
        echo "Environment variable $name is undefined, please set one.";
        exit(255);
    }
}

unset($envVarNames, $name);
