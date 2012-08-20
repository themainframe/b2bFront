<?php
/** 
 * Startup procedures file
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.8
 * @author Damien Walsh <damien@transcendsolutions.net>
 */

// Context check
if(!defined('BF_CONTEXT_INDEX'))
{
  exit();
}

// Define the debug mode
define('BF_PROFILING', false);

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
  define('BF_ROOT', $_ROOT);
}

// Load SQL connection data
require BF_ROOT . 'sql_detail.php';

// Define the autoload function for loading classes
function __autoload($className)
{
  // Try class files
  if(file_exists(BF_ROOT . 'classes/' . $className . '.class.php'))
  {
    include BF_ROOT . 'classes/' . $className . '.class.php';
    return true;
  }
  
  // Try model files
  if(file_exists(BF_ROOT . 'models/' . $className . '.model.php'))
  {
    include BF_ROOT . 'models/' . $className . '.model.php';
    return true;
  }
  
  // Lastly, try Interfaces
  if(file_exists(BF_ROOT . 'interfaces/' . $className . '.interface.php'))
  {
    include BF_ROOT . 'interfaces/' . $className . '.interface.php';
    return true;
  }
 
  return false;
}

// Define the default exception handler
set_exception_handler(array('BFClass', 'handleException'));

// Also redirect errors as exceptions
set_error_handler(array('BFClass', 'handleError'));

?>