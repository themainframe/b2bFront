<?php
/**
 * Admin Startup
 * Sets up the Admin subsystem.
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Define the root path of the application
$_ROOT = getcwd() . '/';

//
// No more configuration beyond this point
//

// Verify root
if(file_exists($_ROOT) && is_dir($_ROOT))
{
  // Define as supplied root location
  define('BF_ROOT', $_ROOT);
}
else
{
  // Reset as the discovered directory
  // Use a custom path for this
  define('BF_ROOT', '/var/www/');
}

// Load SQL connection data
require BF_ROOT . 'sql_detail.php';

// Define the autoload function for loading classes
function __autoload($className)
{
  // Try to load a kernel class
  if(file_exists(BF_ROOT . 'classes/' . $className . '.class.php'))
  {
    include BF_ROOT . 'classes/' . $className . '.class.php';
    return true;
  }  

  // Try to load a handling class
  if(file_exists(BF_ROOT . 'acp/classes/' . $className . '.class.php'))
  {
    include BF_ROOT . 'acp/classes/' . $className . '.class.php';
    return true;
  }
  
  // Try to load an API class
  if(file_exists(BF_ROOT . 'acp/classes/API/' . $className . '.class.php'))
  {
    include BF_ROOT . 'acp/classes/API/' . $className . '.class.php';
    return true;
  }
  
  // Failed to find a suitable class
  return false;
}

// Error Management
include BF_ROOT . '/error_management.php';

?>