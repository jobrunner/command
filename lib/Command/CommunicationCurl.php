<?php
namespace Command;
/**
 * Communication layer class using the cURL library.
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
 * Communication layer class using the cURL library.
 *
 * @package     Command
 * @subpackage  Communication
 * @author      Jo Brunner <jo.brunner@mayflower.de>
 * @copyright   2013 by Jo Brunner, Mayflower GmbH
 * @license     MIT
 * @link        http://www.mayflower.de
 * @since       Available since November 2012
 */
class CommunicationCurl implements CommunicationInterface
{
    /**
     * Perform a HTTP(S) Request
     *
     * @param       $url
     * @param array $params
     * @param array $headers            Additional Headers
     * @param array $additionalCurlOpts Additions for Alex
     *
     * @return object
     */
    protected function _doHttpRequest($url, array $params = null, array $headers = array(), array $additionalCurlOpts = array())
    {
        array_unshift($headers, 'Expect:');

        $postOptions    = array(CURLOPT_SSL_VERIFYPEER => true,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_FOLLOWLOCATION => false,
                                CURLOPT_HEADER         => true,
                                CURLINFO_HEADER_OUT    => true);

        $requestOptions = array(CURLOPT_URL        => $url,
                                CURLOPT_HTTPHEADER => $headers,
                                // to force an content type of application/x-www-form-urlencoded
                                // a common method is providing the paramaters already encoded
                                CURLOPT_POSTFIELDS => http_build_query($params));

        $options        = $postOptions
                        + $requestOptions
                        + $additionalCurlOpts;

        $curl           = curl_init();

        curl_setopt_array($curl, $options);

        $rawResponse    = curl_exec($curl);

        if (false === $rawResponse) {
            throw new \Exception("Connection failed. " . curl_error($curl));
        }

        $headerSize     = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $sentHeader     = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $headerString   = substr($rawResponse, 0, $headerSize - 4);
        $bodyString     = substr($rawResponse, $headerSize);

        $header         = explode("\n", $headerString);

        // remove first line (http protocol version and http status code)
        array_shift($header);

        // extract headers
        $headers        = array();
        $cookies        = array();
        foreach ($header as $headerItemString) {
            list($headerName, $headerValue) = explode(':', $headerItemString);
            if ('Set-Cookie' == $headerName) {
                $cookie                 = $this->_extractCookie($headerValue);
                $cookies[$cookie->name] = $cookie->value;
            }

            $headers[$headerName] = $headerValue;
        }

        return (object)array(
            'sent'    => $sentHeader,
            'code'    => $httpStatusCode,
            'headers' => $headers,
            'cookies' => $cookies,
            'body'    => $bodyString
        );
    }

    protected function _extractCookie($cookieString)
    {
        $name = $value = null;
        if (preg_match('#^([^;]+)#', $cookieString, $combination)) {
            list($name, $value) = explode('=', $combination[1]);
        }

        return (object)array('name'  => trim($name),
                             'value' => trim($value));
    }

    /**
     * Performs a HTTP(S) POST-Request
     *
     * @param       $url
     * @param array $params
     * @param array $headers            Additional Headers
     * @param array $additionalCurlOpts Additions for Alex
     *
     * @return object
     */
    public function doHttpPost($url, array $params = null, array $headers = array(), array $additionalCurlOpts = array())
    {
        $additionalCurlOpts[CURLOPT_POST] = true;

        return $this->_doHttpRequest($url, $params, $headers, $additionalCurlOpts);
    }

    /**
     * Performs a HTTP(S) GET-Request
     *
     * @param       $url
     * @param array $params
     * @param array $headers            Additional Headers
     * @param array $additionalCurlOpts Additions for Alex
     *
     * @return object
     */
    public function doHttpGet($url, array $params = null, array $headers = array(), array $additionalCurlOpts = array())
    {
        $additionalCurlOpts[CURLOPT_HTTPGET] = true;

        // remove a query from url
        @list($url, $query) = explode('?', $url);
        $query              = implode('&', array($query, http_build_query($params)));
        $url                = $url . '?' . $query;
        $params             = array();

        return $this->_doHttpRequest($url, $params, $headers, $additionalCurlOpts);
    }
}