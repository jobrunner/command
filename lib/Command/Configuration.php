<?php
namespace Command;
/**
 * Configuration class using ini files.
 *
 * @package     Command
 * @subpackage  Configuration
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */

/**
 * Configuration class using ini files.
 *
 * @package     Command
 * @subpackage  Configuration
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */
class Configuration
{
    protected $_config      = array();
    protected $_iniFilename = null;

    /**
     * @param null $inifilename
     *
     * @throws \Exception
     */
    public function __construct($configName = null, array $additionalPaths = array())
    {
        $this->_iniFilename = $this->_searchIniFile($configName, $additionalPaths);
        $this->_config      = $this->_parse($this->_iniFilename);
    }

    public function getAll()
    {
        return $this->_config;
    }

    public function getAllNamespace($namespace)
    {
        if (isset($this->_config[$namespace])) {

            return $this->_config[$namespace];
        }

        return array();
    }

    public function callerScript()
    {
      $bt = debug_backtrace();

      return $bt[count($bt) - 1]['file'];
    }

    protected function _searchIniFile($configName = null, array $additionalPaths = array())
    {
        if (null == $configName) {
            $configName = basename($GLOBALS['argv'][0]);
        }

        $configName        = preg_replace('/((\.ini)|(\.php))$/', '', $configName);

        $paths = array();

        foreach ($additionalPaths as $path) {
            if (is_dir($path))  {
                $paths[] = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . rtrim($configName, '.') . '.ini';
            }
            else {
                $paths[] = $path;
            }
        }
        $paths[] = rtrim(getenv("HOME"), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.' . trim($configName, '.');
        $paths[] = rtrim(dirname($this->callerScript()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . rtrim($configName, '.') . '.ini';

        foreach ($paths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        throw new \Exception(sprintf("Couldn't find ini files\n%s\n", implode("\n", $paths)));
    }

    /**
     * Parses INI file adding extends functionality via ":base" postfix on namespace.
     *
     * Code adopted from jeremygiberson@gmail.com
     *
     * @param string $filename
     *
     * @return array
     */
    protected function _parse($filename)
    {
        $iniStructure = parse_ini_file($filename, true);
        $config       = array();

        foreach($iniStructure as $namespace => $properties) {

            @list($name, $extends) = explode(':', $namespace);
            $name                  = trim($name);
            $extends               = trim($extends);

            // create namespace if necessary
            if (!isset($config[$name])) {
                $config[$name] = array();
            }

            // inherit base namespace
            if (isset($iniStucture[$extends])) {
                foreach($iniStructure[$extends] as $property => $value) {
                    $config[$name][$property] = $value;
                }
            }
            // overwrite / set current namespace values
            foreach ($properties as $property => $value) {
                $config[$name][$property] = $value;
            }

            $config[$name] = (object)$config[$name];

            // Hack for JIRA-Labels:
            if (!empty($config[$name]->labels) && !is_array($config[$name]->labels)) {
                $config[$name]->labels = explode(',', $config[$name]->labels);
            }
        }

        return $config;
    }
}