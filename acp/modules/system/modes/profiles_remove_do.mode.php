<?php
/**
 * Module: System
 * Mode: Do Remove Staff Profile
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

// Obtain the staff profile ID
$staffProfileID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($staffProfileID)
{
  // Get information
  $BF->db->select('*', 'bf_admin_profiles')
             ->where("`id` = '{1}'", $staffProfileID)
             ->limit(1)
             ->execute();
             
  // Get name
  $staffProfileRow = $BF->db->next();
  $name = $staffProfileRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The staff profile \'' . $name . '\' was removed.');
  }
  
  // Remove staff profile
  $result = $BF->admin->api('Staff')
                          ->removeProfile($staffProfileID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Staff Profile \'' . $name . '\' was removed.');
  header('Location: ./?act=system&mode=profiles');
}

?>