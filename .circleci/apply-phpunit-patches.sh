#!/bin/sh

# All commands below must no fail
set -e

# Be in the root dir
cd "$(dirname $0)/../"

find tests/ -type f -print0 | xargs -0 sed -i 's/function setUpBeforeClass()/function setUpBeforeClass(): void/g';
find tests/ -type f -print0 | xargs -0 sed -i 's/function tearDownAfterClass()/function tearDownAfterClass(): void/g';
find tests/ -type f -print0 | xargs -0 sed -i 's/function setUp()/function setUp(): void/g';
find tests/ -type f -print0 | xargs -0 sed -i 's/function tearDown()/function tearDown(): void/g';

# Return back to original dir
cd - > /dev/null
