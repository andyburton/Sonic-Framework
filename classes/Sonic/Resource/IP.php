<?php

// Define namespace

namespace Sonic\Resource;

// Start IP Class

class IP
{
	
	
	/*
	 * Allow IPs
	 * @var array
	 */
	
	protected $allowIPs		= array ();
	
	
	/*
	 * Deny IPs
	 * @var array
	 */
	
	protected $denyIPs		= array ();
	
	
	/**
	 * Add an IP address range to allow
	 * Return FALSE is the IP range is invalid
	 * @see formatRange ()
	 * @param string $range IP range to allow
	 * @return boolean
	 */
	
	public function Allow ($range)
	{
		
		$formatted	= $this->formatRange ($range);
		
		if (!$formatted)
		{
			return FALSE;
		}
		
		$this->allowIPs[$formatted[0]]	= $formatted[1];
		return TRUE;
		
	}
	
	
	/**
	 * Add an IP address range to deny
	 * Return FALSE is the IP range is invalid
	 * @see formatRange ()
	 * @param string $range IP range to deny
	 * @return boolean
	 */
	
	public function Deny ($range)
	{
		
		$formatted	= $this->formatRange ($range);
		
		if (!$formatted)
		{
			return FALSE;
		}
		
		$this->denyIPs[$formatted[0]]	= $formatted[1];
		return TRUE;
		
	}
	
	
	/**
	 * Remove an allowed IP address range
	 * Return FALSE is the IP range is invalid
	 * @param string $range IP range to remove
	 * @return boolean
	 */
	
	public function removeAllowed ($range)
	{
		
		$formatted	= $this->formatRange ($range);
		
		if (!$formatted)
		{
			return FALSE;
		}
		
		unset ($this->allowIPs[$formatted[0]]);
		return TRUE;
		
	}
	
	
	/**
	 * Remove a denied IP address range
	 * Return FALSE is the IP range is invalid
	 * @param string $range IP range to remove
	 * @return boolean
	 */
	
	public function removeDenied ($range)
	{
		
		$formatted	= $this->formatRange ($range);
		
		if (!$formatted)
		{
			return FALSE;
		}
		
		unset ($this->denyIPs[$formatted[0]]);
		return TRUE;
		
	}
	
	
	
	/**
	 * Return allowed IP ranges
	 * @return array
	 */
	
	public function getAllowed ()
	{
		return array_keys ($this->allowIPs);
	}
	
	
	/**
	 * Return denied IP ranges
	 * @return array
	 */
	
	public function getDenied ()
	{
		return array_keys ($this->denyIPs);
	}
	
	
	/**
	 * Reset allowed IPs
	 * @return void
	 */
	
	public function resetAllowed ()
	{
		$this->allowIPs	= array ();
	}
	
	
	/**
	 * Reset denied IPs
	 * @return void
	 */
	
	public function resetDenied ()
	{
		$this->denyIPs	= array ();
	}
	
	
	/**
	 * Format an IP address range 
	 * 
	 * Valid IP ranges can be:
	 * 
	 * Single:	  192.168.1.1
	 * CIDR:      192.168.1/24, 192.168.1.0/24 or 192.168.1.0/255.255.255.0
	 * Wildcard:  192.168.1.*
	 * Start-End: 192.168.1.0-192.168.1.255
	 * 
	 * @param string $range Valid IP range
	 * @return FALSE|array (name, range)
	 */
	
	public function formatRange ($range)
	{
		
		// CIDR
		
		if (strpos ($range, '/') !== FALSE)
		{
			
			// Get the IP and netmask
			
			list ($ip, $netmask) = explode ('/', $range, 2);
			
			// Split decimal and pad to ensure we have 4 octets

			$ip	= implode ('.', array_pad (explode ('.', $ip), 4, 0));
			
			// If the netmask is a dotted decimal format
			
			if (strpos ($netmask, '.') !== FALSE)
			{
				
				// Convert wildcards
				
				$netmask		= str_replace ('*', '0', $netmask);
				
				// Convert to long
				
				$ipLong			= ip2long ($ip);
				$netmaskLong	= ip2long ($netmask);
				
				// Check IPs are valid
				
				if ($ipLong === FALSE || $netmaskLong === FALSE)
				{
					return FALSE;
				}
				
				// Return range
				
				return array (
					$ip . '/' . $netmask,
					array (
						(float)sprintf ("%u", $ipLong),
						(float)sprintf ("%u", $netmaskLong)
					)
				);
				
			}
			
			// Else netmask is a CIDR block
			
			else
			{
				
				// Convert netmask block to bit-mask
				// Bitwise NOT on wildcard
				
				
				$wildcard	= pow (2, (32 - (int)$netmask)) - 1;
				$netmaskDec	= ~$wildcard;
				
				// Return range
				
				return array (
					$ip . '/' . $netmask,
					array (
						(float)sprintf ("%u", ip2long ($ip)),
						$netmaskDec
					)
				);
				
			}
			
			// Return TRUE
			
			return TRUE;
			
		}
		
		// Wildcard or Start-End
		
		elseif (strpos ($range, '*') !== FALSE || strpos ($range, '-') !== FALSE)
		{
			
			// Wildcard
			
			if (strpos ($range, '*') !== FALSE)
			{
				$lower	= str_replace ('*', '0', $range);
				$upper	= str_replace ('*', '255', $range);
				$name	= $lower . '-' . $upper;
			}
			
			// Start-End
			
			else
			{
				list ($lower, $upper)	= explode ('-', $range, 2);
				$name	= $range;
			}
			
			// Convert to long

			$lowerLong	= ip2long ($lower);
			$upperLong	= ip2long ($upper);

			// Check IPs are valid

			if ($lowerLong === FALSE || $upperLong === FALSE)
			{
				return FALSE;
			}
			
			// Return range

			return array (
				$name,
				array (
					(float)sprintf ("%u", $lowerLong),
					(float)sprintf ("%u", $upperLong)
				)
			);
			
		}
		
		// Else a single IP

		if (strpos ($range, '.') !== FALSE)
		{
			
			// Return range

			return array (
				$range,
				(float)sprintf ("%u", ip2long ($range))
			);
			
		}
		
		// No range to return
		
		return FALSE;
		
	}
	
	
	/**
	 * Return whether an IP is inside a range
	 * @param intger $ip IP decimal to check
	 * @param string $name IP Range name
	 * @param string|array $range Valid IP range
	 * @return boolean
	 */
	
	protected function isInside ($ip, $name, $range)
	{
		
		// Single IP

		if (!is_array ($range))
		{
			if ($ip == $range)
			{
				return TRUE;
			}
		}

		// CIDR

		elseif (strpos ($name, '/') !== FALSE)
		{
			if (($ip & $range[1]) == ($range[0] & $range[1]))
			{
				return TRUE;
			}
		}

		// Start-End

		elseif (strpos ($name, '-') !== FALSE)
		{
			if (($ip >= $range[0]) && ($ip <= $range[1]))
			{
				return TRUE;
			}
		}
		
		// Not inside any range
		
		return FALSE;
		
	}
	
	
	/**
	 * Return whether an IP is allowed
	 * @param string $ip IPv4 address
	 * @param string $priority Order priority, default deny,allow
	 * @return boolean
	 */
	
	public function isAllowed ($ip, $priority = 'deny,allow')
	{
		
		// Convert IP to decimal
		
		$ipDec = (float)sprintf ("%u", ip2long ($ip));
		
		// Check
		
		foreach (explode (',', $priority) as $type)
		{
			
			switch ($type)
			{
				
				// Check if the IP is allowed
				
				case 'allow':
					
					foreach ($this->allowIPs as $name => $range)
					{
						if ($this->isInside ($ipDec, $name, $range))
						{
							return TRUE;
						}
					}
					
					break;
				
				// Check if IP is denied
					
				case 'deny':
					
					foreach ($this->denyIPs as $name => $range)
					{
						if ($this->isInside ($ipDec, $name, $range))
						{
							return FALSE;
						}
					}
					
					break;
				
			}
			
		}
		
		// Not allowed by default
		
		return FALSE;
		
	}
	
	
}