<?php
/**
 * Module: System
 * Mode: Do Remove Staff Account
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

// Obtain the staff ID
$staffAccountID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($staffAccountID)
{
  // Get information
  $BF->db->select('*', 'bf_admins')
             ->where("`id` = '{1}'", $staffAccountID)
             ->limit(1)
             ->execute();
             
  // Get name
  $staffAccountRow = $BF->db->next();
  $name = $staffAccountRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The staff account \'' . $name . '\' was removed.');
  }
  
  // Remove dealer
  $result = $BF->admin->api('Staff')
                      ->remove($staffAccountID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The staff account \'' . $name . '\' was removed.');
  header('Location: ./?act=system&mode=admins');
}

?>