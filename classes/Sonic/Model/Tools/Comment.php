<?php

// Define namespace

namespace Sonic\Model\Tools;

// Start Comment Class

class Comment
{
	
	
	public $comment	= FALSE;
	private $lines	= array ();
	
	
	/**
	 * Instantiate class
	 * @param string $comment Comment to process
	 * @return void
	 */
	
	public function __construct ($comment)
	{
		$this->comment	= $comment;
		$this->lines	= explode ("\n", self::cleanComment ($comment));
	}
	
	
	/**
	 * Check if a tag is set in the comment
	 * @param string $tag Tag to check for e.g. ignore = @ignore
	 * @return boolean 
	 */
	
	public function hasTag ($tag)
	{
		
		foreach ($this->lines as $line)
		{
			if (strpos ($line, '@' . $tag) === 0)
			{
				return TRUE;
			}
		}
		
		return FALSE;
		
	}
	
	/**
	 * Return array of matching tags
	 * @param string $tag Tags to return
	 * @return array
	 */
	
	public function getTags ($tag)
	{
		
		$tags	= array ();
		
		foreach ($this->lines as $line)
		{
			if (strpos ($line, '@' . $tag) === 0)
			{
				$tags[]	= trim (substr ($line, strlen ('@' . $tag)));
			}
		}
		
		return $tags;
		
	}
	
	
	/**
	 * Return short description
	 * This is the first line of a comment e.g. the line above
	 * @return string 
	 */
	
	public function getShortDescription ()
	{
		return isset ($this->lines[0])? $this->lines[0] : NULL;
	}
	
	
	/**
	 * Return long description
	 * This is every other line of a comment besides the first line and any tags
	 * @return string 
	 */
	
	public function getLongDescription ()
	{
		
		$comment	= '';
		
		foreach ($this->lines as $key => $line)
		{
			
			if ($key == 0 || ($line && $line[0] == '@'))
			{
				continue; 
			}
			
			if ($comment)
			{
				$comment	.= "\n";
			}
			
			$comment .= $line;
			
		}
		
		return $comment;
		
	}
	
	
	/**
	 * Return a stared comment
	 * @param string $strComment Comment
	 * @param integer $intTabs Tabs before line
	 * @return string 
	 */
	
	public static function starComment ($strComment, $intTabs = 0)
	{
		
		// Set return
		
		$strReturn	= '';
		
		// Split comment into lines
		
		$arrComment		= explode ("\n", $strComment);
		
		// Find the position of the first /
		
		$intFirstSlash	= strpos ($arrComment[0], '/');
		
		// Set prefix
		
		$strPrefix		= ($intFirstSlash)? substr ($arrComment[0], 0, $intFirstSlash) : NULL;
		
		// Clean comment
		
		$strComment		= self::cleanComment ($strComment);
		
		// Split comment into lines
		
		$arrComment		= explode ("\n", $strComment);
		
		// Set the high count
		
		$intHigh	= 0;
		
		// For each line
		
		foreach ($arrComment as $strLine)
		{
			
			// If the length is above the high count
			
			if (strlen ($strLine) > $intHigh)
			{
				
				// Set length as high count
				
				$intHigh	= strlen ($strLine);
				
			}
			
		}
		
		// For each line
		
		foreach ($arrComment as &$strLine)
		{
			
			// Count chars
			
			$strCountChars	= count_chars ($strLine, 3);
			
			// If the comment consists of only -'s
			
			if ($strCountChars == '-')
			{
				
				// Set the line to be -'s
				
				$strLine	= str_repeat ('-', $intHigh);
				
			}
			
		}
		
		// Unset reference
		
		unset ($strLine);
		
		// Set tabs
		
		$strTabs	= str_repeat ("\t", $intTabs);
		
		// Add the first lines
		
		$strReturn	.= $strTabs . $strPrefix . '/****' . str_repeat ('*', $intHigh) . '*****' . "\n" . 
					   $strTabs . $strPrefix . '*    ' . str_repeat (' ', $intHigh) . '    *' . "\n";
		
		// For each line
		
		foreach ($arrComment as $strLine)
		{
			
			// Add to return
			
			$strReturn	.= $strTabs . $strPrefix . '*    ' . str_pad ($strLine, $intHigh) . '    *' . "\n";
			
		}
		
		// Add the last lines
		
		$strReturn	.= $strTabs . $strPrefix . '*    ' . str_repeat (' ', $intHigh) . '    *' . "\n" . 
					   $strTabs . $strPrefix . '*****' . str_repeat ('*', $intHigh) . '****/';
		
		// Return
		
		return $strReturn;
		
	}
	
	
	/**
	 * Return a lined comment
	 * @param type $strComment Comment
	 * @param type $intTabs Tabs before line
	 * @return string 
	 */
	
	public static function lineComment ($strComment, $intTabs = 0)
	{
		
		// Set return
		
		$strReturn	= '';
		
		// Split comment into lines
		
		$arrComment	= explode ("\n", $strComment);
		
		// Find the position of the first /
		
		$intFirstSlash	= strpos ($arrComment[0], '/');
		
		// Set prefix
		
		$strPrefix		= ($intFirstSlash)? substr ($arrComment[0], 0, $intFirstSlash) : NULL;
		
		// Clean comment
		
		$strComment	= self::cleanComment ($strComment);
		
		// Split comment into lines
		
		$arrComment	= explode ("\n", $strComment);
		
		// Set tabs
		
		$strTabs	= str_repeat ("\t", $intTabs);
		
		// For each line
		
		foreach ($arrComment as $strLine)
		{
			
			// Add line
			
			$strReturn	.= $strTabs . $strPrefix . '// ' . $strLine . "\n";
			
		}
		
		// Remove last \n
		
		if (substr ($strReturn, -1) == "\n")
		{
			
			$strReturn	= substr ($strReturn, 0, -1);
			
		}
		
		// Return
		
		return $strReturn;
		
	}
	
	
	/**
	 * Return a phpdoc formatted comment
	 * @param string $strComment Comment
	 * @param integer $intTabs Tabs before line
	 * @return string
	 */
	
	public static function phpdocComment ($strComment, $intTabs = 0)
	{
		
		// Set return
		
		$strReturn	= '';
		
		// Split comment into lines
		
		$arrComment		= explode ("\n", $strComment);
		
		// Find the position of the first /
		
		$intFirstSlash	= strpos ($arrComment[0], '/');
		
		// Set prefix
		
		$strPrefix		= ($intFirstSlash)? substr ($arrComment[0], 0, $intFirstSlash) : NULL;
		
		// Clean comment
		
		$strComment		= self::cleanComment ($strComment);
		
		// Split comment into lines
		
		$arrComment		= explode ("\n", $strComment);
		
		// Set tabs
		
		$strTabs	= str_repeat ("\t", $intTabs);
		
		// Add the first lines
		
		$strReturn	.= $strTabs . $strPrefix . '/**' . "\n";
		
		// For each line
		
		foreach ($arrComment as $strLine)
		{
			
			// Add to return
			
			$strReturn	.= $strTabs . $strPrefix . ' * ' . $strLine . "\n";
			
		}
		
		// Add the last lines
		
		$strReturn	.= $strTabs . $strPrefix . ' */';
		
		// Return
		
		return $strReturn;
		
	}
	
	
	/**
	 * Return a comment with all comment chars stripped
	 * @param string $strComment Comment
	 * @return string
	 */
	
	public static function cleanComment ($strComment)
	{
		
		// Strip /r
		
		$strComment	= str_replace ("\r", '', $strComment);
		
		// Set return
		
		$strReturn	= $strComment;
			
		// Split lines
		
		$arrLines	= explode ("\n", $strComment);
		
		// Find the position of the first /
		
		$intFirstSlash	= strpos ($arrLines[0], '/');
		
		// If there is a first slash
		
		if ($intFirstSlash !== FALSE)
		{
			
			// Set prefix
			
			$strPrefix		= substr ($arrLines[0], 0, $intFirstSlash);
			
			// Switch the next char
			
			switch ($strComment[$intFirstSlash +1])
			{
				
				
				// Star comment
				
				case '*':
					
					// Reset return
					
					$strReturn	= '';
					
					// Remove first and last
					
					array_pop ($arrLines);
					array_shift ($arrLines);
					
					// For each line
					
					foreach ($arrLines as &$strLine)
					{
						
						// Remove prefix and clean whitespace
						
						$strLine	= trim (substr ($strLine, $intFirstSlash));
						
						// If the first char is a *
						
						if ($strLine[0] == '*')
						{
							
							// Remove *
							
							$strLine	= substr ($strLine, 1);
							
							// For the first 4 characters
							
							for ($x = 0; $x < 4; $x++)
							{
								
								// If the character is a space
								
								if ($strLine[0] == ' ')
								{
									
									// Remove it
									
									$strLine	= substr ($strLine, 1);
									
								}
								
								// Otherwise
								
								else
								{
									
									// Break
									
									break;
									
								}
								
							}
							
						}
						
						// If the last char is a *
						
						if (substr ($strLine, -1) == '*')
						{
							
							// Remove *
							
							$strLine	= substr ($strLine, 0, -1);
							
							// For the last 4 characters
							
							for ($x = 0; $x < 4; $x++)
							{
								
								// If the character is a space
								
								if ($strLine[strlen ($strLine) -1] == ' ')
								{
									
									// Remove it
									
									$strLine	= substr ($strLine, 0, -1);
									
								}
								
								// Otherwise
								
								else
								{
									
									// Break
									
									break;
									
								}
								
							}
							
						}
						
					}
					
					// Unset reference
					
					unset ($strLine);
					
					// For each line
					
					foreach ($arrLines as $intKey => $strLine)
					{
						
						// If the line contains only spaces
						
						if (count_chars ($strLine, 3) == ' ')
						{
							
							// Remove
							
							unset ($arrLines[$intKey]);
							
						}
						
						// Else
						
						else
						{
							
							// Break
							
							break;
							
						}
						
					}
					
					// For each line in reverse order
					
					foreach (array_reverse ($arrLines, TRUE) as $intKey => $strLine)
					{
						
						// If the line contains only spaces
						
						if (count_chars ($strLine, 3) == ' ')
						{
							
							// Remove
							
							unset ($arrLines[$intKey]);
							
						}
						
						// Else
						
						else
						{
							
							// Break
							
							break;
							
						}
						
					}
					
					// For each line
					
					foreach ($arrLines as $strLine)
					{
						
						// If the comment is only -'s
						
						if (count_chars ($strLine, 3) == '-')
						{
							
							// Add single - to return
							
							$strReturn	.= '-' . "\n";
							
						}
						
						// Else
						
						else
						{
							
							// Add to return
							
							$strReturn	.= rtrim ($strLine) . "\n";
							
						}
						
					}
					
					// If the last char is \n
					
					if (substr ($strReturn, -1) == "\n")
					{
						
						// Remove it
						
						$strReturn	= substr ($strReturn, 0, -1);
						
					}
					
					// Break
					
					break;
					
					
				// Line comment
				
				case '/':
					
					// Reset return
					
					$strReturn	= '';
					
					// For each line
					
					foreach ($arrLines as $strLine)
					{
						
						// If the line is empty
						
						if ($strLine == '')
						{
							
							// Next
							
							continue;
							
						}
						
						// Remove prefix
						
						$strLine	= substr ($strLine, $intFirstSlash);
						
						// Remove //
						
						$strLine	= str_replace ('// ', '', $strLine);
						$strLine	= str_replace ('//', '', $strLine);
						
						// Add to return
						
						$strReturn	.= $strLine . "\n";
						
					}
					
					// If the last char is \n
					
					if (substr ($strReturn, -1) == "\n")
					{
						
						// Remove it
						
						$strReturn	= substr ($strReturn, 0, -1);
						
					}
					
					// Break
					
					break;
				
				
			}
			
		}
		
		// Return
		
		return $strReturn;
		
	}
	
	
}

// End Comment Class