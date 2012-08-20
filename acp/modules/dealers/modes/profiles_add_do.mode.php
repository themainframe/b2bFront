<?php
/**
 * Module: Dealers
 * Mode: Profiles Add Do
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
                                 'unique' => array('bf_user_profiles'),
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
    $BF->admin->packAndRedirect('./?act=dealers&mode=profiles_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Dealers')
                        ->addProfile($BF->in('f_name'),
                                     $BF->in('f_see_rrp'),
                                     $BF->in('f_see_prices'),
                                     $BF->in('f_see_wholesale'),
                                     $BF->in('f_order'),
                                     $BF->in('f_question'),
                                     $BF->in('f_pro_rate')
                                    );
   

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Dealer Profile \'' . $BF->in('f_name') . '\' was created.');
  header('Location: ./?act=dealers&mode=profiles');
}

?>