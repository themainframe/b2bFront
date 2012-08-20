<?php
/**
 * Module: Inventory
 * Mode: Do Remove Item
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

// Obtain the Item ID
$itemID = $BF->inInteger('id');
$name = '';
$result = false;

// Valid
if($itemID)
{
  // Get information
  $BF->db->select('*', 'bf_items')
             ->where("`id` = '{1}'", $itemID)
             ->limit(1)
             ->execute();
             
  // Get SKU
  $itemRow = $BF->db->next();
  $SKU = $itemRow->sku;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The item \'' . $SKU . '\' was removed.');
  }
  
  // Remove classification
  $result = $BF->admin->api('Items')
                          ->remove($itemID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The item \'' . $SKU . '\' was removed.');
  header('Location: ./?act=inventory&mode=browse');
}

?>