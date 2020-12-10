#!/bin/sh

# All commands below must no fail
set -e

# Be in the root dir
cd "$(dirname $0)/../"

find tests/ -type f -print0 | xargs -0 sed -i 's/function setUpBeforeClass(): void/function setUpBeforeClass()/g';
find tests/ -type f -print0 | xargs -0 sed -i 's/function setUp(): void/function setUp()/g';
find tests/ -type f -print0 | xargs -0 sed -i 's/function tearDown(): void/function tearDown()/g';

# Return back to original dir
cd - > /dev/null
