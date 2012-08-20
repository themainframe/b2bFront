<?php
/**
 * Files
 * Admin API
 *
 * Manages general files uploaded by the ACP.
 * Does not manage images.  Because of the thumbnailing behaviour required by
 * image files, these files will be managed by the Images API class.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Files extends API
{
  /**
   * The file storage directory
   * @var string
   */
  private $fileStore = '/store/etc/';

  /**
   * Init (Psuedo-constructor) method
   * @return boolean
   */
  public function init()
  {
    // Load configuration values
    $this->fileStoreDirectory = $this->parent->config->get('com.b2bfront.files.store', true);
    
    return true;
  }

  /**
   * Uploads a file and assigns it a part-random name.
   * Applies a TTL to the file if required.
   *
   * Returns an absolute path to the file.
   * These paths will contain some or all of $uploadedName.
   *
   * @param string $uploadedFile The path to the uploaded file, usually from $_FILES['...']['tmp_name']
   * @param string $uploadedName The name of the uploaded file, usually from $_FILES['...']['name']
   * @param integer $TTL Optionally the time-to-live of the file in seconds. Default no TTL.
   * @param string $alternativeLocation Optionally a location instead of $fileStore to upload to.
   * @return string
   */
  public function upload($uploadedFile, $uploadedName, $TTL = -1, $alternativeLocation = '')
  {
    // Get basic name information
    $nameInformation = Tools::fileNameAndExt($uploadedName);
    
    if(!$nameInformation)
    {
      // Can't get a valid name
      $this->parent->log('Files: Cannot find a name for ' . $uploadedName);
      return false;
    }
    
    // Generate a similar yet random name
    $newLocation = $this->randomPath(BF_ROOT . '/' . 
                                    ($alternativeLocation == '' ? 
                                    $this->fileStore : $alternativeLocation), $nameInformation['ext'],
                                     Tools::removeNonPath($nameInformation['name']));

    
    // Log
    $this->parent->log('Files: ' . $uploadedName . ' will be moved to ' . $newLocation);
    
    // Move the file in to place
    if(!move_uploaded_file($uploadedFile, $newLocation))
    {
      // Cannot move the file
      $this->parent->log('Files: ' . $uploadedName . ' could not be moved to ' . $newLocation);
      return false;
    }
    
    // Should I set a TTL?
    if($TTL)
    {
      // Set a TTL on this file
      $this->parent->setFileTTL($newLocation, $TTL);
    }
    
    // OK
    return Tools::cleanPath($newLocation);
  }
 
  /**
   * Obtain a free private path in the specified directory
   * @param string $path The path to search for a free name
   * @param string $ext The file extension to maintain. Default jpg.
   * @param string $base Optionally a base for the random path.
   * @return string 
   */
  private function randomPath($path, $ext, $base = '')
  {
    // Create a seed
    $randomSeed = rand(0, 999999);
    
    while(Tools::exists($path . '/' . ($base ? $base . '-' : '') . 
                      substr(md5($randomSeed), 0, ($base ? 4 : 10)) . '.' . $ext)
         )
    {
      $randomSeed = rand(0, 999999);
    }
    
    return $path . '/' . ($base ? $base . '-' : '') . 
           substr(md5($randomSeed), 0, ($base ? 4 : 10)) . '.' . $ext;
  }
  
}
?>