<?php

// Define namespace

namespace Sonic\Resource;

// Start Crypt Class

class Crypt
{
	
	
	/**
	 * Characters for a random password
	 */
	
	const CHARSET_RANDOM	= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	
	/**
	 * Return a sha-256 hash for a string
	 * @param string $str String to hash
	 * @return string
	 */
	
	public static function _sha256 ($str)
	{
		return hash ('sha256', $str);
	}
	
	
	/**
	 * Generate a random password
	 * @param integer $length Password length
	 * @return string
	 */
	
	public static function _randomPassword ($length = 10)
	{
		
		// Set random characters variables
		
		$charset		= self::CHARSET_RANDOM;
		
		// Set variables
		
		$charsetLength	= strlen ($charset);
		$password		= NULL;
		
		// Generate Password
		
		for ($i = 0; $i < $length; $i++)
		{
			$password	.= $charset[rand (0, $charsetLength-1)];
		}
		
		// Return password
		
		return $password;
		
	}
	
	
	/**
	 * Generate a rijndael 256 initialisation vector
	 * @param resource $cypher Encryption Resource
	 * @return string
	 */
	
	public static function _genRijndael256IV ($cypher = FALSE)
	{
		
		// If a module has not been passed
		
		if (!$cypher)
		{
			
			// Open the cypher
			
			$cypher		= mcrypt_module_open ('rijndael-256', '', 'ofb', '');
			
		}
		
		// Create the IV
		
		$iv	= mcrypt_create_iv (mcrypt_enc_get_iv_size ($cypher), MCRYPT_RAND);
		
		// Close Module
		
		mcrypt_module_close ($cypher);
		
		// Return IV
		
		return $iv;
		
	}
	
	
	/**
	 * Encrypt and return a string using the Rijndael 256 cypher
	 * @param string $str String to encrypt
	 * @param string $key Key to encrypt the string
	 * @param string $iv Initialisation vector
	 * @return string
	 */
	
	public static function _encryptRijndael256 ($str, $key, $iv = FALSE)
	{
		
		// If the string is empty
		
		if (empty ($str))
		{
			return '';
		}
		
		// Open the cypher
		
		$cypher		= mcrypt_module_open ('rijndael-256', '', 'ofb', '');
		
		// Create the IV if there is none
		
		if ($iv === FALSE)
		{
			$iv		= self::_genRijndael256IV ($cypher);
		}
		
		// Set Key
		
		$key		= substr ($key, 0, mcrypt_enc_get_key_size ($cypher));
		
		// Initialise encryption
		
		mcrypt_generic_init ($cypher, $key, $iv);
		
		// Encrypt String
		
		$encrypted	= mcrypt_generic ($cypher, $strString);
		
		// Terminate encryption hander
		
		mcrypt_generic_deinit ($cypher);
		
		// Close Module
		
		mcrypt_module_close ($cypher);
		
		// Return encrypted string
		
		return $encrypted;
		
	}
	
	
	/**
	 * Decrypt and return a string using the Rijndael 256 cypher
	 * @param string $str String to decrypt
	 * @param string $key Key to decrypt the string
	 * @param string $ic Initialisation vector
	 * @return string
	 */
	
	public static function _decryptRijndael256 ($str, $key, $iv)
	{
		
		// If the string is empty
		
		if (empty ($str))
		{
			return '';
		}
		
		// Open the cypher
		
		$cypher		= mcrypt_module_open ('rijndael-256', '', 'ofb', '');
		
		// Set Key
		
		$key		= substr ($key, 0, mcrypt_enc_get_key_size ($cypher));
		
		// Initialise decryption
		
		mcrypt_generic_init ($cypher, $key, $iv);
		
		// Decrypt String
		
		$decrypted	= mdecrypt_generic ($cypher, $str);
		
		// Terminate encryption hander
		
		mcrypt_generic_deinit ($cypher);
		
		// Close Module
		
		mcrypt_module_close ($cypher);
		
		// Return decrypted string
		
		return $decrypted;
		
	}
	
	
	/**
	 * Set the keyring path for GnuPG
	 * @param string $path Path to keyring
	 */
	
	public static function _setGnuPGKeyring ($path)
	{
		
		// Set environmental variable
		
		putenv ('GNUPGHOME=' . $path);
		
	}
	
	
	/**
	 * GnuPG encrypt a message using the recipient public key and optionally sign
	 * http://devzone.zend.com/article/3753-Using-GnuPG-with-PHP
	 * NOTE: GnuPG must be installed and configured with PHP.
	 *       The recipient must be in your public key ring
	 * @param string $recipient Recipient Indentity (e.g. email address)
	 * @param string $message Message to encrypt
	 * @param string $sender Sender Identity
	 * @param string $senderKey Key Sender Secret Key (Only required if signing)
	 * @param boolean $binary Output in binary (non-ASCII armored)
	 * @return string
	 */
	
	public static function _encryptGnuPG ($recipient, $message, $sender = FALSE, $senderKey = '', $binary = FALSE)
	{
		
		// Create new GnuPG object
		
		$gpg	= new \gnupg ();
		
		// Set error mode
		
		$gpg->seterrormode (\gnupg::ERROR_EXCEPTION);
		
		// If binary
		
		if ($binary)
		{
			
			// Turn off armored mode
			
			$gpg->setarmor (0);
			
		}
		
		// Add the recipient encryption key
		
		$gpg->addencryptkey ($recipient);
		
		// If there is a sender
		
		if ($sender !== FALSE)
		{
			
			// Add signature
			
			$gpg->addsignkey ($sender, $senderKey);
			
			// Return encrypted and signed data
			
			return $gpg->encryptsign ($message);
			
		}
		
		// Return encrypted data
		
		return $gpg->encrypt ($message);
		
	}
	
	
	/**
	 * GnuPG decrypt a message using the recipient private key
	 * http://devzone.zend.com/article/3753-Using-GnuPG-with-PHP
	 * NOTE: GnuPG must be installed and configured with PHP.
	 *       The recipient must be in your private key ring
	 * @param string $recipient Recipient Indentity (e.g. email address)
	 * @param string $recipientKey Recipient Secret Key
	 * @param string $message Message to decrypt
	 * @return string
	 */
	
	public static function _decryptGnuPG ($recipient, $recipientKey, $message)
	{
		
		// Create new GnuPG object
		
		$gpg	= new \gnupg ();
		
		// Set error mode
		
		$gpg->seterrormode (\gnupg::ERROR_EXCEPTION);
		
		// Add the recipient decryption key
		
		$gpg->adddecryptkey ($recipient, $recipientKey);
		
		// Return decrypted data
		
		return $gpg->decrypt ($message);
		
	}
	
	
	/**
	 * GnuPG decrypt and verify a message using the recipient private key
	 * Returns an array in the format: array (0 => $message, 1 => $signatures)
	 * http://devzone.zend.com/article/3753-Using-GnuPG-with-PHP
	 * NOTE: GnuPG must be installed and configured with PHP.
	 *       The recipient must be in your private key ring
	 * @param string $recipient Recipient Indentity (e.g. email address)
	 * @param string $recipientKey Recipient Secret Key
	 * @param string $message Message to decrypt
	 * @return array
	 */
	
	public static function _verifyGnuPG ($recipient, $recipientKey, $message)
	{
		
		// Create new GnuPG object
		
		$gpg		= new \gnupg ();
		
		// Set error mode
		
		$gpg->seterrormode (\gnupg::ERROR_EXCEPTION);
		
		// Add the recipient decryption key
		
		$gpg->adddecryptkey ($recipient, $recipientKey);
		
		// Set decrpyted string
		
		$decrypted	= '';
		
		// Set decrypted and verification data
		
		$return[1]	= $gpg->decryptverify ($message, $decrypted);
		
		// For each signature
		
		foreach ($return[1] as $key => &$signature)
		{
			
			// Get further user data
			
			$signature['user']	= $gpg->keyinfo ($signature['fingerprint']);
			
		}
		
		// Add decrypted data to return array
		
		$return[0]	= $decrypted;
		
		// Return decryption data
		
		return $return;
		
	}
	
	
}

// End Crypt Class