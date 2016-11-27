# CamooBulkSms
PHP SMS API Sending SMS via the CAMOO SMS gateway

Requirement
-----------

This library needs minimum requirement for doing well on run.

      - [Sign up](https://www.camoo.cm/join) for a free CAMOO SMS account
      - Ask CAMOO Team for new access_key for developers
      - CAMOO SMS API client for PHP requires version 5.4.x and above

Quick Examples

1) Sending a SMS

    $oSMS = new CamooSms('account_key', 'account_secret');
    $orSMS = $oSMS->sendText( '+237623456790', 'MyApp', 'Hello Kmer world!' );
  
2) Display an overview of a successfully sent message

    echo $oSMS->displayOverview($orSMS);
    
3)  Send the same SMS to many recipients
            
            - Per request, a max of 50 recipients can be entered.
    
     $oSMS = new CamooSms('account_key', 'account_secret');
     $orSMS = $oSMS->sendText( ['+237623456790', '+237623456791', '+237623456792'], 'MyApp', 'Hello Kmer world!' );
     var_dump($orSMS);
    
Most Frequent Issues
--------------------

Sending a message returns false.

    This is usually due to your webserver unable to send a request to CAMOO. Make sure the following are met:

  1) Either CURL is enabled for your PHP installation or the PHP option 'allow_url_fopen' is set to 1 (default).

  2) You have no firewalls blocking access to https://api.camoo.cm/v1/sms.json on port 443.
   
Your message appears to have been sent but you do not recieve it.

    Run the example.php file included. This will show any errors that are returned from CAMOO.
    
Handle a status rapport
------------------------

Status rapports are requests that are sent to your platform through a GET request. The requests holds information about the status of a message that you have sent through our API. status rapports are only provided for messages that have configured their status rapport url.

ATTRIBUTES

    Attribute	    Type	    Description
    id	            string	     An unique random ID which is created on the CAMOO platform and is returned upon creation of the object.
    recipient	    string	     The recipient where this status rapport applies to.
    status	        string	     The status of the message sent to the recipient. Possible values: scheduled, sent, buffered, delivered, expired, anddelivery_failed
    statusDatetime	datetime    The datum time of this status in RFC3339 format date('Y-m-d H:i:s')

REQUEST

    GET http://your-own.url/script?id=b9389ur787874487486844&recipient=237612345678&status=delivered&statusDatetime=2016-11-05 13:35:35
    
RESPONSE

    200 OK
 
 Your platform should respond with a 200 OK HTTP header.
