# CamooBulkSms
PHP SMS API Sending SMS via the CAMOO SMS gateway

Quick Examples

1) Sending an SMS

    $camoo_sms = new CamooBulkSms('account_key', 'account_secret');
    $orSMS = $camoo_sms->sendText( '+237623456790', 'MyApp', 'Hello Kmer world!' );
  
2) Display an overview of a successfully sent message

    echo $camoo_sms->displayOverview($orSMS);
    
Most Frequent Issues
--------------------

Sending a message returns false.

    This is usually due to your webserver unable to send a request to CAMOO. Make sure the following are met:

  1) Either CURL is enabled for your PHP installation or the PHP
      option 'allow_url_fopen' is set to 1 (default).

  2) You have no firewalls blocking access to https://api.camoo.cm/v1/sms.json
     on port 443.
   
Your message appears to have been sent but you do not recieve it.

    Run the example.php file included. This will show any errors that
    are returned from CAMOO.
