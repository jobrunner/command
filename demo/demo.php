#!/usr/bin/php -Cq
<?php
ini_set('date.timezone', 'UTC');
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');

require "../vendor/autoload.php";

// for type see http://www.php.net/getopt
// If you use a "%s" in desc-key, the default value will be filled in.
$options  = array(array('short' => "h", 'long' => "help",     'default' => null,  'type' => "",   'desc' => "Display this help message and exit."),
                  array('short' => "i", 'long' => "input",    'default' => null,  'type' => "::", 'desc' => "Input to search"),
                  array('short' => "f", 'long' => "format",   'default' => 'tsv', 'type' => "::", 'desc' => "Defines the output format. Valid values are 'cvs', 'tsv' or 'json'. (default: tsv.)"),
                  array('short' => "I", 'long' => "ini",      'default' => null,  'type' => "::", 'desc' => "Full path for custome ini file")
);

$helpText = sprintf("Service Interface to demo dummy search. Copyright (c) 2016, Mayflower GmbH. All rights reserved.
Autor Jo Brunner <jo.brunner@mayflower.de>

Give me an search text with the -i (short) or --isbn (long form)  e.g.:

%1\$s -i3658132809
%1\$s -i\"3658132 809\"
%1\$s -i'365 8132809'
%1\$s --input=3658132809
%1\$s --input=\"3-658 132809\"
%1\$s --input='978-3-658-13280-4'

and I am going to start a couple of searches in some sources.
Then I try to create usefull informations for your records in a required format.

", basename($argv[0]));

try {

    $commandLine = new \Command\CommandLine($options);
    $opts        = $commandLine->getOpts();

    // optional, $configName can be null (default is always $argv[0])
    $configName = basename($argv[0]);
    $paths      = array();

    // add local ini file from command line opt to configure script:
    if (isset($opts->ini)) {
        $paths[]    = $opts->ini;
    }

    $config = new \Command\Configuration($configName, $paths);

    if (isset($opts->help)) {

        $commandLine->outputHelp($helpText);
        exit;
    }

    if (isset($opts->input)) {
        processInput($opts->input);
    }

} catch (\Exception $e) {

    printf("\n%s\n", $e->getMessage());
    exit(1);
}

exit(0);


function processInput($input)
{
    printf("\nSearching for '%s'...\nNo results.\n\n", $input);
}