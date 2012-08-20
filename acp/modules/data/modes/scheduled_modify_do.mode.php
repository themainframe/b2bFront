<?php
/**
 * Module: Data
 * Mode: Scheduled Modify Do
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

// Obtain time
$time = str_pad($BF->in('f_schedule_time_hours'), 2, '0', STR_PAD_LEFT) . ':' . 
        str_pad($BF->in('f_schedule_time'), 2, '0', STR_PAD_LEFT);

// Build the validation array
$validation = array(
              
    'schedule_date' => array(
    
                   'validations' => array(
                                     'done' => array(),
                                     'futureDate' => array($time)
                                    ),
                                    
                   'value' => $BF->in('f_schedule_date'),
                   
                   'name' => 'Schedule Date and Time'
                       
                  )

);

// Get the ID
$scheduleID = $BF->inInteger('f_schedule_id');

// Check each field
foreach($validation as $fieldName => $fieldData)
{
  // Create a validator
  $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);

  // Check
  if($validator->batch($fieldData['validations'])->failed())
  {
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=data&mode=scheduled_modify&id=' . $scheduleID,
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Get the time value
$timestamp = strtotime($BF->in('f_schedule_date') . ' ' . $time);

// Get any notifications
$notifySMS = $BF->in('f_schedule_notify_sms');
$notifyEmail = $BF->in('f_schedule_notify_email');

// Get new item create request value
$createNewSKUs = $BF->in('f_new_skus');

// Create the schedule
$result = $BF->admin->api('Data')
                        ->updateSchedule($scheduleID,
                                         $timestamp,
                                         $createNewSKUs,
                                         $notifySMS,
                                         $notifyEmail);

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Scheduled Import was modified.');
  header('Location: ./?act=data&mode=scheduled');
}
    
?>