<?php

// Define namespace

namespace Sonic\Resource;

// Start Image Class

class Image extends File
{
	
	
	/**
	 * Image resize methods
	 */
	
	const RESIZE_MAX			= 1;
	const RESIZE_MIN			= 2;
	const RESIZE_EXACT			= 3;
	const RESIZE_CROP			= 4;
	
	
	/**
	 * Load, resize and save an image from an uploaded file
	 * @param string $filename New Filename
	 * @param integer $newX New Width
	 * @param integer $newY New Height
	 * @param integer $method Resize method
	 * @param boolean $proportional Keep aspect ratio
	 * @param boolean $replace Replace existing image
	 * @return boolean
	 */
	
	public function createFromUpload ($filename = FALSE, $newX = FALSE, $newY = FALSE, $method = self::RESIZE_MAX, $proportional = FALSE, $replace = FALSE)
	{
		
		// If there is no absolute path
		
		if (!$this->path)
		{
			new \Sonic\Message ('error', 'No absolute path set!');
			return FALSE;
		}
		
		// Load image
		
		if (!($objImage = self::_Load ($this->tmpPath ())))
		{
			return FALSE;
		}
		
		// If a new image width and height hae been passed rezize image
		
		if ($newX !== FALSE && $newY !== FALSE)
		{
			$objImage	= self::_Resize ($objImage, $newX, $newY, $method, $proportional);
		}
		
		// If there is no new filename set to the old one
		
		if (!$filename)
		{
			$filename	= $this->getFilename ();
		}
		
		// Save image
		
		if (!self::_Save ($objImage, $this->getPath () . $filename, $replace))
		{
			new \Sonic\Message ('error', 'Could not save image: ' . $filename);
			return FALSE;
		}
		
		// Destroy image
		
		imagedestroy ($objImage);
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Load image as a resource
	 * @param string $path Image filepath to load
	 * @return resource
	 */
	
	public static function _Load ($path)
	{
		
		// Check file exists
		
		if (!parent::_Exists ($path))
		{
			return FALSE;
		}
		
		// Set image resource
		
		$image	= FALSE;
		
		// Switch image type and load
		
		switch (self::_getMIME ($path))
		{
			
			// gif
			
			case IMAGETYPE_GIF:
				$image		= imagecreatefromgif ($path);
				break;
				
			// png	
			
			case IMAGETYPE_PNG:
				$resImg		= imagecreatefrompng ($path);
				break;
				
			// jpeg / default	
			
			default:
				$image		= imagecreatefromjpeg ($path);
				break;			
			
		}
		
		// Return image resource
		
		return $image;
		
	}
	
	
	/**
	 * Save an image resource
	 * @param resource $image Image resource
	 * @param string $path File path to save
	 * @param boolean $replace Replace if exists
	 * @return boolean
	 */
	
	public static function _Save ($image, $path, $replace = FALSE)
	{
		
		// If a file with the same name already exists
		
		if ($replace === FALSE && parent::_Exists ($path))
		{
//			new \Sonic\Message ('error', 'An image with this filename already exists: ' . $path);
			return FALSE;
		}
		
		// Switch new path extension to save file
		
		switch (parent::_getExtension ($path))
		{
			
			// gif
			
			case 'gif':
			
				if (!imagegif ($image, $path))
				{
					return FALSE;
				}
				
				break;
			
			// png
			
			case 'png':
			
				if (!imagepng ($image, $path))
				{
					return FALSE;
				}
				
				break;
				
			// default
				
			default:
				
			
				if (!imagejpeg ($image, $path, 80))
				{
					return FALSE;
				}
				
				break;
			
		}
		
		// Successfully saved, return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Resize an image resource
	 * @param resource $image Image Resource
	 * @param integer $newX New Width
	 * @param integer $newY New Height
	 * @param integer $method Resize method
	 * @param boolean $proportional Resize keeping image aspect ratio
	 * @return resource
	 */
	
	public static function _Resize ($image, $newX, $newY, $method = self::RESIZE_MAX, $proportional = TRUE)
	{
		
		// Get original image sizes
		
		$origX	= imagesx ($image);
		$origY	= imagesy ($image);
		
		// Set canvas sizes
		
		$canvasX	= $newX;
		$canvasY	= $newY;
		
		// Switch method
		
		switch ($method)
		{
			
			// Maximum Resize
			
			case self::RESIZE_MAX:
				
				// If the width is relatively bigger than the height
				
				if ($origX / $newX > $origY / $newY)
				{
					
					// Resize the image so the width is the max
					
					$newY		= ($newX / $origX) * $origY;
					$canvasY	= $newY;
					
				}
				
				// Else the height is bigger than the width
				
				else
				{
					
					// Resize the image so the height is the max
					
					$newX		= ($newY / $origY) * $origX;
					$canvasX	= $newX;
					
				}
				
				// Break
				
				break;
				
			
			// Minimum resize
			
			case self::RESIZE_MIN:
				
				// If the width is relatively bigger than the height
				
				if ($origX / $newX > $origY / $newY)
				{
					
					// Resize the image so the height is the max
					
					$newX		= ($newY / $origY) * $origX;
					$canvasX	= $newX;
					
				}
				
				// Else the height is bigger than the width
				
				else
				{
					
					// Resize the image so the width is the max
					
					$newY		= ($newX / $origX) * $origY;
					$canvasY	= $newY;
					
				}
				
				// Break
				
				break;
				
			
			// Exact resize
			
			case self::RESIZE_EXACT:
				
				// If proportional
				
				if ($proportional)
				{
					
					// If the original width is bigger
					
					if ($origX / $newX > $origY / $newY)
					{
						
						// Resize the image so the width is the max
						
						$newY	= ($newX / $origX) * $origY;
						
					}
					
					// Else the height is bigger than the width
					
					else
					{
						
						// Resize the image so the height is the max
						
						$newX	= ($newY / $origY) * $origX;
						
					}
					
				}
				
				// Break
				
				break;


			// Exact resize with cropping

			case self::RESIZE_CROP:

				// If proportional

				if ($proportional)
				{

					// If the width is relatively bigger than the height

					if ($origX / $newX > $origY / $newY)
					{

						// Resize the image and crop

						$newX	= ($newY / $origY) * $origX;

					}

					// Else the height is bigger than the width

					else
					{

						// Resize the image and crop

						$newY	= ($newX / $origX) * $origY;

					}

				}

				// Break

				break;

		}
		
		// Calculate new image position to center
		
		$posX	= ($canvasX - $newX) / 2;
		$posY	= ($canvasY - $newY) / 2;

		// Copy image and create new resized image
		
		$newImage		= imagecreatetruecolor ($canvasX, $canvasY);
		
		// Set white colour
		
		$colourWhite	= imagecolorallocate ($newImage, 0xFF, 0xFF, 0xFF);
		
		// Set background
		
		imagefill ($newImage, 0, 0, $colourWhite);
		
		// Resize and resample image
		
		imagecopyresampled ($newImage, $image, $posX, $posY, 0, 0, $newX, $newY, $origX, $origY);
		
		// Return new image
		
		return $newImage;
		
	}	
	
	
	/**
	 * Resize and copy an image
	 * @param string $originalPath Original image path
	 * @param string $newPath New file path
	 * @param integer $newX New Width
	 * @param integer $newY New Height
	 * @param integer $method Resize method
	 * @param boolean $proportional Keep image aspect ratio
	 * @return boolean
	 */
	
	public static function _copyResize ($originalPath, $newPath, $newX, $newY, $method = self::RESIZE_MAX, $proportional = TRUE)
	{
		
		// Check file exists
		
		if (!parent::_Exists ($originalPath))
		{
			return FALSE;
		}
		
		// Load image
		
		$image	= self::_Load ($originalPath);
		
		// Resize image
		
		$newImage	= self::_Resize ($image, $newX, $newY, $method, $proportional);
		
		// Save new image
		
		if (!self::_Save ($newImage, $newPath, TRUE))
		{
			
			// If the image cannot be saved set an error
			
			new \Sonic\Message ('error', 'Could not save image: ' . $newPath);
			
			// Delete images
			
			imagedestroy ($image);
			imagedestroy ($newImage);
			
			// return FALSE
			
			return FALSE;
			
		}
		
		// Delete images
		
		imagedestroy ($image);
		imagedestroy ($newImage);
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Return the file mime type
	 * @param string $path File path
	 * @return string|boolean
	 */
	
	public static function _getMIME ($path)
	{
		
		// Check file exists
		
		if (!parent::_Exists ($path))
		{
			return FALSE;
		}
		
		// Get image data
		
		$arrData	= getimagesize ($path);
		
		// Return mime type
		
		return $arrData[2];
		
	}
	
	
	/****************************************************
	*                                                   *
	*    Function _validMIME                            *
	*    Check a file MIME type is valid                *
	*    @param string $path File Path               *
	*    @param array $allowed Allowed MIME types    *
	*    @return boolean                                *
	*                                                   *
	****************************************************/
	
	public static function _validMIME ($path, $allowed = array (IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))
	{
		
		// Get mime type
		
		$mime	= self::_getMIME ($path);
		
		// If mime type could not be retreived
		
		if ($mime === FALSE)
		{
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// If the mime type is not in the array
		
		if (!in_array ($mime, $allowed))
		{
			
			// Generate the allowed types string
			
			$allowedTypes	= NULL;
			$x				= 0;
			
			foreach ($allowed as $type)
			{
				
				$x++;
				
				switch ($type)
				{
					case IMAGETYPE_GIF		: $allowedTypes .= 'gif';	break;
					case IMAGETYPE_JPEG		: $allowedTypes .= 'jpg';	break;
					case IMAGETYPE_PNG		: $allowedTypes .= 'png';	break;
					case IMAGETYPE_SWF		: $allowedTypes .= 'swf';	break;
					case IMAGETYPE_PSD		: $allowedTypes .= 'psd';	break;
					case IMAGETYPE_BMP		: $allowedTypes .= 'bmp';	break;
					case IMAGETYPE_TIFF_II	: $allowedTypes .= 'tiff';	break;
					case IMAGETYPE_TIFF_MM	: $allowedTypes .= 'tiff';	break;
					case IMAGETYPE_JPC		: $allowedTypes .= 'jpc';	break;
					case IMAGETYPE_JP2		: $allowedTypes .= 'jp2';	break;
					case IMAGETYPE_JPX		: $allowedTypes .= 'jpf';	break;
					case IMAGETYPE_JB2		: $allowedTypes .= 'jb2';	break;
					case IMAGETYPE_SWC		: $allowedTypes .= 'swc';	break;
					case IMAGETYPE_IFF		: $allowedTypes .= 'aiff';	break;
					case IMAGETYPE_WBMP		: $allowedTypes .= 'wbmp';	break;
					case IMAGETYPE_XBM		: $allowedTypes .= 'xbm';	break;
				}
				
				if ($x < count ($allowed))
				{
					$allowedTypes	.= ', ';
				}
				
			}
			
			// Set an error
			
			new \Sonic\Message ('error', 'Invalid image type: image must be ' . $allowedTypes);
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
}