<?php
/**
 * Images
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 2.0
 * @author Damien Walsh
 */
class Images extends API
{
  /**
   * The type of file to create when producing thumbnails
   * Should be either 'jpg', 'png' or 'gif'
   * @var string
   */
  private $thumbnailType = 'jpg';
  
  /**
   * The path of the hard image directory
   * @var string
   */
  private $hardDirectory = '/store/image/';
  
  /**
   * Init (Psuedo-constructor) method
   * @return boolean
   */
  public function init()
  {
    // Load configuration values
    $this->hardDirectory = $this->parent->config->get('com.b2bfront.images.hard', true);
    $this->thumbnailType = $this->parent->config->get('com.b2bfront.images.thumbnail-type', true);
    
    return true;
  }
  
  /**
   * Create an image and return an Image ID
   *
   * NB: If the description is not provided or is unsuitable a random name
   *     will be automatically chosen.
   *
   * @param string $uploadedFile The path to the uploaded file, usually from $_FILES['...']['tmp_name']
   * @param string $uploadedName The name of the uploaded file, usually from $_FILES['...']['name']
   * @param string $description Optionally a description of the image.
   * @return integer
   */
  public function createImage($uploadedFile, $uploadedName, $description)
  {
    // Get basic name information
    $nameInformation = Tools::fileNameAndExt($uploadedName);
  
    if(!$nameInformation)
    {
      // Can't get a valid name
      $this->parent->log('Images: Cannot find a name for ' . $uploadedName);
      return false;
    }
    
    // Try to generate a nice name
    if($description)
    {
      // New location is based on the $description.
      $newLocation = Tools::randomPath(BF_ROOT . '/' . $this->hardDirectory, $nameInformation['ext'],
                                       Tools::removeNonPath($description));
    }
    else
    {
      // New location is based on randomness only.
      $newLocation = Tools::randomPath(BF_ROOT . '/' . $this->hardDirectory, $nameInformation['ext']);
    }
    
    
    // Upladed?
    if(is_uploaded_file($uploadedFile))
    {
      // Move the uploaded file in to place
      if(!move_uploaded_file($uploadedFile, $newLocation))
      {
        // Cannot move the uploaded file
        $this->parent->log('Images: Uploaded ' . $uploadedName . 
          ' could not be moved to ' . $newLocation);
        return false;
      }
    }
    else
    {
      // Move the new file
      if(!rename($uploadedFile, $newLocation))
      {
        // Cannot move the file
        $this->parent->log('Images: ' . $uploadedName . 
          ' could not be moved to ' . $newLocation);
        return false;
      }
    }
        
    // Create an array of information
    list($width, $height) = getimagesize($newLocation);
    
    $imageInfo = array(
      'name' => $uploadedName,
      'path' => $newLocation,
      'width' => $width,
      'height' => $height,
      'url' => Tools::cleanPath('/' . $this->hardDirectory . '/' . basename($newLocation))
    );
    
    // Create thumbnails in the new location and keep track of URLs
    $imageInfo['thumbnails'] = $this->makeThumbnails($newLocation, dirname($newLocation));
    
    // Add to DB
    $this->parent->db->insert('bf_images', array(
                               'url' => $this->parent->config->get('com.b2bfront.site.url', true) . 
                                        Tools::cleanPath('/' . $this->hardDirectory . '/' . basename($newLocation)),
                               'size_x' => $imageInfo['width'],
                               'size_y' => $imageInfo['height'],
                               'size_bytes' => filesize($newLocation),
                               'timestamp' => time()  
                             ))
                     ->execute();
                     
    // Add ID to image info
    $imageInfo['id'] = $this->parent->db->insertID;
                     
    return $imageInfo;
  }
  
  /**
   * Return true if this class supports the filename passed in $path
   * Based on file ext.
   * @param string $path The path to test
   * @return boolean
   */
  public function supportsFile($path)
  {
    // Get basic name information
    $nameInformation = Tools::fileNameAndExt($path);
    
    if(!$nameInformation)
    {
      // Can't get a valid name
      return false;
    }
    
    // Check the ext
    $lowerCaseExt = strtolower($nameInformation['ext']);
    
    // Supported types
    $supported = array(
      'jpg',
      'gif',
      'png',
      'jpeg'
    );  
    
    return in_array($lowerCaseExt, $supported);
  }
   
  /**
   * Create thumbnails for the specified file and place them in a (optionally) specified
   * directory.  If no directory is specified the files will be placed in the same directory.
   * Returns an array of thumbnail file paths.
   * @param string $path The path to the image to create thumbnails.
   * @param string $thumbnailDirectory Optionally a path to a directory into which thumbnails are saved.
   * @return array|boolean
   */
  private function makeThumbnails($path, $thumbnailDirectory = '')
  {
    // Check the file
    if(!file_exists($path))
    {
      return false;
    }
    
    // Get the basename and ext
    $nameInformation = Tools::fileNameAndExt($path);
    
    // Basename OK?
    if(!$nameInformation)
    {
      return false;
    }
    
    // Load thumbnail sizes
    $thumbnailQuery = $this->parent->db->query();
    $thumbnailQuery->select('*', 'bf_image_thumbnail_sizes')
                   ->execute();
             
    // Collect thumbnail URLs
    $thumbnailURLs = array();
             
    // Create each size
    while($thumbnailSize = $thumbnailQuery->next())
    {
      // Create the thumbnail in this size and name it with the suffix
      // For example, for thumbnail suffix 'lrg':
      // big-motorbike.jpg  =>  big-motorbike-lrg.jpg
      
      $lcaseExt = strtolower($nameInformation['ext']);
      $imageHandle = null;
      
      switch($lcaseExt)
      {
        case 'png':
          $imageHandle = imagecreatefrompng($path);
          break;
          
        case 'jpg':
        case 'jpeg':
          $imageHandle = @imagecreatefromjpeg($path);
          break;
          
        case 'gif':
          $imageHandle = imagecreatefromgif($path);
          break;
      }
      
      if(!$imageHandle)
      {
        // Failed to make a handle for the thumbnail
        $this->parent->log('Images: ' . 'Failed to create thumbnail \'' . $thumbnailSize->name . '\' for ' . 
                           $path . ' - Could not create GD resource handle - ' . 
                           (file_exists($path) ? 'File exists' : 'File Missing!') . ' - Length of file is ' . 
                           filesize($path));
        return false;
      }
      
      // Get the size of the image
      list($width, $height) = getimagesize($path);
      
      // Create a new image of this size
      $imageNew = imagecreatetruecolor($thumbnailSize->width, $thumbnailSize->height);
      
      // If the thumbnail size is catagorically larger than the current size,
      // do not scale the image, place it on a background and center it.
      if($thumbnailSize->width > $width && $thumbnailSize->height > $height)
      {
        // Center the image on a white background
        $white = imagecolorallocate($imageNew, 0xFF, 0xFF, 0xFF);
        imagefill($imageNew, 0, 0, $white);
        
        // Place the image in the center
        $centerX = intval(($thumbnailSize->width / 2) - ($width / 2));
        $centerY = intval(($thumbnailSize->height / 2) - ($height / 2));
        
        // Place the image
        imagecopy($imageNew,
                 $imageHandle, 
                 $centerX, 
                 $centerY,
                 0, 0, 
                 $width,
                 $height
                );
      }
      else
      {
        // Make the image fit inside the thumbnail, start by finding the 
        // image's largest dimension
        $widthIsLargest = ($width > $height);
        
        // Calculate the new sizes
        if($widthIsLargest)
        {
          // Scale the width down
          $newWidth = $thumbnailSize->width;
          $newHeight = intval($height / ($width / $thumbnailSize->width));
        }
        else
        {
          // Scale the height down
          $newHeight = $thumbnailSize->height;
          $newWidth = intval($width / ($height / $thumbnailSize->height));
        }
      
        // Create a canvas
        $temporaryCanvas = imagecreatetruecolor($newWidth, $newHeight);
        
        // Copy and resample the file onto the new canvas
        imagecopyresampled($temporaryCanvas, 
                           $imageHandle, 0, 0, 0, 0,
                           $newWidth, 
                           $newHeight, 
                           $width,
                           $height
                          );
                          
        // Center it on the thumbnail
        $white = imagecolorallocate($imageNew, 0xFF, 0xFF, 0xFF);
        imagefill($imageNew, 0, 0, $white);
        
        // Place the image in the center
        $centerX = intval(($thumbnailSize->width / 2) - ($newWidth / 2));
        $centerY = intval(($thumbnailSize->height / 2) - ($newHeight / 2));
        
        // Place the image
        imagecopy($imageNew,
                  $temporaryCanvas, 
                  $centerX, 
                  $centerY,
                  0, 0, 
                  $newWidth,
                  $newHeight
                 );
      }
                        
      // Export the file as it's original type with the prefix
      $filenameNew = $thumbnailDirectory . '/' . $nameInformation['name'] . 
                     '-' . $thumbnailSize->suffix . '.' . $nameInformation['ext'];
      
      // Clean path
      $filenameNew = Tools::cleanPath($filenameNew);
            
      $result = false;  
                   
      switch($lcaseExt)
      {
        case 'png':
          $this->parent->log('Images: ' . 'Writing thumbnail \'' . $thumbnailSize->name . '\' as PNG.');
          $result = imagepng($imageNew, $filenameNew);
          break;
          
        case 'jpg':
        case 'jpeg':
          $this->parent->log('Images: ' . 'Writing thumbnail \'' . $thumbnailSize->name . '\' as JPEG.');
          $result = imagejpeg($imageNew, $filenameNew);
          break;
          
        case 'gif':
          $this->parent->log('Images: ' . 'Writing thumbnail \'' . $thumbnailSize->name . '\' as GIF.');
          $result = imagegif($imageNew, $filenameNew);
          break;
      }
    

      if(!$result)
      {
        // Failed to save a thumbnail
        $this->parent->log('Images: ' . 'Failed to save thumbnail \'' . $thumbnailSize->name . '\' data for ' . 
                           $path . ' - Could not write to ' . $filenameNew);
      }
      
      // Add to list of thumbnails so far
      $thumbnailURLs[$thumbnailSize->suffix] = $this->parent->config->get('com.b2bfront.site.url', true) .
                                               Tools::cleanPath('/' . Tools::relativePath($thumbnailDirectory) . 
                                               '/' . $nameInformation['name'] . '-' . $thumbnailSize->suffix . 
                                               '.' . $nameInformation['ext']);
    }
    
    return $thumbnailURLs;
  }
  
  /**
   * Remove an image from the DB and hard storage
   * @param integer $imageID The ID of the image to remove
   * @return boolean
   */
  public function remove($imageID)
  {
    // Get the image first
    $imageRemoval = $this->parent->db->getRow('bf_images', $imageID);
    
    // Remove hard-linked image
    $url = $this->parent->config->get('com.b2bfront.site.url', true);
    $path = '/' . str_replace($url, '', $imageRemoval->url);
    
    // Remove the root image
    @unlink(BF_ROOT . '/' . $path);
    
    $this->parent->log('Images: ' . 'Untouched ' . basename($path) . 
                       ' from the filesystem.');
    
    // Remove associated thumbnails
    // Load thumbnail sizes
    $thumbnailQuery = $this->parent->db->query();
    $thumbnailQuery->select('*', 'bf_image_thumbnail_sizes')
                   ->execute();
      
    // Get name and extension
    $nameInformation = Tools::fileNameAndExt($path);
                   
    while($thumbnailSize = $thumbnailQuery->next())
    {
      // Build path
      $thumbnailPath = BF_ROOT . '/' . $this->hardDirectory . '/' . $nameInformation['name'] . 
                       '-' . $thumbnailSize->suffix . '.' . $nameInformation['ext'];
      
      $this->parent->log('Images: ' . 'Untouched ' . $nameInformation['name'] . '-' . 
                         $thumbnailSize->suffix . '.' . $nameInformation['ext'] . 
                         ' from the filesystem.');
                       
      // Attempt removal
      @unlink($thumbnailPath);
    }
    
    // Remove from the DB
    $this->parent->db->delete('bf_images')
                     ->where('`id` = \'{1}\'', $imageID)
                     ->limit(1)
                     ->execute();
    
    // Unlink from all items to avoid key violations
    $this->parent->db->delete('bf_item_images')
                     ->where('`image_id` = \'{1}\'', $imageID)
                     ->execute();

    // Unlink from all categories to avoid key violations
    $this->parent->db->update('bf_categories', array(
                         'image_id' => -1
                       ))
                     ->where('`image_id` = \'{1}\'', $imageID)
                     ->execute();

    $this->parent->log('Images: ' . 'Image ' . $imageID . 
                       ' was removed.');
    
    return true;
  }
  
  /**
   * Resize and replace an image
   * Automatically uses the correct format and file ext.
   * @param string $path The path to the image to resize
   * @param integer $largestDimension The new largest dimesion of the image
   * @return boolean
   */
  public function resizeReplace($path, $largestDimension)
  {
    // Check the file
    if(!Tools::exists($path))
    {
      $this->parent->log($path . ' does not exist - While resizing image');
      return false;
    }
    
    // Get basic name information
    $nameInformation = Tools::fileNameAndExt($path);
    
    if(!$nameInformation)
    {
      // Can't get a valid name
      $this->parent->log('Cannot obtain a valid name for ' . 
                         $path . ' - While resizing image');
      
      return false;
    }
    
    // Decide how to open the file
    $imageHandle = null;
    $lowerCaseExt = strtolower($nameInformation['ext']);
    
    switch($lowerCaseExt)
    {
      case 'png':
        $imageHandle = imagecreatefrompng($path);
        break;
        
      case 'jpg':
      case 'jpeg':
        $imageHandle = imagecreatefromjpeg($path);
        break;
        
      case 'gif':
        $imageHandle = imagecreatefromgif($path);
        break;
    }
    
    if(!$imageHandle)
    {
      // Failed to make a handle for the thumbnail
      $this->parent->log('Failed to open for resizing: ' . 
                         $path . ' - Could not create GD resource handle');
      return false;
    }
    
    // Get the size of the image
    list($width, $height) = getimagesize($path);
    
    // Decide how to scale the image
    $heightScalable = ($height > $width);

    // Calculate the scale factor to lower the width/height to the $largestDimension value
    $scaleFactor = $largestDimension / ($heightScalable ? $height : $width);
    
    // New sizes
    $newWidth = intval($width * $scaleFactor);
    $newHeight = intval($height * $scaleFactor);
  
    // Create a new image of this size
    $imageNew = imagecreatetruecolor($newWidth, $newHeight);
    
    // Copy and resample the file
    imagecopyresampled($imageNew, 
                       $imageHandle, 0, 0, 0, 0,
                       $newWidth, 
                       $newHeight, 
                       $width,
                       $height
                      );
    
    // Decide how to save the file
    $result = false;
    switch($lowerCaseExt)
    {
      case 'png':
        $result = imagepng($imageNew, $path);
        break;
        
      case 'jpg':
      case 'jpeg':
        $result = imagejpeg($imageNew, $path);
        break;
        
      case 'gif':
        $result = imagegif($imageNew, $path);
        break;
    }
    
    if(!$result)
    {
      // Failed to save a thumbnail
      $this->parent->log('Failed to save resized image data for ' . 
                         $path . ' - Could not write to ' . $path);
      
      return false;
    }
    
    return true;
  }
}
?>