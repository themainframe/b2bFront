<?php
/**
 * Module: Website
 * Mode: Do Add Article
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
  
    'name'  => array(
     
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
    $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Determine the type
switch(strtoupper($BF->in('f_type')))
{
  case 'ART_TEXT':
  
    $content = $BF->inUnfiltered('content');
    
    break;
    
  case 'ART_IMAGE':
  
    // Make an API call to create image
    $image = $BF->admin->api('Images')
                       ->createImage($_FILES['f_image']['tmp_name'],
                                     $_FILES['f_image']['name']);
    
    // Success/Failure ?
    if(!$image)
    {
      $BF->admin->notifyMe('Error', 'Unsupported article type.', 'cross-circle.png');
      $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                      '', (string)$validator);

      exit();
    }
    
    $relativeImagePath = Tools::relativePath($image['path']);
    
    $content = $relativeImagePath;
    
    break;
    
  case 'ART_ITEM':
  
    // Success/Failure ?
    if(!$BF->in('f_item_id'))
    {
      $BF->admin->notifyMe('Error', 'Please select an item.', 'cross-circle.png');
      $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                      '', (string)$validator);

      exit();
    }
  
    $content = $BF->inInteger('f_item_id');
    
    break;
  
  case 'ART_ITEM_COLLECTION':
  
    // Success/Failure ?
    if(!$BF->in('f_item_ids'))
    {
      $BF->admin->notifyMe('Error', 'Please select one or more items.', 'cross-circle.png');
      $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                      '', (string)$validator);

      exit();
    }
  
    $content = $BF->inInteger('f_item_ids');
    
    break;
    
  case 'ART_CATEGORY':
  
    // Success/Failure ?
    if(!$BF->in('f_category'))
    {
      $BF->admin->notifyMe('Error', 'Please select a category.', 'cross-circle.png');
      $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                      '', (string)$validator);

      exit();
    }
  
    $content = $BF->inInteger('f_category');
    
    break;
    
  default:
  
    // Invalid type
    $BF->admin->notifyMe('Error', 'Unsupported article type.', 'cross-circle.png');
    header('Location: ./?act=website&mode=articles');
    
    // No more rendering
    exit();
    
    break;
}

// Create the page
$result = $BF->db->insert('bf_articles', array(
                            'name' => $BF->in('f_name'),
                            'content' => $content,
                            'article_category_id' => $BF->inInteger('f_article_category'),
                            'meta_content' => $BF->inUnfiltered('f_metadata'),
                            'timestamp' => time(),
                            'type' => strtoupper($BF->in('f_type')),
                            'expiry_timestamp' => 2147483647   // Max
                         ))
                 ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The article \'' . $BF->in('f_name') . '\' was created.');
  header('Location: ./?act=website&mode=articles');
}

?>