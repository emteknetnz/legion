<?php

// this file is called from within the primary container to run tests

use Emteknetnz\Legion\PrimaryContainerHelper;

require 'src/autoload.php';

$primaryContainer = new PrimaryContainerHelper();
$primaryContainer->runTests();
