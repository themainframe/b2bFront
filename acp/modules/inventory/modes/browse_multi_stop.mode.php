<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Stop Items
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

// Count all items
$items = $BF->in('dv_inventory');
if(!$items)
{
  // Return to inventory
  header('Location: ./?act=inventory');
  exit();
}

// Split
$itemsArray = explode(',', $items);

// Get Checkboxes
foreach($itemsArray as $key => $item)
{
  if($BF->inInteger('inventory_' . $item) != 1 || !is_numeric($item))
  {
    // Not Selected or not number
    unset($itemsArray[$key]);
  }
}

// Count 
$itemsCount = count($itemsArray);

// Empty set?
if($itemsCount == 0)
{
  // Return to inventory
  $BF->admin->notifyMe('Instruction', 'Select one or more items first.', 'property.png');
  header('Location: ./?act=inventory');
  exit();
}

// Stop the items specified
$BF->db->update('bf_items', array(
                     'visible' => 0 
                   ))
           ->where('id IN ({1})', Tools::CSV($itemsArray))
           ->limit($itemsCount)
           ->execute();
           
// Uncache all
foreach($itemsArray as $item)
{
  // Uncache the row
  $BF->cache->removeRow('bf_items', $item);
}

$BF->admin->notifyMe('OK', 'The selected items have been stopped.');
header('Location: ./?act=inventory');

?>