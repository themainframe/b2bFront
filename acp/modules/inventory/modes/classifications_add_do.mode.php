<?php
/**
 * Module: Inventory
 * Mode: Do Add Classification
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
                                 'unique' => array('bf_classifications'),
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
    $BF->admin->packAndRedirect('./?act=inventory&mode=classifications_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Passed validation
$result = $BF->admin->api('Classifications')
                    ->add($BF->in('f_name'));

// Try to add any attribute templates
if($BF->in('attributes'))
{
  // Split and create objects for them
  $attributes = explode("\n", str_replace("\r", '', $BF->in('attributes')));
  foreach($attributes as $attribute)
  {
    if(trim($attribute) == '')
    {
      continue;
    }
  
    $this->db->insert('bf_classification_attributes', array(
                       'name' => $attribute,
                       'classification_id' => $result
                     ))->execute();
  }
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The classification ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=inventory&mode=classifications');
}

?>