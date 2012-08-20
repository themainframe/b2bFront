<?php
/**
 * Module: Inventory
 * Mode: Browse Filters Remove Do
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

// Obtain the filter ID
$filterID = $BF->inInteger('id');

// Valid
if($filterID)
{
  // Remove filter
  $BF->db->delete('bf_admin_inventory_browse_filters')
             ->where('id = \'{1}\'', $filterID)
             ->limit(1)
             ->execute();
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The filter was removed.');
  header('Location: ./?act=inventory&mode=browse_modify_filters');
}

?>
