<?php
/**
 * Module: Website
 * Mode: Do Add Download
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}


// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => array(
                                 'unique' => array('bf_downloads', 'name'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Title'
                   
              )
              
);

// Check each field
foreach($validation as $fieldName => $fieldData)
{
  // Create a validator
  $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);

  // Check
  if($validator->batch($fieldData['validations'])->failed())
  {
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=website&mode=downloads_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Upload the file
$temporaryFile = $_FILES['f_file']['tmp_name'];
$temporaryName = $_FILES['f_file']['name'];
    
// Upload the file - no TTL, upload in to /store/downloads/
$result = $BF->admin->api('Files')
                    ->upload($temporaryFile, $temporaryName, -1, '/store/downloads/');

$path = Tools::relativePath($result);

// Try to identify
$mimeType = 'File';

try
{
  // Try to ID the file type
  $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
  $mimeType = finfo_file($fileInfo, BF_ROOT . '/' . $path);
}
catch(Exception $exception)
{
  // Non-critical failure.
}


if(!$result)
{
  $BF->admin->packAndRedirect('./?act=website&mode=downloads_add',
                                  'file', 'Could not upload the file.');
                                  
  exit();
}

// Create the download
$result = $BF->db->insert('bf_downloads', array(
                            'name' => $BF->in('f_name'),
                            'timestamp' => time(),
                            'path' => $path,
                            'size' => filesize(BF_ROOT . '/' . $path),
                            'mime_type' => $mimeType
                         ))
                 ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The download \'' . $BF->in('f_name') . '\' was created.');
  header('Location: ./?act=website&mode=downloads');
}

?>