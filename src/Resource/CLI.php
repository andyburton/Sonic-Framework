<?php

// Define namespace

namespace Sonic\Resource;

// Start CLI Class

class CLI
{
	
	
	/**
	 * Output a log entry
	 * @param string $status Log status e.g. error, success, debug
	 * @param string $msg Message to output
	 * @param boolean $nl Add newline to the output
	 * @param boolean $email Email to admin, default false
	 * @return boolean
	 */
	
	public static function Output ($status, $msg, $nl = TRUE, $email = FALSE)
	{
		
		$status	= strtoupper ($status);
		
		if (is_array ($msg) || is_object ($msg))
		{
			$msg	= Parser::pre ($msg, 0, TRUE, FALSE);
		}
		
		echo '[' . date ('Y-m-d H:i:s') . '][' . $status . '] ' . $msg . ($nl? "\n" : NULL);
		
		if ($email)
		{
			return static::emailAdmin ($status, $msg);
		}
		
		return TRUE;
		
	}
	
	
	/**
	 * Email admin
	 * @param string $status Message status e.g. error, success, debug
	 * @param string $msg Message to email
	 * @param boolean $email Email to admin, default false
	 * @return boolean
	 */
	
	public static function emailAdmin ($status, $msg)
	{
		
		// Check admin email constants are defined
		
		if (!defined ('EMAIL_FROM'))
		{
			status::Output ('ERROR', 'EMAIL_FROM not specified');
			return;
		}
		
		if (!defined ('AUTHOR_NAME'))
		{
			status::Output ('ERROR', 'AUTHOR_NAME not specified');
			return;
		}
		
		if (!defined ('AUTHOR_EMAIL'))
		{
			status::Output ('ERROR', 'AUTHOR_EMAIL not specified');
			return;
		}
		
		// Create email
		
		$email	= new Email;

		if (defined ('EMAIL_SMTP') && EMAIL_SMTP)
		{
			$email->setSMTPStream ();
		}
		else
		{
			$email->setPHPMail ();
		}
		
		// Set message
		
		$email->addHTML ("
			<p>CLI email from " . EMAIL_DOMAIN . ":<br />
			status: " . $status . "<br />
			message: " . nl2br ($msg) . "
			</p>
		");
		
		// Send to admin
		
		$email->addRecipient (AUTHOR_EMAIL, AUTHOR_NAME);

		if ($email->Send (EMAIL_FROM, 'CLI Message') && !\Sonic\Message::count ('error'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}
	
	
}

// End CLI Class