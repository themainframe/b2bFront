<?php
/**
 * Module: Dealers
 * Mode: Do Modify Dealer Profile
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
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_user_profiles')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=dealers&mode=profiles');
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
    $BF->admin->packAndRedirect('./?act=dealers&mode=profiles_modify&id=',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = false;

// Make an API call to modify the dealer profile
$result = $BF->admin->api('Dealers')
                        ->modifyProfile($ID,
                                        $BF->in('f_name'),
                                        $BF->in('f_see_rrp'),
                                        $BF->in('f_see_prices'),
                                        $BF->in('f_see_wholesale'),
                                        $BF->in('f_order'),
                                        $BF->in('f_question'),
                                        $BF->in('f_pro_rate')
                                       );

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the Dealer Profile \'' . $row->name . '\' were saved.');
  header('Location: ./?act=dealers&mode=profiles');
}

?>