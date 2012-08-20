<?php
/**
 * Module: Inventory
 * Mode: Do Modify Item Tag
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

// Get the ID
$ID = $BF->inInteger('f_id');

// Get the row information
$BF->db->select('*', 'bf_item_tags')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=tags');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=organise_modify&id=',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Make changes to the item tag
// Get formatting options
$formattingBold = $BF->in('f_list_bold');
$formattingItalic = $BF->in('f_list_italic');
$formattingSmallCaps = $BF->in('f_list_small_caps');
$formattingColour = $BF->in('f_list_colour');

// Modify item tag
$result = $BF->admin->api('ItemTags')
                        ->modify(
                                  $ID,                                  
                                  $BF->in('f_name'), 
                                  $BF->in('f_icon'), 
                                  $formattingBold, 
                                  $formattingItalic, 
                                  $formattingSmallCaps,
                                  $formattingColour,
                                  $BF->inInteger('f_masthead') == 1
                                 );

// Masthead Image
if($_FILES['f_image']['name'] != '')
{
  // Update image too
  // Ask the API if it supports this type of image
  $supportsName = $BF->admin->api('Images') 
                            ->supportsFile($_FILES['f_image']['name']);
  
  // Check for supported path
  if(!$supportsName)
  {
    $BF->admin->packAndRedirect('./?act=inventory&mode=tags_modify&id=' . $BF->inInteger('id'),
                                    'image', 'The file must either .jpg, .gif or .png');
                                    
    exit();
  }
  
  // Make an API call to move the image
  $result = $BF->admin->api('Images')
                      ->createImage($_FILES['f_image']['tmp_name'],
                                    $_FILES['f_image']['name'], $SKU);
                      
  // Failure (No image upload took place)?
  if(!$result)
  {
    $BF->admin->packAndRedirect('./?act=inventory&mode=tags_modify&id=' . $BF->inInteger('id'),
                                'image', 'Please check the image file.');
                                    
    exit();
  }
  
  // Calculate proportions
  $width = intval($result['width']);
  $height = intval($result['height']);
  $proportion = 1.0;
  
  if($width > $height)
  {
    $proportion = $width / $height;
  }
  else
  {
    $proportion = $height / $width;
  }

  
  // Check the size of the image, it may need to be resized
  $maxSize = $BF->config->get('com.b2bfront.site.max-category-image-size', true);
  if($width > $maxSize || $height > $maxSize)
  {
    // Resize
    $resizeResult = $BF->admin->api('Images') 
                              ->resizeReplace($result['path'], $maxSize);
                                  
    if(!$resizeResult)
    {
      $BF->admin->packAndRedirect('./?act=inventory&mode=tags_modify&id=' . $BF->inInteger('id'),
                                      'image', 'The image could not be resized.');
                                      
      exit();
    }
  }
  
  // Get the relative image path
  $relativeImagePath = Tools::relativePath($result['path']);
  
  // Update the image
  $BF->db->update('bf_item_tags', array(
                    'masthead_image_path' => $relativeImagePath
                 ))
         ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
         ->limit(1)
         ->execute();
  
  
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the Item Tag \'' . $row->name . '\' were saved.');
  header('Location: ./?act=inventory&mode=tags');
}

?>