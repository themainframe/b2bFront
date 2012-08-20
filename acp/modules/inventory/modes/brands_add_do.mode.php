<?php
/**
 * Module: Inventory
 * Mode: Do Add Brand
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
                                 'unique' => array('bf_brands'),
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=brands_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

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

// Make an API call to create image
$result = $BF->admin->api('Images')
                    ->createImage($_FILES['f_image']['tmp_name'],
                                  $_FILES['f_image']['name']);
                    
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

// Passed validation
$result = $BF->admin->api('Brands')
                    ->add($BF->in('f_name'), $relativeImagePath,
                      $BF->inInteger('f_classification'));

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The brand ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=inventory&mode=brands');
}

?>