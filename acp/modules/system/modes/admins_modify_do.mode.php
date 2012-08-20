<?php
/**
 * Module: System
 * Mode: Do Modify Staff Account
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
  header('Location: ./?act=system&mode=admins');
  exit();
}

// Load the admin to modify
$adminID = $BF->inInteger('f_id');

// Query for it
$BF->db->select('*', 'bf_admins')
           ->where('id = \'{1}\'', $adminID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=system&mode=admins');
  exit();
}

$adminRow = $BF->db->next();

// Build the validation array
$validation = array(
  
    'name' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              ),

    'email' => array(
    
               'validations' => array(
                                 'email' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_email'),
               
               'name' => 'Email'
                   
              ),
              
    'description' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_description'),
               
               'name' => 'Description'
                   
              ),
              
    'full_name' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_full_name'),
               
               'name' => 'Full Name'
                   
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
    $BF->admin->packAndRedirect('./?act=system&mode=admins_modify&id=' . $adminID,
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Staff')
                        ->modify($BF->in('f_id'),
                                 $BF->in('f_name'),
                                 $BF->in('f_password'),
                                 $BF->in('f_email'),
                                 $BF->in('f_full_name'),
                                 $BF->in('f_description'),
                                 $BF->in('f_profile'),
                                 $BF->in('f_phone_mobile'),
                                 $BF->in('f_supervisor'),
                                 array(
                                   'notification_new_order' => 
                                     $BF->inInteger('no_new_order'),
                                   'notification_note_added' => 
                                     $BF->inInteger('no_note_added'),
                                   'notification_request_for_account' =>
                                     $BF->inInteger('no_request_for_account'),
                                   'notification_new_question' => 
                                     $BF->inInteger('no_new_question'),
                                   'notification_target_met' => 
                                     $BF->inInteger('no_target_met'),
                                   'notification_target_missed' => 
                                     $BF->inInteger('no_target_missed'),
                                   'notification_system_event' => 
                                     $BF->inInteger('no_system_event'),
                                   'notification_new_data_jobs' => 
                                     $BF->inInteger('no_new_data_jobs')
                                 ));
     
if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Changes to the staff account ' . $BF->in('f_name') . ' were saved.');
  header('Location: ./?act=system&mode=admins');
}

?>