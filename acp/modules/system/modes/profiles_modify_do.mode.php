<?php
/**
 * Module: Dealers
 * Mode: Do Modify Staff Profile
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_admin_profiles')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=system&mode=profiles');
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
    $BF->admin->packAndRedirect('./?act=system&mode=profiles_modify&id=',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = false;

// Make an API call to modify the staff profile
$result = $BF->admin->api('Staff')
                        ->modifyProfile($ID,
                                        $BF->in('f_name'),
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
  $BF->admin->notifyMe('OK', 'Changes to the Staff Profile \'' . $row->name . '\' were saved.');
  header('Location: ./?act=system&mode=profiles');
}

?>