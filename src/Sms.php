<?php
namespace Camoo\Sms;

/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: src/Sms.php
 * created: Jan 2015
 * Updated: Oct. 2017
 * Created by: Epiphane Tchabom (e.tchabom@camoo.cm)
 * Description: CAMOO SMS LIB
 *
 * @link http://www.camoo.cm
 */

/**
 * Class Camoo\Sms\Sms handles the methods and properties of sending an SMS message.
 *
 * Usage: $oSMS = new Camoo\Sms\Sms( $account_key, $account_password );
 * Methods:
 *     sendText ( $to, $from, $message, $unicode = null )
 *
 *     inboundText ( $data=null )
 *
 *
 */
require_once 'Base.php';
require_once 'HttpClient.php';

class Sms extends Base
{

    // Camoo account credentials
    private $oCredentials = [];

    /**
     * @var array The most recent parsed Camoo response.
     */
    private $camoo_response = '';

    // Current message
    public $to         = null;
    public $from       = null;
    public $text       = null;
    public $network    = null;
    public $message_id = null;

    // A few options
    public $ssl_verify = false; // Verify Camoo SSL before sending any message


    public function __construct($api_key, $api_secret)
    {
        $this->oCredentials = ['api_key' => $api_key, 'api_secret' => $api_secret];
    }

    /**
     * Prepare new text message.
     *
     * If $unicode is not provided we will try to detect the
     * message type. Otherwise set to TRUE if you require
     * unicode characters.
     */
    public function sendText($to, $from, $message, $unicode = null)
    {

        // Making sure strings are UTF-8 encoded
        if (!is_numeric($from) && !mb_check_encoding($from, 'UTF-8')) {
            trigger_error('$from needs to be a valid UTF-8 encoded string');
            return false;
        }

        if (!mb_check_encoding($message, 'UTF-8')) {
            trigger_error('$message needs to be a valid UTF-8 encoded string');
            return false;
        }

        if ($unicode === null) {
            $containsUnicode = max(array_map('ord', str_split($message))) > 127;
        } else {
            $containsUnicode = (bool)$unicode;
        }

        // Make sure $from is valid
        $from = $this->validateOriginator($from);

        // URL Encode
        $sFrom = urlencode($from);
        $sMessage = urlencode($message);

        // Send away!
        $hPost = [
            'from'    => $sFrom,
            'to'      => !is_array($to)? $to : implode(',', $to),
            'message' => $sMessage,
            'type'    => $containsUnicode ? 'unicode' : 'text'
        ];
        return $this->sendSmsRequest($hPost);
    }

    /**
     * Prepare and send a new message.
     */
    private function sendSmsRequest($data)
    {
        try {
            $this->camoo_bulksms_uri = $this->getEndPointUrl();
            $oHttpClient = new HttpClient($this->camoo_bulksms_uri, $this->oCredentials);
            return $this->decode($oHttpClient->performRequest('POST', $data));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('SMS Request can not be performed!');
        }
    }

    /**
     * @Brief Validate an originator string
     *
     * If the originator ('from' field) is invalid, some networks may reject the network
     * whilst stinging you with the financial cost! While this cannot correct them, it
     * will try its best to correctly format them.
     */
    private function validateOriginator($inp)
    {
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);

        if (preg_match('/[a-zA-Z]/', $inp)) {
            // Alphanumeric format so make sure it's < 11 chars
            $ret = substr($ret, 0, 11);
        } else {
            // Numerical, remove any prepending '00'
            if (substr($ret, 0, 2) == '00') {
                $ret = substr($ret, 2);
                $ret = substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }

    
    /**
    * read the current user balance
    * @return mixed Balance
    */
    public function getBalance()
    {
        try {
            $this->setResourceName('balance');
            $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->oCredentials);
            return $this->decode($oHttpClient->performRequest('GET'));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Balance Request can not be performed!');
        }
    }
    
    /**
    * Initiate a topup to recharge a user account
    * Only available for MTN Mobile Money Cameroon
    *
    * @param $hData, ['phonenumber' => '671234567', 'amount' => 1000]
    * @return mixed Trx
    */
    public function topup($hData)
    {
        try {
            $this->setResourceName('topup');
            $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->oCredentials);
            return $this->decode($oHttpClient->performRequest('POST', $hData));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('Topup Request can not be performed!');
        }
    }
    
    /**
    * Read a sent message by Id
    *
    * @param $xMessageId, message ID
    * @return mixed Message
    */
    public function view($xMessageId)
    {
        try {
            $this->setResourceName('view');
            $oHttpClient = new HttpClient($this->getEndPointUrl(), $this->oCredentials);
            return $this->decode($oHttpClient->performRequest('GET', ['id' => $xMessageId]));
        } catch (CamooSmsException $err) {
            throw new CamooSmsException('View Request can not be performed!');
        }
    }
}
