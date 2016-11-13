<?php
namespace Command;
/**
 * Communication layer class interface.
 *
 * @package     Command
 * @subpackage  Communication
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */

/**
 * Communication layer class interface.
 *
 * @package     Command
 * @subpackage  Communication
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */
interface CommunicationInterface
{
    public function doHttpGet($url, array $params = null, array $headers = array(), array $additionalCurlOpts = array());
    public function doHttpPost($url, array $params = null, array $headers = array(), array $additionalOpts = array());
}
