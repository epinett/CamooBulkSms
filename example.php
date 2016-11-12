<?php
    include_once( "src/CamooSms.php" );
    /**
     * @Brief Send a sms
     *
     */
    // Step 1: Declare new CamooBulkSms.
    $oSMS = new CamooSms('api_key', 'secret_key');
    // Step 2: Use sendText( $to, $from, $message ) method to send a message.
    $orSMS = $oSMS->sendText( '+237612345678', 'YourCompany', 'Hello kmer world!' );

// Optional
    // Step 3: Display an overview of the message
    echo $oSMS->displayOverview($orSMS);
    // Done!
?>
