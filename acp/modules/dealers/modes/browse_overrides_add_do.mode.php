<?php
/**
 * Module: Dealers
 * Mode: Do Add Pricing Overrides
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

// Obtain the Item IDs
$itemIDs = $BF->in('f_ids');
$itemIDs = Tools::unCSV($itemIDs);

// Keep track of successes
$count = 0;

// Fail silently
if(is_array($itemIDs))
{
  foreach($itemIDs as $itemID)
  {
    if(is_numeric($itemID))
    {
      // Save a Pricing Override at the current item value
      $item = $BF->db->getRow('bf_items', $itemID);
      
      if($item)
      { 
        // Create the override
        $BF->db->insert('bf_user_prices', array(
                   'user_id' => $BF->inInteger('f_uid'),
                   'item_id' => intval($itemID),
                   'trade_price' => $item->trade_price,
                   'pro_net_price' => $item->pro_net_price
                 ))
                ->execute();
        
        // Remove the non-present cache flag            
        $cacheKey = 'override-' . $BF->inInteger('f_uid') . '-' . 
          intval($itemID);
        $BF->cache->removeValue($cacheKey);        
            
        $count ++;
      }
    }
  }
}


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Pricing Override' . Tools::plural($count) . 
    ' ' . ($count != 1 ? 'were' : 'was') . ' added.');
  header('Location: ./?act=dealers&mode=browse_overrides&id=' . $BF->inInteger('f_uid'));
}

?>