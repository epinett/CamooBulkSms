<?php
 include_once( "src/CamooSms.php" );
    /**
     * @Brief recharge user account
     * Only available for Mobile Money MTN Cameroon Ltd
     */
    // Step 1: Declare new CamooSms.
    $oSMS = new CamooSms('api_key', 'secret_key');

    var_export($oSMS->topup(['phonenumber' => '671234567', 'amount' => 4000]));

// output:
/*
stdClass Object
(
    [message] => pending
    [topup] => stdClass Object
        (
            [payment_id] => 40
            [completed] => 0
        )

    [code] => 200
)

// Step2 :
    - Dial *126*1#
    - Choose option to authorize the transaction
    - Enter your MTN Mobile Money PIN
    - Choose the option to approve the Payment
    - Choose option and confirm  
*/



?>                  
