<?php
namespace AlgoliaSearch;

$make_is_writable_fail = false;

function is_writable($filename)
{
    global $make_is_writable_fail;

    if (true === $make_is_writable_fail) {
        return false;
    }

    return \is_writeable($filename);
}
