<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Classify Do
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
  if(!is_numeric($item))
  {
    // Not number
    unset($itemsArray[$key]);
  }
}

// Count 
$itemsCount = count($itemsArray);

// Empty set?
if($itemsCount == 0)
{
  // Return to inventory
  header('Location: ./?act=inventory');
  exit();
}

// Delete any item attributes associated with the classified items.
$BF->db->delete('bf_item_attribute_applications')
           ->where('item_id IN ({1})', Tools::CSV($itemsArray))
           ->execute();

// Start the items specified
$BF->db->update('bf_items', array(
                     'classification_id' => $BF->inInteger('f_classification') 
                   ))
           ->where('id IN ({1}) AND `parent_item_id` = -1', Tools::CSV($itemsArray))
           ->limit($itemsCount)
           ->execute();

$BF->admin->notifyMe('OK', 'The classification of the selected items has been changed.');
header('Location: ./?act=inventory');

?>