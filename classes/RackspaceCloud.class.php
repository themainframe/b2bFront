<?php
/** 
 * RackspaceCloud class
 * Provides access to Rackspace Cloud Web Services API
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class RackspaceCloud extends Base
{
  /**
   * A Rackspace Cloud connection object
   * @var object
   */
  private $connection = null;

  /**
   * Init (Psuedo-constructor) method
   * @return boolean
   */
  public function init()
  {
    // Include the library files
    require_once BF_ROOT . '/libraries/rackspace/cloudfiles.php';
    
    return true;
  }
  
  /**
   * Connect to the Rackspace Cloud
   * @return boolean True on success, False on failure
   */
  public function connect()
  {
    // Already linked?
    if($this->connection)
    {
      $this->parent->log('Resuming existing Rackspace Cloud session.');
      return true;
    }
  
    // Get details from config
    $rscUsername = $this->parent->config->get('com.b2bfront.rackspace.username', true);
    $rscAPIkey = $this->parent->config->get('com.b2bfront.rackspace.api-key', true);
    
    // Failed to load config
    if(!$rscUsername || !$rscAPIkey)
    {
      // Cannot connect
      throw new Exception('Cannot connect to Rackspace Cloud Web Services API with no config. ' . 
                          'Check com.b2bfront.rackspace.*');
      
      return false;
    }
    
    // Connect
    $authentication = new CF_Authentication($rscUsername, $rscAPIkey);
    $authentication->authenticate();
    
    // Success?
    if(!$authentication)
    {
      return false;
    }
    
    // Create link
    $this->connection = new CF_Connection($authentication);
    
    // Success?
    if(!$this->connection)
    {
      return false;
    }
    
    $this->parent->log('Online with The Rackspace Cloud Web Services API.');
    
    // We are now online with Rackspace Cloud Web Services API
    return true;
  }
  
  /**
   * Get the total data used on Rackspace Cloud for the container
   * @return integer
   */
  public function getUsedBytes()
  {
    // Check connection
    if(!$this->connection)
    {
      // Cannot transact without an active connection
      $this->parent->log('Cannot perform RSC transactions when not connected.');
      return false;
    }
    
    // Obtain the container name
    $containerName = $this->parent->config->get('com.b2bfront.rackspace.container', true);
    
    $this->parent->log('Attempting to open RSC container: ' . $containerName);
    
    // Open container
    $container = $this->connection->get_container($containerName);
    
    // Get object list
    $objects = $container->get_objects();
    
    // Add sizes
    $totalSize = 0;
    foreach($objects as $object)
    {
      $totalSize += $object->content_length;
    }
    
    return $totalSize;
  }
  
  /**
   * Push the specified file to the Web Services API
   * Returns the URL of the object on the Content Distribution Network (CDN)
   * @param string $path The path to the file to upload.
   * @return string|boolean
   */
  public function push($path)
  {
    // Check file
    if(!file_exists($path))
    {
      $this->parent->log('The file ' . $path . ' cannot be uploaded to RSC.');
      return false;
    }
    
    // Check connection
    if(!$this->connection)
    {
      // Cannot transact without an active connection
      $this->parent->log('Cannot perform RSC transactions when not connected.');
      return false;
    }
    
    // Obtain the container name
    $containerName = $this->parent->config->get('com.b2bfront.rackspace.container', true);
    
    $this->parent->log('Attempting to open RSC container: ' . $containerName);
    
    // Open container
    $container = $this->connection->get_container($containerName);
    
    if(!$container)
    {
      $this->parent->log('Could not obtain the RSC container ' . $containerName);
      return false;
    }
    
    // Create empty object
    $object = $container->create_object(basename($path));
    
    // Upload the data
    if(!$object->load_from_filename($path))
    {
      $this->parent->log('The file ' . $path . ' could not be transferred to RSC.');
      return false;
    }
  
    $this->parent->log('Transferred ' . filesize($path) . ' bytes to RSC.');
    
    // Finished, return the URL
    return $this->parent->config->get('com.b2bfront.rackspace.container-url', true) . basename($path);
  }
  
  /** 
   * Remove a file via the Rackspace Cloud Services API
   *
   * NB: This method will remove the file from the cloud.
   *     The action cannot be undone without a backup.
   *
   * @param string $nodeName The file to remove from the cloud
   * @return boolean
   */
  public function delete($nodeName)
  {
    // Check connection
    if(!$this->connection)
    {
      // Cannot transact without an active connection
      $this->parent->log('Cannot perform RSC transactions when not connected.');
      return false;
    }
    
    // Obtain the container name
    $containerName = $this->parent->config->get('com.b2bfront.rackspace.container', true);
    
    $this->parent->log('Attempting to open RSC container: ' . $containerName);
    
    // Open container
    $container = $this->connection->get_container($containerName);
  
    // Remove a file
    $container->delete_object($nodeName);
  
    return true;
  }
  
  /**
   * Close the connection to the Rackspace Cloud Web Services API
   * @return boolean
   */  
  public function close()
  {
    if($this->connection && method_exists($this->connection, 'close'))
    {
      $this->parent->log('Disconnected from The Rackspace Cloud Web Services API.');
      $this->connection->close();
    }
  }
}
?>