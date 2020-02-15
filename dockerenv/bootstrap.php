<?php

// check for existance of identifier fileset in script-a.sh or script-b.sh 
if (file_exists('/home/is_legion_docker.txt')) {
    $envPath =  dirname(__FILE__) . '/.env';
    
    // load order in this class cannot be guaranteed, may happen before or after
    // silverstripe/framework/src/includes/constants.php
    
    // silverstripe framework was autoloaded first
    // overwrite values by local .env.docker over the top of it
    if (class_exists('SilverStripe\Core\EnvironmentLoader')) {
        $loader = new SilverStripe\Core\EnvironmentLoader();
        $loader->loadFile($envPath, true);
    }

    // this module was autoloaded before silverstripe framework
    if (!class_exists('SilverStripe\Core\EnvironmentLoader')) {

        // this is to stop .env being loaded in constants.php when the silverstripe
        // framework module is loaded
        putenv('SS_IGNORE_DOT_ENV=1');

        // read .env.docker file and loaded as environment variables via putenv()
        $lines = file_get_contents($envPath);
        foreach (preg_split("%\r?\n%", $lines) as $line) {
            if (!$line) {
                continue;
            }
            $line = preg_replace('%["\']%', '', $line);
            putenv($line);
        }
    }
}