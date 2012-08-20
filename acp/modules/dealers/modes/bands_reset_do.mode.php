<?php
/**
 * Module: Dealers
 * Mode: Do Discount Band Reset
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
$discountBandID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($discountBandID)
{
  // Get information
  $BF->db->select('*', 'bf_user_bands')
             ->where("`id` = '{1}'", $discountBandID)
             ->limit(1)
             ->execute();
             
  // Get name
  $discountBandRow = $BF->db->next();
  $name = $discountBandRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The discount band \'' . $name . '\' was reset.');
  }
  
  // Remove dealer
  $result = $BF->admin->api('Dealers')
                          ->resetDiscountBand($discountBandID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Discount Band \'' . $name . '\' was reset.');
  header('Location: ./?act=dealers&mode=bands');
}

?>