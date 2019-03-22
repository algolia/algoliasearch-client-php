<?php
/**
 * This file registers this as a Magento library if the client is running under
 * Magento 2, enabling code interception (plugins).
 */
if (class_exists("\Magento\Framework\Component\ComponentRegistrar")) {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
        'algolia/algoliasearch-client-php',
        __DIR__
    );
}
