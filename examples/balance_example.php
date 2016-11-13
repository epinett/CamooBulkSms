<?php
    include_once( "../src/CamooSms.php" );
    /**
     * @Brief read current balance
     *
     */
    // Step 1: Declare new CamooSms.
    $oSMS = new CamooSms('api_key', 'secret_key');
 
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


?>
