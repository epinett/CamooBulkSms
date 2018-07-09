<?php
    require_once(dirname(__DIR__)."/src/Sms.php");
    /**
     * @Brief read current balance
     *
     */
    // Step 1: Declare new Camoo\Sms\Sms.
    $oSMS = new Camoo\Sms\Sms('api_key', 'secret_key');
 
    var_export($oSMS->getBalance());

// output:
/*
stdClass Object
(
    [message] => OK
    [balance] => stdClass Object
        (
            [balance] => 910
            [currency] => XAF
        )

)*/
