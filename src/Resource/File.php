<?php

// Define namespace

namespace Sonic\Resource;

// Start File Class

class File
{
	
	
	/**
	 * The uploaded file data
	 * @var array 
	 */
	
	protected $upload		= array ();
	
	/**
	 * The absolute file directory path
	 * @var string 
	 */
	
	protected $path			= FALSE;
	
	/**
	 * The file name including extension
	 * @var string 
	 */
	
	protected $filename		= FALSE;
	
	/**
	 * The file name
	 * @var string
	 */
	
	protected $name			= FALSE;
	
	/**
	 * The file extension
	 * @var string
	 */
	
	protected $extension	= FALSE;
	
	
	
	/**
	 * Constructor method
	 * @param array $file Upload file array
	 * @return void
	 */
	
	public function __construct ($file = array ())
	{
		
		// Set upload file if there is one
		
		if ($file)
		{
			$this->Upload ($file);
		}
		
	}
	
	
	/**
	 * Set the file upload and check for any errors
	 * @param array $file Uploaded file data
	 * @return boolean
	 */
	
	public function Upload ($file)
	{
		
		// Set uploaded file
		
		$this->upload	= $file;
		
		// If there is an upload error
		
		if ($this->upload['error'] != UPLOAD_ERR_OK)
		{
			
			// Set error
			
			new \Sonic\Message ('error', self::_uploadError ($this->upload['error']));
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Set file details
		
		$this->filename		= $this->upload['name'];
		$this->extension	= self::_getExtension ($this->filename);
		$this->name			= self::_getFilename ($this->filename);
		
		// Return TRUE
		
		return TRUE;
		
	}

	
	/**
	 * Save the uploaded file to a new location
	 * @param string $path Directory to save file into
	 * @param string $name New filename
	 * @param string $extension New extension
	 * @return boolean
	 */
	
	public function Save ($path, $name = FALSE, $extension = FALSE)
	{
		
		// Set path/name variables
		
		if (!$this->setPath ($path))
		{
			return FALSE;
		}
		
		if ($name)
		{
			$this->name	= $name;
		}
		
		if ($extension)
		{
			$this->extension	= $extension;
		}
		
		$this->filename	= $this->name . '.' . $this->extension;
		
		// Get tmp path
		
		$tmpPath	= $this->tmpPath ();
		
		if (!$tmpPath)
		{
			return FALSE;
		}
		
		// Move the file
		
		if (!move_uploaded_file ($tmpPath, $this->path . $this->filename))
		{
			
			// Set error
			
			new \Sonic\Message ('error', 'Cannot save uploaded file!');
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Return the filename including extension
	 * @return string
	 */
	
	public function getFilename ()
	{
		return $this->filename;
	}
	
	
	/**
	 * Return the filename excluding the extension
	 * @return string
	 */
	
	public function getName ()
	{
		return $this->name;
	}
	
	
	/**
	 * Return the file absolute directory path
	 * @return string
	 */
	
	public function getPath ()
	{
		return $this->path;
	}
	
	
	/**
	 * Return the filename extension
	 * @return string
	 */
	
	public function getExtension ()
	{
		return $this->extension;
	}
	
	
	/**
	 * Set absolute directory path
	 * @param string $path Directory path
	 * @return boolean
	 */
	
	public function setPath ($path)
	{
		
		if (!is_dir ($path) && !mkdir ($path, 0755, TRUE))
		{
			return FALSE;
		}
		
		if (!is_writable ($path))
		{
			return FALSE;
		}
		
		$this->path	= $path;
		return TRUE;
		
	}
	
	
	/**
	 * Return the uploaded file tmp path
	 * @return string|boolean
	 */
	
	public function tmpPath ()
	{
		
		// If there is no uploaded file
		
		if (!$this->upload)
		{
			
			// Set error
			
			new \Sonic\Message ('error', 'No file has been uploaded!');
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Return the file tmp path
		
		return $this->upload['tmp_name'];
		
	}
	
	
	/**
	 * Delete the file
	 * @return boolean
	 */
	
	public function Delete ()
	{
		
		// If there is a file and path
		
		if ($this->path && $this->filename)
		{
			
			// Delete file
			
			return self::_Delete ($this->path . $this->filename);
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Delete a file
	 * @param string $file File to delete
	 * @return boolean
	 */
	
	public static function _Delete ($file)
	{
		
		// If file doesnt exists
		
		if (!self::_Exists ($file))
		{
			return FALSE;
		}
		
		// Delete file
		
		return @unlink ($file);
		
	}
	
	
	/**
	 * Return an upload error string
	 * @param integer $error Error code
	 * @return string
	 */
	
	public static function _uploadError ($error)
	{
		
		// Return an error dependent on the error number
		
		switch ($error)
		{
			
			case UPLOAD_ERR_INI_SIZE:
				
				return 'The filesize cannot be more than ' . ini_get ('post_max_size');
				break;
				
			case UPLOAD_ERR_FORM_SIZE:
				
				return 'The filesize cannot be more than ' . $_POST['MAX_FILE_SIZE'];
				break;
				
			case UPLOAD_ERR_PARTIAL:
				
				return 'The file was only partially uploaded. Please try again.';
				break;
				
			case UPLOAD_ERR_NO_FILE:
				
				return 'No file was selected to upload!';
				break;
				
			case UPLOAD_ERR_NO_TMP_DIR:
				
				return 'There is no temporary folder to upload the file to. Please inform the site administrator.';
				break;
				
			case UPLOAD_ERR_CANT_WRITE:
				
				return 'Failed to write the file to disk.';
				break;
				
			default:
				
				return 'Unknown error.';
				break;
				
		}
		
	}
	
	
	/**
	 * Return the file extension
	 * @param string $file Filename to get extension from
	 * @return string
	 */
	
	public static function _getExtension ($file)
	{
		
		// Get Extension
		
		$base	= basename ($file);
		$arr	= explode ('.', $base);
		$ext	= strtolower (array_pop ($arr));
		
		// Return the extension
		
		return $ext;
		
	}
	
	
	/**
	 * Return the filename without the extension
	 * @param string $file Filename to get filename from
	 * @return string
	 */
	
	public static function _getFilename ($file)
	{
		
		// Remove extension
		
		$base	= basename ($file);
		$arr	= explode ('.', $base);
		array_pop ($arr);
		
		// Reset the filename
		
		$file	= NULL;
		
		// Construct the filename without the extension
		
		foreach ($arr as $part)
		{
			$file	.= $part;
		}
		
		// Return the filename
		
		return $file;
		
	}
	
	
	/**
	 * Return the file mime type
	 * @param string $file File path
	 * @return string|boolean
	 */
	
	public static function _getMIME ($file)
	{
		
		// Check file exists
		
		if (!self::_Exists ($file))
		{
			return FALSE;
		}
		
		// Open file
		
		$finfo = finfo ();
		
		// If file could not be opened
		
		if ($finfo === FALSE)
		{
			
			// Set error
			
			new \Sonic\Message ('error', 'Could not get file MIME type: ' . $file);
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Return mime type
		
		return $finfo->file ($file);
		
	}
	
	
	/**
	 * Check a file exists
	 * @param string $file File path
	 * @return boolean
	 */
	
	public static function _Exists ($file)
	{
		
		// If file doesnt exists
		
		if (!file_exists ($file))
		{
			
			// Set error
			
			new \Sonic\Message ('error', 'File does not exist: ' . $file);
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
}

// End File Class