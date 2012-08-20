<?php
/**
 * Module: Inventory
 * Mode: Do Modify Classification
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
$BF->db->select('*', 'bf_classifications')
       ->where('id = \'{1}\'', $ID)
       ->limit(1)
       ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=inventory&mode=classifications');
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=classifications_modify&id=' . $BF->inInteger('f_id'),
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Modify the classification name
$BF->db->update('bf_classifications', array(
                  'name' => $BF->in('f_name')
               ))
       ->where('`id` = \'{1}\'', $BF->inInteger('f_id'))
       ->limit(1)
       ->execute();
       
// Modify the classification attributes if required


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the classification \'' . $BF->in('f_name') . '\' were saved.');
  header('Location: ./?act=inventory&mode=classifications');
}

?>