<?php

/*
 * In order to use the client without Composer, you can require
 * this file to load all the lib classes but note that you need
 * to pull https://github.com/php-fig/http-message library in
 * your project and require_once all the classes inside src/
 *
 * The other way is to already have Guzzle loaded
 */

/*
 * Some helper functions are outside classes and need to be loaded
 */
require_once './src/functions.php';

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
