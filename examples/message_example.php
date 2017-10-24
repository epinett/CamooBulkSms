<?php
    require_once(dirname(__DIR__)."/src/Sms.php");
    /**
     * @Brief Send a sms
     *
     */
    // Step 1: Declare new Camoo\Sms\Sms.
    $oSMS = new Camoo\Sms\Sms('api_key', 'secret_key');
    // Step 2: Use sendText( $to, $from, $message ) method to send a message.
    $orSMS = $oSMS->sendText('+237612345678', 'YourCompany', 'Hello kmer world!');

    var_dump($orSMS);
    // Done!

    // OR Send the same message to multi-recipients
    // Per request, a max of 50 recipients can be entered.
     //$orSMS = $oSMS->sendText( ['+237612345678', '+237612345679', '+237612345610',], 'YourCompany', 'Hello kmer world!' );
