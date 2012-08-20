<?php
/**
 * Module: System
 * Mode: Staff Profiles Add Do
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

//
// Permissions:
// Need to be supervisor.
//
if(!$BF->admin->isSupervisor)
{
  header('Location: ./?act=system&mode=profiles');
  exit();
}

// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => array(
                                 'unique' => array('bf_admin_profiles'),
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
    $BF->admin->packAndRedirect('./?act=system&mode=profiles_add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Staff')
                        ->addProfile($BF->in('f_name'),
                                     $BF->in('f_account'),
                                     $BF->in('f_categories'),
                                     $BF->in('f_items'),
                                     $BF->in('f_orders'),
                                     $BF->in('f_website'),
                                     $BF->in('f_system'),
                                     $BF->in('f_login'),
                                     $BF->in('f_stats'),
                                     $BF->in('f_chat'),
                                     $BF->in('f_data')
                                    );
   

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Staff Profile' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=system&mode=profiles');
}

?>