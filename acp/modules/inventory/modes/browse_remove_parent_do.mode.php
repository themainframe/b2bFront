<?php
/**
 * Module: Inventory
 * Mode: Do Remove Parent Item
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

// Obtain the Parent Item ID
$parentItemID = $BF->inInteger('id');
$result = false;

if($parentItemID)
{
  // Get information
  $BF->db->select('*', 'bf_parent_items')
             ->where("`id` = '{1}'", $parentItemID)
             ->limit(1)
             ->execute();
             
  // Get Virtual SKU
  $parentItemRow = $BF->db->next();
  $SKU = $parentItemRow->sku;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
                  ->create('The parent item \'' . $SKU . '\' was removed.');
  }
  
  // Remove parent
  $result = $BF->admin->api('Items')
                      ->removeParent($parentItemID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The parent item \'' . $SKU . '\' was removed.');
  header('Location: ./?act=inventory&mode=browse');
}

?>