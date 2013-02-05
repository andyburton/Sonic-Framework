<?php

/**
 * Class to send text messsages. Currently uses the Esendex service.
 */

namespace Sonic\Resource;

class Sms extends Sms\Esendex\SendService
{

    /**
     * SMS Originator (alphanumeric characters only, and must be less than 11 characters).
     * @var string
     */
    
    private $originator     = SMS_ORIGINATOR;
    
    /**
     * Text message type (e.g. Text, SmartMessage, Binary or Unicode).
     * @var string
     */
    
    private $messageType    = 'Text';
    
    
    /**
     * Constructor
     * return \Sonic\Resource\Sms
     */
    
    public function __construct( )
    {
		parent::__construct (SMS_USERNAME, SMS_PASSWORD, SMS_ACCOUNT, FALSE, '');
    }
    
    
    /**
     * Send SMS/Text message via Esendex
     * @param $mobile_number number you wish to send text message,
     *        $message message that you are sending
     * @return array $result of the messageID
     */
    
    public function sendSms ($mobile_number, $message)
    {
        return $this->SendMessage ($mobile_number, $message, $this->messageType);
    }
    
    
    /**
     * Get the status of a SMS/Text Message from Esendex
     * @param $messageID of the message that you wish to find the status of
     * @return array $result 
     */
    
    public function getSmsMessageStatus ($messageID)
    {
        return $this->GetMessageStatus ($messageID);
    }
    
    
}
