<?php
/**
 * Module: Dealers
 * Mode: Bands Add Do
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
  
    'code' => array(
    
               'validations' => array(
                                 'done' => array(),
                                 'unique' => array('bf_user_bands', 'name')
                                ),
                                
               'value' => $BF->in('f_code'),
               
               'name' => 'Band Code'
                   
              ),
  
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
    $BF->admin->packAndRedirect('./?act=dealers&mode=bands_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Dealers')
                    ->addDiscountBand($BF->in('f_code'),
                                      $BF->in('f_name'));
   

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Discount Band   ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=dealers&mode=bands');
}

?>