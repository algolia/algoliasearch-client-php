<?php
/*
 * Copyright (c) 2013 Algolia
 * http://www.algolia.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 */

require_once 'src/AlgoliaSearch/AlgoliaException.php';
require_once 'src/AlgoliaSearch/AlgoliaConnectionException.php';
require_once 'src/AlgoliaSearch/Client.php';
require_once 'src/AlgoliaSearch/ClientContext.php';
require_once 'src/AlgoliaSearch/Index.php';
require_once 'src/AlgoliaSearch/IndexBrowser.php';
require_once 'src/AlgoliaSearch/PlacesIndex.php';
require_once 'src/AlgoliaSearch/SynonymType.php';
require_once 'src/AlgoliaSearch/Version.php';
require_once 'src/AlgoliaSearch/Json.php';
require_once 'src/AlgoliaSearch/FailingHostsCache.php';
require_once 'src/AlgoliaSearch/FileFailingHostsCache.php';
require_once 'src/AlgoliaSearch/InMemoryFailingHostsCache.php';
require_once 'src/AlgoliaSearch/Iterators/AlgoliaIterator.php';
require_once 'src/AlgoliaSearch/Iterators/RuleIterator.php';
require_once 'src/AlgoliaSearch/Iterators/SynonymIterator.php';
