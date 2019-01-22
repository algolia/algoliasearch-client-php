<?php

/*
 * In order to use the client without Composer, you can require
 * this file to load all the lib classes. You need to install
 * the other dependencies.
 *
 * If they are already loaded, ignore the following.
 *
 * If you need to download them, use the script
 * install-dependencies-without-composer in the `bin` folder:
 *
 * `php bin/install-dependencies-without-composer`
 */

/*
 * Some helper functions are outside classes and need to be loaded
 */
require_once __DIR__.'/src/functions.php';
require_once __DIR__.'/src/Http/Psr7/functions.php';

/*
 * Based on https://www.php-fig.org/psr/psr-4/examples/.
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'Algolia\\AlgoliaSearch\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__.'/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (0 !== strncmp($prefix, $class, $len)) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

spl_autoload_register(function ($class) {
    $prefixes = array(
        'Psr\\Http\\Message\\' => 'psr/http-message/src/',
        'Psr\\Log\\' => 'psr/log/Psr/Log/',
        'Psr\\SimpleCache\\' => 'psr/simple-cache/src/',
    );

    $base_dir = __DIR__.'/vendor/';

    foreach ($prefixes as $prefix => $subdir) {
        $len = strlen($prefix);
        if (0 !== strncmp($prefix, $class, $len)) {
            continue;
        }

        $relative_class = substr($class, $len);

        $file = $base_dir.$subdir.str_replace('\\', '/', $relative_class).'.php';

        if (file_exists($file)) {
            require $file;
        }
    }
});
