<?php

/**
 * Class HttpClientException
 *
 */
abstract class HttpClientException extends \Exception {}

/**
 * Class HttpClient
 *
 */
class HttpClient {
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';

    const HTTP_NO_CONTENT = 204;
    const CLIENT_VERSION = '1.2.0';
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var array
     */
    protected $userAgent = array();

    /**
     * @var int
     */
    private $timeout = 10;
    
    /**
    * @var mixed
    */
    
    private $oAuthentication = array();
    
    public $ssl_verify = false; // Verify Camoo SSL before sending any message

    /**
     * @var int
     */
    private $connectionTimeout = 2;

    /**
     * @param string $endpoint
     * @param int $timeout > 0
     * @param int $connectionTimeout >= 0
     *
     * @throws \HttpClientException if timeout settings are invalid
     */
    public function __construct($endpoint, $hAuthentication, $timeout = 10, $connectionTimeout = 2) {
        $this->endpoint = $endpoint;
	$this->oAuthentication = $hAuthentication;
    
        $this->addUserAgentString('CamooSms/ApiClient/' . static::CLIENT_VERSION);
        $this->addUserAgentString($this->getPhpVersion());

        if (!is_int($connectionTimeout) || $connectionTimeout < 0) {
            throw new \HttpClientException(sprintf(
                'Connection timeout must be an int >= 0, got "%s".',
                is_object($connectionTimeout) ? get_class($connectionTimeout) : gettype($connectionTimeout).' '.var_export($connectionTimeout, true))
            );
        }

        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @param string $userAgent
     */
    public function addUserAgentString($userAgent)
    {
        $this->userAgent[] = $userAgent;
    }


    /**
     * @param string      $method
     * @param string|null $data
     *
     * @return array
     *
     * @throws HttpClientException
     */
   
     public function performRequest( $method, $data=array()) {
        // Build the post data
        $data = array_merge($data, $this->oAuthentication);
	$data['user_agent'] = implode(' ', $this->userAgent);
        $post = '';
        foreach ( $data as $k => $v ) {
            $post .= "&$k=$v";
        }
        // If available, use CURL
        if (function_exists('curl_version')) {
            $to_camoo = curl_init( $this->endpoint );
            curl_setopt($to_camoo, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($to_camoo, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($to_camoo, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);

            if (!$this->ssl_verify) {
                curl_setopt( $to_camoo, CURLOPT_SSL_VERIFYPEER, false);
            }
            
         if ( $method === static::REQUEST_GET ) {
            curl_setopt($to_camoo, CURLOPT_HTTPGET, true);
        } elseif ($method === static::REQUEST_POST ) {
            curl_setopt($to_camoo, CURLOPT_POST, true);
            curl_setopt( $to_camoo, CURLOPT_POSTFIELDS, $post );
        } 
        
            $from_camoo = curl_exec( $to_camoo );
            curl_close ( $to_camoo );
        } elseif (ini_get('allow_url_fopen')) {
            // No CURL available so try the awesome file_get_contents
            $opts = array('http' =>
                array(
                    'method'  => $method,
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post
                )
            );
            $context = stream_context_create($opts);
            $from_camoo = file_get_contents($this->endpoint, false, $context);
        } else {
            // No way of sending a HTTP post
            throw new \HttpClientException('No way of sending a HTTP Request');
        
        }
        return $from_camoo;
    }
	
     /**
     * @return string
     */
    private function getPhpVersion() {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
        }
        return 'PHP/' . PHP_VERSION_ID;
    }
}
