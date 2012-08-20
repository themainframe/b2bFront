<?php
/**
 * Module: Inventory
 * Mode: Do Modify Brand
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=brands_modify&id=' . $BF->inInteger('id'),
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Update the name and primary classification
$BF->db->update('bf_brands', array(
                  'name' => $BF->in('f_name'),
                  'primary_classification_id' => $BF->inInteger('f_classification')
               ))
       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
       ->limit(1)
       ->execute();

if($_FILES['f_image']['name'] != '')
{
  // Update image too
  // Ask the API if it supports this type of image
  $supportsName = $BF->admin->api('Images') 
                            ->supportsFile($_FILES['f_image']['name']);
  
  // Check for supported path
  if(!$supportsName)
  {
    $BF->admin->packAndRedirect('./?act=inventory&mode=brands_add',
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=brands_add',
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
  
  // More than the acceptable margin?
  $acceptedMargin = $BF->config->get('com.b2bfront.brands.max-logo-proportion', true);
  $checkBrandImageSize = $BF->config->get('com.b2bfront.brands.constrain-logo-proportions', true);
  
  // Failure (Bad image proportions)?
  if($checkBrandImageSize && ($proportion * 100 < 100 - $acceptedMargin 
    || $proportion * 100 > 100 + $acceptedMargin))
  {
    $BF->admin->packAndRedirect('./?act=inventory&mode=brands_add',
                                    'image', 'The image needs to be more square.');
                                    
    exit();
  }
  
  // Check the size of the image, it may need to be resized
  $maxSize = $BF->config->get('com.b2bfront.brands.max-logo-size', true);
  if($width > $maxSize || $height > $maxSize)
  {
    // Resize
    $resizeResult = $BF->admin->api('Images') 
                              ->resizeReplace($result['path'], $maxSize);
                                  
    if(!$resizeResult)
    {
      $BF->admin->packAndRedirect('./?act=inventory&mode=brands_add',
                                      'image', 'The image could not be resized.');
                                      
      exit();
    }
  }
  
  // Get the relative image path
  $relativeImagePath = Tools::relativePath($result['path']);
  
  // Update the image
  $BF->db->update('bf_brands', array(
                    'image_path' => $relativeImagePath
                 ))
         ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
         ->limit(1)
         ->execute();
  
  
}

$BF->admin->notifyMe('OK', 'Changes to the brand ' . $BF->in('f_name') . ' were saved.');
header('Location: ./?act=inventory&mode=brands');

?>