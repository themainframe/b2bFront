<?php
/**
 * Module: Inventory
 * Mode: Do Add Classification Attribute
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=classifications_modify_attributes_add&id=' . $BF->inInteger('f_id'),
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Passed validation
$result = $BF->admin->api('Classifications')
                    ->addAttribute($BF->in('f_name'), $BF->inInteger('f_id'));

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The classification attribute \'' . $BF->in('f_name') . '\' was created.');
  header('Location: ./?act=inventory&mode=classifications_modify_attributes&id=' . $BF->inInteger('f_id'));
}

?>