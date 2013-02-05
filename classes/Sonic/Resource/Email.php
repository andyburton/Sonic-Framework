<?php

/**
 * Provides an OO approach to creating and sending multi-part MIME Emails to specified recipients.
 * Supports html, images, attachments email address checking and multiple recipients.
 * Email parsing features are also available to add recipient specific information in emails using smarty templating.
 * Multiple recipients are also dealt with using an smtp stream to make things a alot quicker.
 */


// Define namespace

namespace Sonic\Resource;

// Start Email Class

class Email
{
	
	
	/**
	 * Headers
	 * @var array
	 */
	
	private $_headers			= array ();
	
	/**
	 * Plain Data
	 * @var array
	 */
	
	private $_plain				= array ();
	
	/**
	 * HTML Data
	 * @var array
	 */
	
	private $_html				= array ();
	
	/**
	 * Images
	 * @var array
     */
	
	private $_images          	= array ();
	
	/**
	 * Image MIME Types Allowed
	 * @var array
	 */
	
	private $_imagesAllowed		= array (IMG_GIF, IMG_JPEG);
	
	/**
	 * Attachments
	 * @var array
	 */
	
	private $_attachments		= array ();
	
	/**
	 * Template file
	 * @var string
	 */
	
	private $_tpl				= FALSE;
	
	/**
	 * Message Body
	 * @var array
	 */
	
	private $_body				= array ();
	
	/**
	 * Recipients
	 * @var array
	 */
	
	private $_recipients		= array ();
	
	/**
	 * Message Subject
	 * @var string
	 */
	
	private $_subject			= NULL;
	
	/**
	 * Message
	 * @var string
	 */
	
	private $_message			= NULL;
	
	/***
	 * HTML Data
	 * @var boolean
	 */
	
	private $_htmlSet			= FALSE;
	
	/**
	 * Images
	 * @var boolean
	 */
	
	private $_imagesSet			= FALSE;
	
	/**
	 * Attachments
	 * @var boolean
	 */
	
	private $_attachmentsSet	= FALSE;
	
	/**
	 * Body
	 * @var boolean
	 */
	
	private $_bodySet			= FALSE;
	
    /**
     * Use SMTP stream
     * @var boolean
     */

    private $_useStream			= TRUE;
	
	/**
	 * New Line
	 * @var string
	 */
	
	private $_nl				= "\r\n";

	/**
	* From name
	* @var string
	*/

	private $_fromName			= FALSE;

	/**
	* From email address
	* @var string
	*/

	private $_fromAddress		= FALSE;
	
	/**
	 * SMTP Settings
	 * @var array
     */
	
	protected $smtp				= array ();
	
	/**
	 * Set log status
	 * @var boolean
	 */

        public $logFlag				= FALSE;

        
        /**
	 * Set log status
	 * @var boolean
	 */

        private $_callbackMethod		= FALSE;

        
        
	
	/**
	 * Instantiate the class
	 * @return \Sonic\Resource\Email
	 */
	
	public function __construct ()
	{
		
		// If we're using SMTP stream

        if ($this->_useStream)
        {

            // Set send mode to stream

            $this->setSMTPStream ();

        }

        // Else

        else
        {

            // Set send mode to PHP

            $this->setPHPMail ();

        }

		// Set log status

		if (defined ('EMAIL_LOG') && EMAIL_LOG === TRUE && 
			defined ('EMAIL_LOG_PATH'))
		{
			$this->logFlag	= TRUE;
		}
		
	}
	

    /**
     * Use SMTP stream to send mail
     * @param array $smtp SMTP parameter array
     *   server, port, domain, username, password
     * @return void
     */

    public function setSMTPStream ($smtp = FALSE)
    {

        // If an SMTP parameter array has been passed

        if (is_array ($smtp))
        {

            // Set it

            $this->smtp	= $smtp;

        }

        // Else set details

        else
        {

            // SMTP Settings

            $this->smtp		= array (
                'server'		=> defined ('EMAIL_SERVER')? EMAIL_SERVER : NULL,
                'port'			=> defined ('EMAIL_PORT')? EMAIL_PORT : NULL,
                'domain'		=> defined ('EMAIL_DOMAIN')? EMAIL_DOMAIN : NULL,
                'username'		=> defined ('EMAIL_USERNAME')? EMAIL_USERNAME : NULL,
                'password'		=> defined ('EMAIL_PASSWORD')? EMAIL_PASSWORD : NULL
            );

        }

        // Set stream mode

        $this->_useStream	= TRUE;

        // Reset NL

        $this->_nl			= "\r\n";

    }


    /**
     * Use PHP to send mail
     * @return void
     */

    public function setPHPMail ()
    {

        // Set PHP mode

        $this->_useStream	= FALSE;

        // Change NL to single \n if we're using Qmail

        if (isset ($_SERVER['QMAIL']))
        {
            $this->_nl	= "\n";
        }
        else
        {
            $this->_nl	= "\r\n";
        }
        
    }


	/**
	 * Add Recipient
	 * @param string $email Email Address
	 * @param string $name Recipient Name
	 * @param array $details Recipient Details
	 * @return void
	 */
	
	public function addRecipient ($email, $name, $details = array ())
	{

        // Add email and name to details

        $details['email']	= $email;
        $details['name']		= $name;

		// Add the recipient
		
		$this->_recipients[]	= $details;
		
	}
	
	
	/**
	 * Remove Recipient
	 * @param string $email Email Address
	 * @return void
	 */
	
	public function removeRecipient ($email)
	{

        // Remove recipients that match the email address

        foreach ($this->_recipients as $key => $val)
        {

            if ($val['email'] == $email)
            {
                unset ($this->_recipients[$key]);
            }

        }
		
	}
	
	
	/**
	 * Return the recipient array
	 * @return array
	 */
	
	public function getRecipients ()
	{
		return $this->_recipients;
	}
	
	
	/**
	 * Set the message subject
	 * @param string $subject Message Subject
	 * @return void
	 */
	
	public function setSubject ($subject)
	{
		$this->_subject	= $subject;
	}
	
	
	/**
	 * Add Email Header
	 * @param string|array $header Headers
     * @param boolean $unique Header is unique
     *   Overwrite the original value if it is
	 * @return boid
	 */
	
	private function addHeader ($header, $unique = FALSE)
	{
		
		// If the header is an array
		
		if (is_array ($header))
		{
            
			// Merge the headers arrays
			
			$this->_headers	= array_merge ($this->_headers, $header);
			
		}
		else
		{

            // If unique remove any duplicates

            if ($unique)
            {

                $headerArray	= explode (':', $header);
                $headerKey		= $headerArray[0];
				
                foreach ($this->_headers as $key => $val)
                {
					
                    if (stripos ($val, $headerKey) === 0)
                    {

                        unset ($this->_headers[$key]);

                    }

                }

            }

			// Add the header to the headers array

			$this->_headers[]	= $header;

		}
		
	}
	
	
	/**
	 * Add HTML to Email Message
	 * @param string $html HTML Data
	 * @param string $plain Plain Equivelant
	 * @return void
	 */
	
	public function addHTML ($html, $plain = FALSE)
	{
		
		// Set the HTML boolean to true
		
		$this->_htmlSet	= TRUE;
		
		// If there is a plain equivalent
		
		if ($plain)
		{
			
			// Add it to the plain array
			
			$this->addPlain ($plain);
			
		}
		
		// Add the HTML to the HTML array
		
		$this->_html[]	= $html;
		
	}
	
	
	/**
	 * Set plain text
	 * @param string $plain Plain Text
	 */
	
	public function addPlain ($plain)
	{
		$this->_plain[]	= $plain;
	}
	
	
	/**
	 * Add Image to Email Message
	 * @param string $filePath File Path
	 * @param string $fileName File Name
	 * @return boolean
	 */
	
	public function addImage ($filePath, $fileName = NULL)
	{
		
		// Check Image Exists
		
		if (!file_exists ($filePath))
		{
			
			// Add error and return false
			
			new \Sonic\Message ('error', $filePath . ' doesnt exist!');
			return FALSE;
			
		}
		
		// Get MIME Type
		
		if (!$imgData = getimagesize ($filePath))
		{
			
			// Add error and return false
			
			new \Sonic\Message ('error', 'Cannot get MIME type!');
			return FALSE;
			
		}
		
		// Check Image is allowed
		
		if (!in_array ($imgData[2], $this->_imagesAllowed))
		{
			
			// Set variables
			
			$allowedTypes	= NULL;
			$x				= 0;
			
			// Generate the allowed images string
			
			foreach ($this->_imagesAllowed as $type)
			{
				
				$x++;
				
				$allowedTypes .= image_type_to_extension ($type);
				
				if ($x < count ($this->_imagesAllowed))
				{
					$allowedTypes	.= ', ';
				}
				
			}
			
			// Add error and return false
			
			new \Sonic\Message ('error', 'Invalid image type: image must be ' . $allowedTypes);
			return FALSE;
			
		}
		
		// Get Image Data
		
		if (!$fileData = @file_get_contents ($filePath))
		{
			
			// Add error and return false
			
			new \Sonic\Message ('error', 'Cannot get image data!');
			return FALSE;
			
		}
		
		// Set images boolean
		
		$this->_imagesSet	= TRUE;
		
		// Add Image
		
		$this->_images[]	= array (
            'name'				=> $fileName,
            'data'				=> $fileData,
            'type'				=> image_type_to_mime_type ($imgData[2]),
            'cid'				=> md5 (uniqid (time ()))
		);
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Add Attachment to Email Message
	 * @param string $filePath File Path
	 * @param string $fileName File Name
	 * @param string $fileData File Data
	 * @return boolean
	 */
	
	public function addAttachment ($filePath = FALSE, $fileName = NULL, $fileData = FALSE)
	{
		
		// If its an existing file
		
		if ($filePath !== FALSE)
		{
			
			// Check File Exists
			
			if (!file_exists ($filePath))
			{
				
				// Add error and return false

				new \Sonic\Message ('error', $filePath . ' doesnt exist!');
				return FALSE;
				
			}

			// Get MIME Type

            $objFinfo   = new finfo;

			if (!$mimeType = $objFinfo->file ($filePath, FILEINFO_MIME))
			{
				
				// Add error and return false

				new \Sonic\Message ('error', 'Cannot get MIME type!');
				return FALSE;
				
			}
			
			// Get File Data
			
			if (!$fileData = @file_get_contents ($filePath))
			{
				
				// Add error and return false

				new \Sonic\Message ('error', 'Cannot get attachment data!');
				return FALSE;
				
			}
			
		}
		else if ($filePath === FALSE && $fileData !== FALSE)
		{
			
			// If the data is valid
			
			$mimeType =	'text/plain';
			
		}
		else
		{

			// Add error and return false

			new \Sonic\Message ('error', 'Invalid Attachment!');
			return FALSE;
			
		}
		
		// Set attachments boolean
		
		$this->_attachmentsSet	= TRUE;
		
		// Add Attachment
		
		$this->_attachments[]	= array (
            'name'		=> $fileName,
            'data'		=> $fileData,
            'type'		=> $mimeType
		);
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Set the Message Body
	 * @param string $body Message Body
	 * @return boolean
	 */
	
	public function addBody ($body)
	{
		
		// Add body string to body array
		
		$this->_bodySet	= TRUE;
		$this->_body[]	= $body;
		
	}
	
	
	/**
	 * Set the message template file to user
	 * @param string $tpl Template file path
	 * @return void
	 */
	
	public function setTemplate ($file)
	{
		
		// Get template resource

		$tpl	= \Sonic\Sonic::getResource ('tpl');

		// If it's smarty and the template path doesn't exist
		
		if ($tpl instanceof \Smarty && !is_readable ($file))
		{
			
			// Set in the smarty template dir
			
			$file	= $tpl->getTemplateDir (0) . $file;
			
		}
		
		// If the template is readable then set it
		
		if (is_readable ($file))
		{
			$this->_tpl	= $file;
		}
		
	}
	
	
	/**
	 * Construct the HTML part of the messages
	 * @param string $firstBoundary Original message boundary
	 * @return string
	 */
	
	private function constructHTML ($firstBoundary)
	{
		
		// Reset the message
		
		$message		= NULL;
		
		// Set boundaries
		
		$secondBoundary	= '=====' . md5 (uniqid (time ()));
		$thirdBoundary	= '=====' . md5 (uniqid (time ()));
		
		// Get HTML and Plain strings
		
		$html			= NULL;
		$plain			= NULL;
		
		foreach ($this->_html as $htmlBlock)
		{
			$html .= $htmlBlock;
		}
		
		if ($this->_plain)
		{
			
			foreach ($this->_plain as $plainBlock)
			{
				$plain .= $plainBlock;
			}
			
		}
		else
		{
			$plain	= strip_tags ($html);
		}
		
		// If there are images
		
		if ($this->_imagesSet)
		{
			
			// Replace images with CID in HTML message
			
			foreach ($this->_images as $image)
			{
				
				$html	= preg_replace ($image['name'], 'cid:' . $image['cid'], $html);
				
			}
			
			// Generate HTML Message
			
			$message	.= '--' . $firstBoundary . $this->_nl;
			$message	.= 'Content-Type: multipart/related;' . $this->_nl . chr (9);
			$message	.= 'boundary="' . $secondBoundary . '"' . str_repeat ($this->_nl, 3);
			
			$message	.= '--' . $secondBoundary . $this->_nl;
			$message	.= 'Content-Type: Multipart/alternative;' . $this->_nl . chr (9);
			$message	.= 'boundary="' . $thirdBoundary . '"' . str_repeat ($this->_nl, 3);
			
			$message	.= '--' . $thirdBoundary . $this->_nl;
			$message	.= 'Content-Type: text/plain; "charset=iso-8859-1"' . $this->_nl;
			$message	.= 'Content-Transfer-Encoding: 8bit' . str_repeat ($this->_nl, 2);
			$message	.= $plain . str_repeat ($this->_nl, 2);
			
			$message	.= '--' . $thirdBoundary . $this->_nl;
			$message	.= 'Content-Type: text/html; "charset=iso-8859-1"' . $this->_nl;
			$message	.= 'Content-Transfer-Encoding: 8bit' . str_repeat ($this->_nl, 2);
			$message	.= $html . str_repeat ($this->_nl, 2);
			$message	.= '--' . $thirdBoundary . '--' . str_repeat ($this->_nl, 2);
			
			// Add Images
			
			foreach ($this->_images as $image)
			{
				$message	.= $this->constructImage ($image, $secondBoundary);
			}
			
			// End HTML
			
			$message	.= '--' . $secondBoundary . '--' . str_repeat ($this->_nl, 2);
			
		}
		else
		{
			
			// Generate HTML Message
			
			if ($plain)
			{
				
				$message	.= '--' . $firstBoundary . $this->_nl;
				$message	.= 'Content-Type: text/plain; charset="iso-8859-1"' . $this->_nl;
				$message	.= 'Content-Transfer-Encoding: 8bit' . str_repeat ($this->_nl, 2);
				$message	.= $plain . str_repeat ($this->_nl, 2);
				
			}
			
			if ($html)
			{
				
				$message	.= '--' . $firstBoundary . $this->_nl;
				$message	.= 'Content-Type: text/html; "charset=iso-8859-1"' . $this->_nl;
				$message	.= 'Content-Transfer-Encoding: 8bit' . str_repeat ($this->_nl, 2);
				$message	.= $html . str_repeat ($this->_nl, 2);
				
			}
			
		}
		
		// Return message
		
		return $message;
		
	}
	
	
	/**
	 * Construct the specified image and add it to the message
	 * @param array $image Image to construct
	 * @param string $boundary Part Boundary
	 * @return string
	 */
	
	private function constructImage ($image, $boundary)
	{
		
		// Set variables
		
		$output	= NULL;
		
		// Add the boundary
		
		$output	.= '--' . $boundary . $this->_nl;
		
		// Add the image to the message
		
		$output	.= 'Content-Type: ' . $image['type'];
		
		if ($image['name'])
		{
			$output	.= '; name="' . $image['name'] . '"';
		}
		
		$output	.= $this->_nl;
		$output	.= 'Content-Transfer-Encoding: base64' . $this->_nl;
		$output	.= 'Content-ID: <' . $image['cid'] . '>' . str_repeat ($this->_nl, 2);
		$output	.= chunk_split (base64_encode ($image['data']), 70) . $this->_nl;
		
		// Return image part
		
		return $output;
		
	}
	
	
	/**
	 * Construct the specified attachment and add it to the message
	 * @param array $attachment Attachment to construct
	 * @param string $boundary Pary Boundary
	 * return string
	 */
	
	private function constructAttachment ($attachment, $boundary)
	{
		
		// Set variables
		
		$output	= NULL;
		
		// Add the boundary
		
		$output	.= '--' . $boundary . $this->_nl;
		
		// Add the attachment to the message
		
		$output	.= 'Content-Type: ' . $attachment['type'];
		
		if ($attachment['name'])
		{
			$output	.= '; name="' . $attachment['name'] . '"';
		}
		
		$output	.= $this->_nl;
		$output	.= 'Content-Transfer-Encoding: base64' . $this->_nl;
		
		// Add attachment as plain text if its the body, otherwise add as a file attachment
		
		if ($attachment['type'] == 'text/plain')
		{
			$output	.= $this->_nl;
		}
		else
		{
			$output	.= 'Content-Disposition: attachment; filename="' . $attachment['name'] . '"' . str_repeat ($this->_nl, 2);
		}
		
		$output	.= chunk_split (base64_encode ($attachment['data']), 70) . $this->_nl;
		
		// Return attachment part
		
		return $output;
		
	}
	
	
	/**
	 * Construct the message body
	 * @return boolean
	 */
	
	private function constructBody ()
	{
		
		// Set variables
		
		$body	= NULL;
		
		// Create body string
		
		foreach ($this->_body as $bodyBlock)
		{
			$body .= $bodyBlock;
		}
		
		// Add as an attachment
		
		return $this->addAttachment (FALSE, NULL, $body);
		
	}
	
	
	/**
	 * Construct the message headers
	 * @return string
	 */
	
	private function constructHeaders ()
	{
		
		// Set variables
		
		$headers	= NULL;
		
		// Make header string
		
		foreach ($this->_headers as $header)
		{
			$headers .= $header . $this->_nl;
		}
		
		// Return headers
		
		return $headers;
		
	}
	
	
	/**
	 * Construct the email message
	 * @return boolean
	 */
	
	public function Build ()
	{
		
		// Create Boundaries
		
		$boundary		= '=====' . md5 (uniqid (time ()));

        // Reset headers

        $this->_headers	= array ();

		// Add MIME Headers
		
		$this->addHeader ('MIME-version: 1.0');
		$this->addHeader ('Content-Type: Multipart/alternative;' . $this->_nl . chr (9) . 'boundary="' . $boundary . '"');
		
		// Start Message
		
		$this->_message	= NULL;
		
		// If there is a template file
		
		if ($this->_tpl)
		{
			
			// Load it and add to html
			
			$this->addHTML (@file_get_contents ($this->_tpl));
			
		}
		
		// If HTML data
		
		if ($this->_htmlSet || $this->_plain)
		{
			
			// Construct the HTML and add it to the message
			
			$this->_message	.= $this->constructHTML ($boundary);
			
		}
		
		// If Body
		
		if ($this->_bodySet)
		{
			
			// Construct the body
			
			if (!$this->constructBody ())
			{
				
				// Add error and return FALSE
				
				new \Sonic\Message ('error', 'Cannot add message body!');
				return FALSE;
				
			}
			
		}
		
		// If Attachments
		
		if ($this->_attachmentsSet)
		{
			
			// Add them to the message
			
			foreach ($this->_attachments as $attachment)
			{
				$this->_message	.= $this->constructAttachment ($attachment, $boundary);
			}
			
		}
		
		// Finish Message
		
		$this->_message	.= '--' . $boundary . '--' . $this->_nl;
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	
	/**
	 * Construct message, if not done already, and send email.
	 * Attribute to and from addresses will replace any to or from headers
	 * @param string $from Sender of the email in the format Name<user@domain.com>
	 * @param string $subject Email subject
     * @param array $headers Additional mail headers
	 * @return boolean
	 */
	
	public function Send ($from, $subject = FALSE, $headers = FALSE)
	{

        // If the sender is not in the name <email> format

        if (preg_match ('/(.*?)<(.*?)>/', $from, $fromArray))
        {

            // Set the name and address

            $this->_fromName	= trim ($fromArray[1]);
            $this->_fromAddress	= $fromArray[2];
			
			// Validate the name and email
			
			try
			{
				
				Parser::_Validate ('From name', array (
					'type'		=> \Sonic\Model::TYPE_STRING,
					'charset'	=> 'alpha',
					'min'		=> 1,
					'max'		=> 255
				), $this->_fromName);
				
				Parser::_validateEmail ($this->_fromAddress);
				
			}
			catch (Parser\Exception $e)
			{

				// Add error and return FALSE

				new \Sonic\Message ('error', $e->getMessage ());
				return FALSE;

			}

        }
        else
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'Invalid from format, must be Name<user@domain.com>: ' . $from);
            return FALSE;

        }

        // If a subject has been passed set it

        if ($subject !== FALSE)
        {
            $this->setSubject ($subject);
        }

        // If here is no subject

        if (!$this->_subject)
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'No message subject!');
            return FALSE;

        }

		// Build message

        if (!$this->Build ())
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'Cannot build the message!');
            return FALSE;

        }

        // Add additional headers

        if ($headers !== FALSE)
        {
            $this->addHeader ($headers);
        }

		// Add subject header

        if ($this->_subject)
        {
            $this->addHeader ('Subject: ' . $this->_subject, TRUE);
        }

        // Add from header

        $this->addHeader ('From: ' . $this->_fromName . '<' . $this->_fromAddress . '>', TRUE);

        // Add date header

        $this->addHeader ('Date: ' . date ('r'), TRUE);

        // Send using correct method

        if ($this->_useStream)
        {

            // SMTP Stream

            return $this->sendUsingStream ();

        }
        else
        {

            // PHP Mail

            return $this->sendUsingPHP ();

        }

    }


    /**
     * Send email to recipients using SMTP stream
     * @return boolean
     */

    private function sendUsingStream ()
    {
        
		// Construct Headers
		
		$headers	= $this->constructHeaders ();
		
        // Create a new stream object

        $stream	= new Stream ('tcp://' . $this->smtp['server'], $this->smtp['port']);

        // If there was a stream error

        if ($stream->isError ())
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', $stream->getErrors (TRUE));
            return FALSE;

        }

        // Receive

        if (!$stream->Receive ('220'))
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'Initial Receive Failed: ' . $stream->getErrors (TRUE));
            return FALSE;

        }
		
        // Send EHLO

        if (!$stream->Send ('EHLO ' . $this->smtp['domain'], array ('220','250')))
        {
			
			// Attempt a second time, some SMTP servers seem to have a problem
			// where somewhat space is sent which causes the first EHLO to fail
			
			if (!$stream->Send ('EHLO ' . $this->smtp['domain'], array ('220','250')))
			{

				// Add error and return FALSE

				new \Sonic\Message ('error', 'EHLO Failed: ' . $stream->getErrors (TRUE));
				return FALSE;
				
			}

        }

        // Authenticate

        if ($this->smtp['username'] && $this->smtp['password'] && !$stream->Send ('AUTH LOGIN', array ('250','334')))
        {

            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'AUTH LOGIN Failed: ' . $stream->getErrors (TRUE));
            return FALSE;

        }
		
		// Username

		if ($this->smtp['username'] && !$stream->Send (base64_encode ($this->smtp['username']), array ('250','334')))
		{
			
            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'USERNAME Failed: ' . $stream->getErrors (TRUE));
            return FALSE;

		}

		// Password

		if ($this->smtp['password'] && !$stream->Send (base64_encode ($this->smtp['password']), '235'))
		{
			
            // Add error and return FALSE
			
			new \Sonic\Message ('error', 'PASSWORD Failed: ' . $stream->getErrors (TRUE));
            return FALSE;

		}

		// Get template resource

		$tpl	= \Sonic\Sonic::getResource ('tpl');

		// Set smarty template status

		$smarty	= $tpl instanceof \Smarty;
		
		// If we have a smarty template object

		if ($smarty)
		{
			
			// Get caching status

			$smartyPrevCache	= $tpl->caching;

			// Disable caching

			$tpl->setCaching (FALSE);

		}

		// For each recipient

		foreach ($this->_recipients as $recipient)
		{
			
			// Send email
			
			try
			{
			
				// Check to make sure the recipients email address is valid
				
				Parser::_validateEmail ($recipient['email']);

				// Send Mail From

				if (!$stream->Send ('MAIL FROM:' . $this->_fromAddress, '250'))
				{

					// Add error and return FALSE

					new \Sonic\Message ('error', 'MAIL FROM Failed: ' . $stream->getErrors (TRUE));
					return FALSE;
					
				}

				// Send RCPT TO

				if (!$stream->Send ('RCPT TO:' . $recipient['email'], '250'))
				{

					// Add error and return FALSE

					new \Sonic\Message ('error', 'RCPT TO Failed: ' . $stream->getErrors (TRUE));
					return FALSE;

				}

				// Send DATA

				if (!$stream->Send ('DATA', array ('250','354','451')))
				{

					// Add error and return FALSE

					new \Sonic\Message ('error', 'DATA Failed: ' . $stream->getErrors (TRUE));
					return FALSE;

				}

				// Add recipient header to original headers

				$emailHeaders	= $headers . 'To: ' . $recipient['name'] . '<' . $recipient['email'] . '>' . $this->_nl;

				// If we're using smarty

				if ($smarty)
				{
					
					// Clear variables

					$tpl->clearAllAssign ();

					// Assign variables

					$tpl->assign ('recipient', $recipient);

					// Fetch

					$emailMessage	= $tpl->fetch ('string:' . $this->_message);

				}

				// Else we're not using smarty

				else
				{

					// Just leave the message as it is
					
					$emailMessage	= $this->_message;

				}

				// Generate message to send

				$message	= array (
					$emailHeaders,
					'',
					$emailMessage,
					'',
					'.'
				);

				// Send Message
				
				if (!$stream->Send ($message, '250'))
				{

					// Add error and return FALSE

					new \Sonic\Message ('error', 'DATA Failed: ' . $stream->getErrors (TRUE));
					return FALSE;

				}
                                
                                if (is_callable($this->_callbackMethod)) 
                                        call_user_func($this->_callbackMethod, $this->_subject, $recipient['email'], $this->_fromAddress, $emailMessage);

				// If we're logging the email

				if ($this->logFlag)
				{

					// Log

					$this->Log ($recipient['email'], $emailHeaders, $emailMessage, 'stream');

				}

			}

			// Else the email address is not valid

			catch (Parser\Exception $e)
			{

				// Add error

				new \Sonic\Message ('error', 'Invalid recipient email address: ' . $recipient['email']);

				// Skip to next recipient

				continue;

			}

		}

		// If we're using smarty

		if ($smarty)
		{

			// Reset caching status

			$tpl->caching	= $smartyPrevCache;

		}
		
		// Send QUIT

		if (!$stream->Send ('QUIT'))
		{

			// Add error

			new \Sonic\Message ('error', 'QUIT Failed: ' . $stream->getErrors (TRUE));

		}

		// Return TRUE

		return TRUE;

	}


    /**
     * Send email to recipients using PHP mail
     * @return boolean
     */

    private function sendUsingPHP ()
    {

		// Construct Headers

		$headers	= $this->constructHeaders ();

		// Get template resource

		$tpl	= \Sonic\Sonic::getResource ('tpl');

		// Set smarty template status

		$smarty	= $tpl instanceof \Smarty;
		
		// If we have a smarty template object

		if ($smarty)
		{
			
			// Get caching status

			$smartyPrevCache	= $tpl->caching;

			// Disable caching

			$tpl->setCaching (FALSE);

		}

		// For each recipient

		foreach ($this->_recipients as $recipient)
		{
			
			// Send email
			
			try
			{
			
				// Check to make sure the recipients email address is valid
				
				Parser::_validateEmail ($recipient['email']);
				
				// Set recipient

				$recipientTo	= $recipient['name'] . '<' . $recipient['email'] . '>';
				
				// Add recipient header to original headers

				$emailHeaders	= $headers . 'To: ' . $recipientTo . $this->_nl;

				// If we're using smarty

				if ($smarty)
				{

					// Clear variables

					$tpl->clearAllAssign ();

					// Assign variables

					$tpl->assign ('recipient', $recipient);

					// Fetch

					$emailMessage	= $tpl->fetch ('string:' . $this->_message);

				}

				// Else we're not using smarty

				else
				{

					// Just leave the message as it is

					$emailMessage	= $this->_message;

				}

				// Send Message
				
				if (!mail ($recipientTo, $this->_subject, $emailMessage, $emailHeaders))
				{

					// Add error and return FALSE

					new \Sonic\Message ('error', 'Cannot send message to: ' . $recipient['email']);

					// return FALSE

					return FALSE;

				}

                                if (is_callable($this->_callbackMethod)) 
                                        call_user_func($this->_callbackMethod, $this->_subject, $recipientTo, $this->_fromAddress, $emailMessage);

                                
				// If we're logging the email

				if ($this->logFlag)
				{

					// Log

					$this->Log ($recipient['email'], $emailHeaders, $emailMessage, 'phpmail');

				}
				
			}

			// Else the email address is not valid

			catch (Parser\Exception $e)
			{

				// Add error

				new \Sonic\Message ('error', 'Invalid recipient email address: ' . $recipient['email']);

				// Skip to next recipient

				continue;

			}

		}

		// If we're using smarty

		if ($smarty)
		{

			// Set caching status

			$tpl->caching	= $smartyPrevCache;

		}

		// Return TRUE

		return TRUE;

	}



	/**
	 * Log sent message
	 * @param string $recipient Recipient email address
	 * @param string $headers Email headers string
	 * @param string $email Email content
	 * @param string $method Send method
	 * @return void
	 */

	protected function Log ($recipient, $headers, $email, $method)
	{

		// Set filename

		$filename	= date ('Y-m-d_H-i-s') . '_' . $method . '_' . $recipient . '.txt';

		// Log

		@file_put_contents (EMAIL_LOG_PATH . $filename, $headers . $email);

	}

        protected function SetCallback($callback)
        {
            $this->_callbackMethod = $callback;
        }

	
}

// End Email Class