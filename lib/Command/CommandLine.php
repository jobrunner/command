<?php
namespace Command;
/**
 * CommandLine class using getopt.
 *
 * @package     Command
 * @subpackage  CommandLine
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */

/**
 * CommandLine class using getopt.
 *
 * @package     Command
 * @subpackage  CommandLine
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */

class CommandLine
{
    protected $_colWith0 = 2;
    protected $_colWith1 = 19;
    protected $_colWith2 = 1;
    protected $_colWith3 = 58;

    protected $_options = array();

    public function __construct(array $options = null)
    {
        if (null != $options) {
            $this->_options = $options;
        }
    }

    public function getOpts(array $options = null)
    {
        if (null != null) {
            $this->_options = $options;
        }

        // mapping from command to property
        $optHashTable = array();
        foreach ($this->_options as $option) {
            $optHashTable[$option['long']]  = $option;
            $optHashTable[$option['short']] = $option;
        }

        $shortOpts      = array_map(function($opt) {return $opt['short'] . $opt['type']; }, $this->_options);
        $longOpts       = array_map(function($opt) {return $opt['long']  . $opt['type']; }, $this->_options);

        $opts           = (object)getopt(implode("", $shortOpts), $longOpts);

        $commands       = [];

        foreach ($opts as $command => $value) {
            // setting flags
            if (empty($optHashTable[$command]['type'])) {
                $commands[$optHashTable[$command]['long']] = true;
            } else {
                $commands[$optHashTable[$command]['long']] = $value;
            }
        }

        // set default values
        foreach ($this->_options as $option) {
            if (!isset($commands[$option['long']]) && !empty($option['default'])) {
                $commands[$option['long']] = $option['default'];
            }
        }

        return (object)$commands;
    }


    public function outputHelp($helpText)
    {
        echo $helpText;
        echo "\n";

        foreach ($this->_options as $opt) {
            if (isset($opt['short'])) {
                $opt['short'] = '-' . $opt['short'];
            }

            if (isset($opt['long'])) {
                $opt['long'] = '--' . $opt['long'];
                if (!empty($opt['type'])) {
                    $opt['long'] = $opt['long'] . '=name';
                }
            }

            $line = str_pad("", $this->_colWith0) . trim(implode(", ", array($opt['short'], $opt['long'])), ', ');

            echo $line;

            if (strlen($line) > ($this->_colWith0 + $this->_colWith1)) {
                echo "\n" . str_pad("", $this->_colWith0 + $this->_colWith1 + $this->_colWith2);
            } else {
                echo str_pad("", ($this->_colWith0 + $this->_colWith1 + $this->_colWith2) - strlen($line));
            }

            if (false !== strpos($opt['desc'], '%s') && $opt['default'] != null) {
                $desc = sprintf($opt['desc'], $opt['default']);
            }
            else {
                $desc = $opt['desc'];
            }
            $lines = explode("\n", wordwrap($desc, $this->_colWith3, "\n"));

            // first line without trailing spaces
            list(, $line) = each($lines);
            echo $line . "\n";

            // lines with trailing spaces
            while (list(,$line) = each($lines)) {
                echo str_pad("", $this->_colWith0 + $this->_colWith1 + $this->_colWith2) . $line . "\n";
            }
            echo "\n";
        }
    }
}
