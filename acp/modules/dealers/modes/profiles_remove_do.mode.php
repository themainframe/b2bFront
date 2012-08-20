<?php
/**
 * Module: Dealers
 * Mode: Do Remove Dealer Profile
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

// Obtain the dealer profile ID
$dealerProfileID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($dealerProfileID)
{
  // Get information
  $BF->db->select('*', 'bf_user_profiles')
             ->where("`id` = '{1}'", $dealerProfileID)
             ->limit(1)
             ->execute();
             
  // Get name
  $dealerProfileRow = $BF->db->next();
  $name = $dealerProfileRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The dealer profile \'' . $name . '\' was removed.');
  }
  
  // Remove dealer profile
  $result = $BF->admin->api('Dealers')
                          ->removeProfile($dealerProfileID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Dealer Profile \'' . $name . '\' was removed.');
  header('Location: ./?act=dealers&mode=profiles');
}

?>