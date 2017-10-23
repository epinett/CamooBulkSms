<?php
    require_once(dirname(__DIR__)."/src/Sms.php");
    /**
     * @Brief View Message by message-id
     */
    // Step 1: Declare new Camoo\Sms\Sms.
    $oSMS = new Camoo\Sms\Sms('api_key', 'secret_key');
    var_export($oSMS->view("686874387367648440"));
