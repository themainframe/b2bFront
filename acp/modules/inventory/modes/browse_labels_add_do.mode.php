<?php
/**
 * Module: Inventory
 * Mode: Do Add Label
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
                                 'unique' => array('bf_item_labels', 'name'),
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=browse_labels_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->db->query();
$result->insert('bf_item_labels', array(
                  'name' => $BF->in('f_name'),
                  'colour' => $BF->in('f_colour')
               ))
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The label ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=inventory&mode=browse_labels');
}

?>