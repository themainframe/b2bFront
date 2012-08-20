<?php
/**
 * Module: Website
 * Mode: Do Add Page
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
  
    'title' => array(
    
               'validations' => array(
                                 'unique' => array('bf_pages', 'title'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_title'),
               
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
    $BF->admin->packAndRedirect('./?act=website&mode=pages_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Create the page
$result = $BF->db->insert('bf_pages', array(
                            'title' => $BF->in('f_title'),
                            'content' => $BF->inUnfiltered('content')
                         ))
                 ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The page \'' . $BF->in('f_title') . '\' was created.');
  header('Location: ./?act=website&mode=pages');
}

?>