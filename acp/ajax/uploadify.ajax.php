<?php
/**
 * Uploadify Connector
 * AJAX Responder
 *
 * Works with the Uploadify jQuery plugin.
 * http://www.uploadify.com
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Change config domain
$BF->config->setPath('com.b2bfront.acp');

// Create admin object
$BF->admin = new Admin(& $BF);

// Check admin validity
if(!$BF->admin->isAdmin)
{
  exit();
}

// Get SKU
$SKU = strtoupper($BF->in('sku'));

// Admin verified OK
// Set the name of the field
$fieldName = 'Filedata';

// Allowed types
$allowedTypes = array(
  'jpg',
  'jpeg',
  'png',
  'gif'
);

// Were files uploaded?
if(empty($_FILES)) 
{
  // No files found
  exit();
}

// Temporary file
$temporaryFile = $_FILES[$fieldName]['tmp_name'];
$temporaryName = $_FILES[$fieldName]['name'];

// Check files
if(!$temporaryFile && !$temporaryName)
{
  $BF->log('Could not find a name for an uploaded file.');
  exit();
}

// Log
$BF->log('Initiating upload of ' . $temporaryName);

// Verify the type
$nameInformation = Tools::fileNameAndExt($temporaryName);

if(!$nameInformation)
{
  $BF->log('File ' . $temporaryName . ' could not be split into extension and name.');
  exit();
}

if(!in_array(strtolower($nameInformation['ext']), $allowedTypes))
{
  // Not allowed
  $BF->log('File ' . $temporaryName . ' is not allowed on this server.');
  header('HTTP/1.0 404 Not Found');
  exit();
}

// Make an API call to move the image
$result = $BF->admin->api('Images')
                    ->createImage($temporaryFile, $temporaryName, $SKU);

if(!$result)
{
  $BF->log('Failed API call: createImage - ' . $result);
  exit();
}

// Output JSON containing the result
print json_encode($result);

// Finished
$BF->shutdown();

?>