<?php

/**
 * A resource class to help with socket streaming
 * http://uk3.php.net/manual/en/ref.stream.php
 */


// Define namespace

namespace Sonic\Resource;

// Start Stream Class

class Stream
{
	
	
	/**
	 * Stream Resource
	 * @var resource
	 */
	
	private $_stream		= FALSE;
	
	/**
	 * Stream Log
	 * @var array
	 */
	
	protected $log			= array ();
	
	/**
	 * Errors
	 * @var array
	 */
	
	protected $errors		= array ();
	
	/**
	 * Stream URL
	 * @var string
	 */
	
	protected $url			= NULL;
	
	/**
	 * Stream Port
	 * @var integer
	 */
	
	protected $port			= 0;
	
	/**
	 * Stream Timeout
	 * @var integer
	 */
	
	protected $timeout		= 30;
	
	/**
	 * Stream Error Message
	 * @var string
	 */
	
	protected $errorMessage	= NULL;
	
	/**
	 * Stream Error Code
	 * @var integer
	 */
	
	protected $errorCode	= 0;
	
	/**
	 * Development Mode
	 * @var boolean
	 */
	
	protected $dev			= FALSE;
	
	
	
	/**
	 * Instantiate the class
	 * @param mixed $url Stream URL
	 * @param integer $port Socket Port
	 * @param integer $timeout Stream Timeout
	 * @param boolean $dev Development Mode
	 * @return  @return \Sonic\Resource\Stream
	 */
	
	public function __construct ($url, $port = FALSE, $timeout = FALSE, $dev = FALSE)
	{
		
		// Internal Variables
		
		$this->url		= $url;
		$this->port		= $port? $port : $this->port;
		$this->timeout	= $timeout? $timeout : $this->timeout;
		$this->dev		= $dev;
		
		// Connect
		
		$this->Connect ();
		
	}
	
	
	/**
	 * Connect to a stream
	 * @return void
	 */
	
	public function Connect ()
	{
		
		// Development Mode
		
		if ($this->dev)
		{
			
			// Add to log
			
			$this->log[]	= 'Connection Attempt';
			
		}
		
		// Check to see if the stream is already connected
		
		if ($this->_stream === FALSE)
		{
			
			// Attempt to connect to the stream
			
			$this->_stream	= stream_socket_client ($this->url . ':' . $this->port, $this->errorCode, $this->errorMessage, $this->timeout);
			
			// If the connection failed
			
			if ($this->_stream === FALSE)
			{
				
				// Development Mode
				
				if ($this->dev)
				{
					
					// Add to log
					
					$this->log[]	= 'Cannot to connect to ' . $this->url . ':' . $this->port;
					
				}
				
				// Create an error
				
				$this->Error ('The stream could not connect: #' . $this->errorCode . ' - ' . $this->errorMessage);
				return;
				
			}
			
			// Development Mode
			
			else if ($this->dev)
			{
				
				// Add to log
				
				$this->log[]	= 'Connected to ' . $this->url . ':' . $this->port;
				
			}
			
			// Set blocking
			
			stream_set_blocking ($this->_stream, 1);
			
		}
		
		// If the stream is connected
		
		else
		{
			
			// Development Mode
			
			if ($this->dev)
			{
				
				// Add to log
				
				$this->log[]	= 'Cannot to connect to ' . $this->url . ':' . $this->port;
				
			}
			
			// Create an error
			
			$this->Error ('The stream is already connected!');
			
		}
		
	}
	
	
	/**
	 * Send data through a stream
	 * @param string|array $data Data to send
	 * @param string|array $reply Check for reply
	 * @return boolean
	 */
	
	public function Send ($data, $reply = FALSE)
	{
		
		// Check the stream is connected
		
		if ($this->_stream === FALSE)
		{
			
			// If not create an error
			
			$this->Error ('Cannot send data, the stream is not connected!');
			
			// return FALSE
			
			return FALSE;
			
		}
		
		// If the data is an array
		
		if (is_array ($data))
		{
			
			// Development Mode
			
			if ($this->dev)
			{
				
				// Add to log
				
				$this->log[]	= 'Send data array:';
				
			}
			
			// Foreach data value
			
			foreach ($data as $val)
			{
				
				// Development Mode
				
				if ($this->dev)
				{
					
					// Add to log
					
					$this->log[]	= $val;
					
				}
				
				// Send the data through the stream
				
				$send	= stream_socket_sendto ($this->_stream, $val . "\r\n");
				
				// If there was an error sending the data
				
				if ($send === FALSE)
				{
					
					// Set an error
					
					$this->Error ('Error sending data: #' . $this->errorCode . ' - ' . $this->errorMessage);
					
					// Close the stream
					
					$this->Close ();
					
					// return FALSE
					
					return FALSE;
					
				}
				
			}
			
		}
		
		// If the data is not an array
		
		else
		{
			
			// Development Mode
			
			if ($this->dev)
			{
				
				// Add to log
				
				$this->log[]	= 'Send: ' . $data;
				
			}
			
			// Send the data through the stream
			
			$send	= stream_socket_sendto ($this->_stream, $data . "\r\n");
			
			// If there was an error sending the data
			
		
			if ($send === FALSE)
			{
				
				// Set an error
				
				$this->Error ('Error sending data: #' . $this->errorCode . ' - ' . $this->errorMessage);
				
				// Close the stream
				
				$this->Close ();
				
				// return FALSE
				
				return FALSE;
				
			}
			
		}
		
		// If a reply is expected
		
		if ($reply !== FALSE)
		{
			
			return $this->Receive ($reply);
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	
	/**
	 * Receive data through a stream
	 * @param string|array $reply Expected Reply
	 * @return boolean
	 */
	
	public function Receive ($reply = FALSE)
	{
		
		// Check the stream is connected
		
		if ($this->_stream === FALSE)
		{
			
			// If not create an error
			
			$this->Error ('Cannot receive data, the stream is not connected!');
			
			// return FALSE
			
			return FALSE;
			
		}
		
		// Set some variables to collect the received data
		
		$recv	= NULL;
		
		// Receive the data from the socket
		
		while (TRUE)
		{
			
			// Append the buffer to the received data
			
			$recv	.= stream_socket_recvfrom ($this->_stream, 4096);
			
			// If the string has been terminated
			
			if (strstr ($recv, "\r\n"))
			{
				
				// Break the loop
				
				break;
				
			}
			
		}
		
		// Development Mode
		
		if ($this->dev)
		{
			
			// Add to log
			
			$this->log[]	= 'Received data: ' . trim ($recv);
			
		}
		
		// If a reply was expected
		
		if ($reply !== FALSE)
		{
			
			// Get the reply code (first 3 characters of the reply)
			
			$replyCode	= substr ($recv, 0, 3);
			
			// If the expected reply is an array
			
			if (is_array ($reply))
			{
				
				// Check to see if the code is in the expected replies array
				
				if (!in_array ($replyCode, $reply))
				{
					
					// The code is not in the array so create an error
					
					$this->Error ('Invalid response: ' . $replyCode);
					
					// return FALSE
					
					return FALSE;
					
				}
				
			}
			
			// If the data is not an array
			
			else
			{
				
				// Check to see if the code is the same as the expected response
				
				if ($replyCode != $reply)
				{
					
					// The code is not the expected reponse so set an error
					
					$this->Error ('Invalid response: ' . $replyCode);
					
					// return FALSE
					
					return FALSE;
					
				}
				
			}
			
			// Development Mode
			
			if ($this->dev)
			{
				
				// Add to log
				
				$this->log[]	= 'Expected Response: ' . $replyCode;
				
			}
			
		}
		
		// return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Set an error
	 * @param string $error Error Messge
	 * @return void
	 */
	
	private function Error ($error)
	{
		
		// Development Mode
		
		if ($this->dev)
		{
			
			// Add to log
			
			$this->log[]	= 'Error: ' . $error;
			
		}
		
		// Add an error to the errrors array
		
		$this->errors[]	= $error;
		
	}
	
	
	/**
	 * Determine if there is an error
	 * @return boolean
	 */
	
	public function isError ()
	{
		
		// If there is not an error
		
		if (count ($this->errors) == 0)
		{
			
			// return FALSE
			
			return FALSE;
			
		}
		
		// return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Return the errors
	 * @param boolean $str Return as string
	 * @return mixed
	 */
	
	public function getErrors ($str = FALSE)
	{
		
		// Copy the errors array
		
		$errors	= $this->errors;
		
		// Reset errors
		
		$this->resetErrors ();
		
		// If return as string
		
		if ($str)
		{
			
			// Convert to string and return
			
			return implode ('<br />', $errors);
			
		}
		
		// Else
		
		else
		{
			
			// Return the errors array
			
			return $errors;
			
		}
		
	}
	
	
	/**
	 * Reset the errors array
	 * @return void
	 */
	
	public function resetErrors ()
	{
		
		// Reset the errors
		
		$this->errors	= array ();
		
	}
	
	
	/**
	 * Return the stream log
	 * @return array
	 */
	
	public function getLog ()
	{
		
		// Return log
		
		return $this->log;
		
	}
	
	
	/**
	 * Close the stream
	 * @return boolean
	 */
	
	public function Close ()
	{
		
		// Development Mode
		
		if ($this->dev)
		{
			
			// Add to log
			
			$this->log[]	= 'Close';
			
		}
		
		// Close the stream
		
		//stream_socket_shutdown ($this->_stream, STREAM_SHUT_RDWR);
		fclose ($this->_stream);
		
		// Set the stream to FALSE
		
		$this->_stream	= FALSE;
		
	}
	
	
	/**
	 * Destruct the object
	 * @return boolean
	 */
	
	public function __destruct ()
	{
		
		// If the stream is still open
		
		if ($this->_stream !== FALSE)
		{
			
			// Close the stream
			
			$this->Close ();
			
		}
		
	}
	
	
}

// End Stream Class