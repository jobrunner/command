<?php
class CommandTest extends PHPUnit_Framework_TestCase
{
    public $options;
    public $helpText;

    public function setUp()
    {
        global $argv;
        $argv[0] = 'tool';

        $this->options  = array(array('short' => "h", 'long' => "help",   'default' => null,  'type' => "",   'desc' => "Display this help message and exit."),
                          array('short' => "m", 'long' => "message",   'default' => null,  'type' => "::", 'desc' => "Message to print"),
                          array('short' => "I", 'long' => "include", 'default' => '/usr/local/include/', 'type' => "::", 'desc' => "Include path (Default: %s)")
        );

        $this->helpText = sprintf("
Test tool %s Copyright (c) 2012-2016, Mayflower GmbH. All rights reserved.
Autor Jo Brunner <jo.brunner@mayflower.de>

", basename($argv[0]));

    }

    /**
     * @group configuration
     * @expectedException \Exception
     */
    public function testConfigurationNotFound1()
    {
        $iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ressources' . DIRECTORY_SEPARATOR . 'notfound.ini';

        new \Command\Configuration('test', array($iniPath));
    }

    /**
     * @group configuration
     */
    public function testConfigurationNotFound2()
    {
        $iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ressources' . DIRECTORY_SEPARATOR . 'test.ini';

        new \Command\Configuration('test', array($iniPath));
    }

    /**
     * @group configuration
     */
    public function testConfiguration()
    {
        $iniPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ressources' . DIRECTORY_SEPARATOR . 'test.ini';

        $config = new \Command\Configuration('test', array($iniPath));

        $configuration = $config->getAll();
        $this->assertArrayHasKey('SECTION-1', $configuration);
        $this->assertArrayHasKey('SECTION-2', $configuration);
        $this->assertObjectHasAttribute('tests', $configuration['SECTION-1']);

        $this->assertEquals(count($configuration['SECTION-1']->tests), 2);
        $this->assertEquals($configuration['SECTION-1']->tests[1], 'äh');
    }

    /**
     * @group commandline
     */
    public function testCommandLineDefaultValue()
    {

        $commandLine = new \Command\CommandLine($this->options);
        $opts        = $commandLine->getOpts();

        $this->assertObjectHasAttribute('include', $opts);
        $this->assertEquals($opts->include, '/usr/local/include/');

        $this->assertObjectNotHasAttribute('help', $opts);
    }

    /**
     * @group commandline
     */
    public function testCommandLineHelp()
    {
        $commandLine = new \Command\CommandLine($this->options);

        ob_start();
        $commandLine->outputHelp($this->helpText);
        $content = ob_get_contents();
        ob_end_clean();

        $finalHelpText = <<<'EOD'
Test tool tool Copyright (c) 2012-2016, Mayflower GmbH. All rights reserved.
Autor Jo Brunner <jo.brunner@mayflower.de>


  -h, --help          Display this help message and exit.
  -m, --message=name  Message to print
  -I, --include=name  Include path (Default: /usr/local/include/)
EOD;

        $this->assertEquals(trim($content, "\n"), trim($finalHelpText, "\n"));
    }
}