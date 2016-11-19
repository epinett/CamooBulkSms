<?php
 include_once( "src/CamooSms.php" );
    /**
     * @Brief View Message by message-id
     */
    // Step 1: Declare new CamooSms.
    $oSMS = new CamooSms('api_key', 'secret_key');
    var_export($oSMS->view("686874387367648440"));
