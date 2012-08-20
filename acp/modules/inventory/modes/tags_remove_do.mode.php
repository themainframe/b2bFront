<?php
/**
 * Module: Inventory
 * Mode: Do Remove Item Tag
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

// Obtain the Item Tag ID
$itemTagID = $BF->in('id');

// Valid
if($itemTagID)
{
  // Get information
  $BF->db->select('*', 'bf_item_tags')
             ->where("`id` = '{1}'", $itemTagID)
             ->limit(1)
             ->execute();
             
  // Get name
  $itemTagRow = $BF->db->next();
  $name = $itemTagRow->name;
  
  // Add restore point
  if($BF->config->get('com.b2bfront.restorepoints.auto', true))
  {
    $BF->admin->api('RestorePoints')
              ->create('The Item Tag \'' . $name . '\' was removed.');
  }
  
  // Remove Item Tag
  $result = $BF->admin->api('ItemTags')
                      ->remove($itemTagID);
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Item Tag \'' . $name . '\' was removed.');
  header('Location: ./?act=inventory&mode=tags');
}

?>