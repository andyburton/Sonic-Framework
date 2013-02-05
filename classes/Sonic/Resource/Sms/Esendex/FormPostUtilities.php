<?php

/*
Name:			FormPostUtilities.php
Description:	Esendex PHP HTTP Form Post Utilities
Documentation: 	https://www.esendex.com/isSecure/messenger/formpost/SendServiceNoHeader.asmx

Copyright (c) 2004/2005 Esendex®

If you have any questions or comments, please contact:

support@esendex.com
http://www.esendex.com/support
*/
namespace Sonic\Resource\Sms\Esendex;
class FormPostUtilities
{
	var $isSecure;
	var $certificate;
	
	function FormPostUtilities( $isSecure = false, $certificate = "" )
	{
		$this->isSecure = $isSecure;
		$this->certificate = $certificate;
	}

	function FormPost( $dataStream, $url )
	{
            $postFields = "";
            $port = 80;

            foreach ( $dataStream as $key => $value )
            {
                    if( !empty( $key ) && !empty( $value ) )
                    {
                            if ( !empty( $postFields ) ) 
                            {
                                    $postFields.= "&";
                            }

                            $postFields.= $key."=".urlencode( $value );
                    }
            }

            try
            {
                // Redmine issue #1428
                if (function_exists('curl_init'))
                {                
                    $curlHandle = curl_init();    				// Initialise the curl handle.
                    curl_setopt( $curlHandle, CURLOPT_URL, $url ); 		// Set the post URL.
                    curl_setopt( $curlHandle, CURLOPT_FAILONERROR, 1 );
                    curl_setopt( $curlHandle, CURLOPT_FOLLOWLOCATION, 1 );	// Allow redirects.
                    curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 ); 	// Return into a variable.
                    curl_setopt( $curlHandle, CURLOPT_TIMEOUT, 30 );	// Times out after 30 seconds.

                    if ( $this->isSecure )
                    {
                        curl_setopt( $curlHandle, CURLOPT_CAINFO, $this->certificate );
                        curl_setopt( $curlHandle, CURLOPT_SSL_VERIFYPEER, 1 );
                        curl_setopt( $curlHandle, CURLOPT_SSL_VERIFYHOST, 1 );

                        $port = 443;
                    }
                    curl_setopt( $curlHandle, CURLOPT_HEADER, 0 );
                    curl_setopt( $curlHandle, CURLOPT_PORT, $port );		// Set the port number.
                    curl_setopt( $curlHandle, CURLOPT_POST, 1 ); 			// Set the POST method.
                    curl_setopt( $curlHandle, CURLOPT_POSTFIELDS, $postFields );	// Add the POST fields.

                    $result = curl_exec( $curlHandle ); 				// run the whole process

                    curl_setopt( $curlHandle, CURLOPT_RETURNTRANSFER, 1 );
                    curl_close( $curlHandle );

                    return $this->ParseResult( $result );
                }
                else
                {
                    return FALSE;
                }
            }
            catch (Exception $e)
            {
                return FALSE;
            }
	}
	
	function ParseResult( $result )
	{
		$results = explode( "\r\n", $result );
		
		$index = count( $results );

		$i = 0;
		$j = 0;

		while( $i < $index )
		{
			$ampersandPosition = strpos( $results[$i], "&" );

			if( $ampersandPosition != false )
			{
				$values[$j] = explode( "&", $results[$i] );
				$results[$i] = $this->GetKeyValuePairs( $values[$j] );
				$j++;
			}
			
			$i++;
		}

		//Get the message and key/value pair elements from the results.
		$messages = $this->GetMessagesArrays( $results );
		$keyValuePairs = $this->GetKeyValuePairs( $results );

		if( !is_array( $messages ) )
		{
			return $keyValuePairs;
		}

		$keyValuePairs['Messages'] = $messages;
		
		return $keyValuePairs;
	}

	function GetKeyValuePairs( $results )
	{
		$i = 0;
		$j = 0;
		$response = "";
		$index = count( $results );

		while( $i < $index )
		{
			if( !is_array( $results[$i] ) )
			{
				$equalsPosition = strpos( $results[$i], "=" );

				if( $equalsPosition != false )
				{
					$resultKey = substr( $results[$i], 0, strpos( $results[$i], "=" ) );
					$resultValue = urldecode( substr( $results[$i], $equalsPosition + 1, strlen( $results[$i] ) - $equalsPosition - 1 ) );

					$response[$resultKey] = $resultValue;
				}
			}
			
			$i++;
		}
		
		return $response;
	}

	function GetMessagesArrays( $results )
	{
		$i = 0;
		$j = 0;
		
		$index = count( $results );
		
		$messages;

		while( $i < $index )
		{
			if( is_array( $results[ $i ] ) )
			{
				$messages[$j] = $results[$i];
				
				$j++;
			}
			
			$i++;
		}

		$result = "";
		
		if ( $j > 0 )
		{
			$result = $messages;
		}
		
		return $result;
	}
}
