<?php
/**
 * Module: Website
 * Mode: Do Modify Article
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
    $BF->admin->packAndRedirect('./?act=website&mode=articles_modify&id=' . 
                                $BF->inInteger('id'), $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Update basics
$BF->db->update('bf_articles', array(
                  'name' => $BF->in('f_name'),
                  'article_category_id' => $BF->inInteger('f_article_category'),
                  'meta_content' => $BF->inUnfiltered('f_metadata'),
                  'timestamp' => time(),
                  'type' => strtoupper($BF->in('f_type'))
               ))
       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
       ->limit(1)
       ->execute();

// Determine the type
switch(strtoupper($BF->in('f_type')))
{
  case 'ART_TEXT':
  
    $content = $BF->inUnfiltered('content');
    
    $result = $BF->db->update('bf_articles', array(
                                'content' => $content
                             ))
                     ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
                     ->limit(1)
                     ->execute();
    
    break;
    
  case 'ART_IMAGE':
  
    if($_FILES['f_image']['name'] != '')
    {
  
      // Make an API call to create image
      $image = $BF->admin->api('Images')
                         ->createImage($_FILES['f_image']['tmp_name'],
                                       $_FILES['f_image']['name']);
      
      // Success/Failure? - On failure do not update image
      if(!$image)
      {
          $BF->admin->notifyMe('Error', 'Unsupported article type.', 'cross-circle.png');
          $BF->admin->packAndRedirect('./?act=website&mode=articles_add',
                                          '', (string)$validator);
  
        exit();
      }
      
      $relativeImagePath = Tools::relativePath($image['path']);
      
      $content = $relativeImagePath;
    
      $result = $BF->db->update('bf_articles', array(
                                  'content' => $content
                               ))
                       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
                       ->limit(1)
                       ->execute();
    
    }
        
    break;
    
  case 'ART_ITEM':
  
    // Success/Failure ?
    if($BF->in('f_item_id'))
    {
            
      $content = $BF->inInteger('f_item_id');
      
      $result = $BF->db->update('bf_articles', array(
                                  'content' => $content
                               ))
                       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
                       ->limit(1)
                       ->execute();
    }
  
    
    break;
  
  case 'ART_ITEM_COLLECTION':
  
    // Success/Failure ?
    if($BF->in('f_item_ids'))
    {
            
      $content = $BF->inInteger('f_item_ids');
      
      $result = $BF->db->update('bf_articles', array(
                                  'content' => $content
                               ))
                       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
                       ->limit(1)
                       ->execute();
    }
  
    
    break;
    
  case 'ART_CATEGORY':
  
    // Success/Failure ?
    if($BF->in('f_category'))
    {
            
      $content = $BF->inInteger('f_category');
      
      $result = $BF->db->update('bf_articles', array(
                                  'content' => $content
                               ))
                       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
                       ->limit(1)
                       ->execute();
    }
  
    
    break;
    
  default:
  
    // Invalid type
    $BF->admin->notifyMe('Error', 'Unsupported article type.', 'cross-circle.png');
    header('Location: ./?act=website&mode=articles');
    
    // No more rendering
    exit();
    
    break;
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the article \'' . $BF->in('f_name') . '\' were saved.');
  header('Location: ./?act=website&mode=articles');
}

?>