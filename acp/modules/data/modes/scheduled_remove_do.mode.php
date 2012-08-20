<?php
/**
 * Module: Data
 * Mode: Scheduled Imports Remove Do
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

// Obtain the schedule ID
$scheduleID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($scheduleID)
{
  // Get information
  $BF->db->select('*', 'bf_scheduled_imports')
             ->where("`id` = '{1}'", $scheduleID)
             ->limit(1)
             ->execute();
             
  // Get name
  $scheduleRow = $BF->db->next();
  $name = $scheduleRow->name;
  
  // Remove import
  $result = $BF->admin->api('Data')
                          ->unSchedule($scheduleID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Scheduled Import \'' . $name . '\' was removed.');
  header('Location: ./?act=data&mode=scheduled');
}

?>