<?php
/**
 * Module: Inventory
 * Mode: Do Remove Outlet
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

// Obtain the outlet ID
$outletID = $BF->inInteger('id');
$result = false;

// Valid
if($outletID)
{
  // Get information
  $BF->db->select('*', 'bf_outlets')
             ->where("`id` = '{1}'", $outletID)
             ->limit(1)
             ->execute();
             
  // Get name
  $outletRow = $BF->db->next();
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('An outlet was removed.');
  }
  
  // Remove outlet
  $result = $BF->admin->api('Outlets')
                          ->remove($outletID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The outlet was removed.');
  header('Location: ./?act=inventory&mode=outlets');
}

?>