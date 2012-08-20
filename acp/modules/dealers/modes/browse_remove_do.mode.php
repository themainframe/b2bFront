<?php
/**
 * Module: Dealers
 * Mode: Do Remove Dealer
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

// Obtain the dealer ID
$dealerID = $BF->inInteger('id');
$letterCallback = $BF->in('letter');
$name = '';
$result = false;

// Valid
if($dealerID)
{
  // Get information
  $BF->db->select('*', 'bf_users')
             ->where("`id` = '{1}'", $dealerID)
             ->limit(1)
             ->execute();
             
  // Get name
  $dealerRow = $BF->db->next();
  $name = $dealerRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The dealer \'' . $name . '\' was removed.');
  }
  
  // Remove dealer
  $result = $BF->admin->api('Dealers')
                          ->remove($dealerID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The dealer \'' . $name . '\' was removed.');
  header('Location: ./?act=dealers&letter=' . $letterCallback);
}

?>