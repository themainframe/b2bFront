<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Tag Do
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

// Iterate over tags
$tags = $BF->db->query();
$tags->select('*', 'bf_item_tags')
     ->execute();
     
// Also grab items as an enumeration
$items = $BF->db->query();
$items->select('*', 'bf_items')
      ->where('id IN ({1})', Tools::CSV($itemsArray))
      ->execute();
     
while($tag = $tags->next())
{
  // Check the action of this tag
  if(!$BF->in('f_tag_' . $tag->id))
  {
    continue;
  }
  
  switch($BF->in('f_tag_' . $tag->id))
  {
    case 'leave':
    
      // Do nothing with this tag
      
      break;
      
    case 'add':
    
      // Add this tag to each item in the set
      
      while($item = $items->next())
      {
        try
        {
          $BF->db->insert('bf_item_tag_applications', array(
                               'item_id' => $item->id,
                               'item_tag_id' => $tag->id
                             ))
                     ->execute();
        }
        catch(Exception $exception)
        {
          // Duplicate "add" -> No error.
        }
      }
      
      // Rewind items
      $items->rewind();
              
      break;
      
    case 'remove':
    
      // Remove this item tag from all the selected items
      
      $BF->db->delete('bf_item_tag_applications')
                 ->where('item_tag_id = {1} AND item_id IN({2})', $tag->id, Tools::CSV($itemsArray))
                 ->execute();

      // Rewind items
      $items->rewind();
    
      break;
  }
}
     

     
$BF->admin->notifyMe('OK', 'The item tags of the selected items have been updated.');
header('Location: ./?act=inventory');

?>