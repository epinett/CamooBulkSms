<?php
    include_once( "src/CamooBulkSms.php" );
    /**
     * @Brief Send a sms
     *
     */
    // Step 1: Declare new CamooBulkSms.
    $oSMS = new CamooBulkSms('api_key', 'secret_key');
    // Step 2: Use sendText( $to, $from, $message ) method to send a message.
    $orSMS = $oSMS->sendText( '+237612345678', 'YourCompany', 'Hello kmer world!' );

// Optional
    // Step 3: Display an overview of the message
    echo $camoo_sms->displayOverview($info);
    // Done!
?>
