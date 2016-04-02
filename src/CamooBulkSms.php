<?php
/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: CamooBulkSms.php
 * updated: Jan 2015
 * Created by: Epiphane Tchabom (e.tchabom@camoo.cm)
 * Description: CAMOO BULKSMS LIB
 *
 * @link http://www.camoo.cm
 */

/**
 * Class CamooBulkSms handles the methods and properties of sending an SMS message.
 *
 * Usage: $oSMS = new CamooBulkSms ( $account_key, $account_password );
 * Methods:
 *     sendText ( $to, $from, $message, $unicode = null )
 *     displayOverview( $camoo_response=null )
 *
 *     inboundText ( $data=null )
 *
 *
 */

class CamooBulkSms {

    // Camoo account credentials
    private $cm_key     = NULL;
    private $cm_secret  = NULL;

    /**
     * @var string Camoo server URI
     *
     * We're sticking with the JSON interface here since json
     * parsing is built into PHP and requires no extensions.
     * This will also keep any debugging to a minimum due to
     * not worrying about which parser is being used.
     */
    private $camoo_bulksms_uri = 'https://api.camoo.cm/v1/sms.json';


    /**
     * @var array The most recent parsed Camoo response.
     */
    private $camoo_response = '';

    // Current message
    public $to         = NULL;
    public $from       = NULL;
    public $text       = NULL;
    public $network    = NULL;
    public $message_id = NULL;

    // A few options
    public $ssl_verify = false; // Verify Camoo SSL before sending any message


    public function __construct ($api_key, $api_secret) {
        $this->cm_key = $api_key;
        $this->cm_secret = $api_secret;
    }

    /**
     * Prepare new text message.
     *
     * If $unicode is not provided we will try to detect the
     * message type. Otherwise set to TRUE if you require
     * unicode characters.
     */
    public function sendText ( $to, $from, $message, $unicode=null ) {

        // Making sure strings are UTF-8 encoded
        if ( !is_numeric($from) && !mb_check_encoding($from, 'UTF-8') ) {
            trigger_error('$from needs to be a valid UTF-8 encoded string');
            return false;
        }

        if ( !mb_check_encoding($message, 'UTF-8') ) {
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
        $sFrom = urlencode( $from );
        $sMessage = urlencode( $message );

        // Send away!
        $hPost = [
            'from'    => $sFrom,
            'to'      => $to,
            'message' => $sMessage,
            'type'    => $containsUnicode ? 'unicode' : 'text'
        ];
        return $this->sendRequest ( $hPost );
    }

    /**
     * Prepare and send a new message.
     */
    private function sendRequest ( $data ) {
        // Build the post data
        $data = array_merge($data, ['api_key' => $this->cm_key, 'api_secret' => $this->cm_secret]);
        $post = '';
        foreach($data as $k => $v){
            $post .= "&$k=$v";
        }

        // If available, use CURL
        if (function_exists('curl_version')) {

            $to_camoo = curl_init( $this->camoo_bulksms_uri );
            curl_setopt( $to_camoo, CURLOPT_POST, true );
            curl_setopt( $to_camoo, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $to_camoo, CURLOPT_POSTFIELDS, $post );

            if (!$this->ssl_verify) {
                curl_setopt( $to_camoo, CURLOPT_SSL_VERIFYPEER, false);
            }

            $from_camoo = curl_exec( $to_camoo );
            curl_close ( $to_camoo );

        } elseif (ini_get('allow_url_fopen')) {
            // No CURL available so try the awesome file_get_contents

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post
                )
            );
            $context = stream_context_create($opts);
            $from_camoo = file_get_contents($this->camoo_bulksms_uri, false, $context);

        } else {
            // No way of sending a HTTP post
            return false;
        }
        return $this->camooParse( $from_camoo );
    }


    /**
     * Recursively normalise any key names in an object, removing unwanted characters
     */
    private function normaliseKeys ($obj) {

        // Determine is working with a class or araay
        if ($obj instanceof stdClass) {
            $new_obj = new stdClass();
            $is_obj = true;
        } else {
            $new_obj = array();
            $is_obj = false;
        }


        foreach($obj as $key => $val){
            // If we come across another class/array, normalise it
            if ($val instanceof stdClass || is_array($val)) {
                $val = $this->normaliseKeys($val);
            }

            // Replace any unwanted characters in they key name
            if ($is_obj) {
                $new_obj->{str_replace('-', '', $key)} = $val;
            } else {
                $new_obj[str_replace('-', '', $key)] = $val;
            }
        }

        return $new_obj;
    }


    /**
     * Parse server response.
     */
    private function camooParse ( $from_camoo ) {
        $response = json_decode($from_camoo);
        // Copy the response data into an object, removing any '-' characters from the key
        $response_obj = $this->normaliseKeys($response);
        $response_obj = $response_obj->sms;

        if ($response_obj) {
            $this->camoo_response = $response_obj;

            // Find the total cost of this message
            $response_obj->cost = $total_cost = 0;
            if ( $response_obj->code == 200 && is_array($response_obj->messages) ) {
                foreach ($response_obj->messages as $msg) {
                    if (property_exists($msg, "messageprice")) {
                        $total_cost = $total_cost + (float)$msg->messageprice;
                    }
                }

                $response_obj->cost = $total_cost;
            }

            return $response_obj;

        } else {
            // A malformed response
            $this->camoo_response = [];
            return false;
        }
    }


    /**
     * @Brief Validate an originator string
     *
     * If the originator ('from' field) is invalid, some networks may reject the network
     * whilst stinging you with the financial cost! While this cannot correct them, it
     * will try its best to correctly format them.
     */
    private function validateOriginator($inp){
        // Remove any invalid characters
        $ret = preg_replace('/[^a-zA-Z0-9]/', '', (string)$inp);

        if(preg_match('/[a-zA-Z]/', $inp)){

            // Alphanumeric format so make sure it's < 11 chars
            $ret = substr($ret, 0, 11);

        } else {

            // Numerical, remove any prepending '00'
            if(substr($ret, 0, 2) == '00'){
                $ret = substr($ret, 2);
                $ret = substr($ret, 0, 15);
            }
        }

        return (string)$ret;
    }


    /**
     * @Brief Display a brief overview of a sent message.
     * Useful for debugging and quick-start purposes.
     */
    public function displayOverview( $oResponse =null ){
        $orInfo = ( $oResponse !== null ) ? $this->camoo_response : $oResponse;

        if (!$oResponse || $oResponse->code != 200) return 'Cannot display an overview of this response';
        #$info = $oResponse->sms;
        $info = $oResponse;
        // How many messages were sent?
        if ( $info->messagecount > 1 ) {

            $status = 'Your message was sent in ' . $info->messagecount . ' parts';

        } elseif ( $info->messagecount == 1) {

            $status = 'Your message was sent';

        } else {

            return 'There was an error sending your message';
        }

        // Build an array of each message status and ID

        $message_status = [];
        foreach ( $info->messages as $message ) {
            $tmp = array('id'=>'', 'status'=>0);

            if ( $message->status != 0) {
                $tmp['status'] = $message->errortext;
            } else {
                $tmp['status'] = 'OK';
                $tmp['id'] = $message->messageid;
            }

            $message_status[] = $tmp;
        }

        // Build the output
        if (isset($_SERVER['HTTP_HOST'])) {
            // HTML output
            $ret = '<table><tr><td colspan="2">'.$status.'</td></tr>';
            $ret .= '<tr><th>Status</th><th>Message ID</th></tr>';
            foreach ($message_status as $mstat) {
                $ret .= '<tr><td>'.$mstat['status'].'</td><td>'.$mstat['id'].'</td></tr>';
            }
            $ret .= '</table>';

        } else {

            // CLI output
            $ret = "$status:\n";

            // Get the sizes for the table
            $out_sizes = array('id'=>strlen('Message ID'), 'status'=>strlen('Status'));
            foreach ($message_status as $mstat) {
                if ($out_sizes['id'] < strlen($mstat['id'])) {
                    $out_sizes['id'] = strlen($mstat['id']);
                }
                if ($out_sizes['status'] < strlen($mstat['status'])) {
                    $out_sizes['status'] = strlen($mstat['status']);
                }
            }

            $ret .= '  '.str_pad('Status', $out_sizes['status'], ' ').'   ';
            $ret .= str_pad('Message ID', $out_sizes['id'], ' ')."\n";
            foreach ($message_status as $mstat) {
                $ret .= '  '.str_pad($mstat['status'], $out_sizes['status'], ' ').'   ';
                $ret .= str_pad($mstat['id'], $out_sizes['id'], ' ')."\n";
            }
        }

        return $ret;
    }

}
