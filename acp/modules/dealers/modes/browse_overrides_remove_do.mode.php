<?php
/**
 * Module: Dealers
 * Mode: Do Remove Pricing Override
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

// Obtain the Pricing Override ID
$overrideID = $BF->inInteger('id');

// Valid
if($overrideID)
{
  // Get the override
  $overrideRow = $BF->db->getRow('bf_user_prices', $overrideID);

  // Remove Override:
  $BF->db->delete('bf_user_prices')
         ->where('`id` = \'{1}\'', $overrideID)
         ->limit(1)
         ->execute();

  // Un-cache
  $cacheKey = 'override-' . $BF->inInteger('uid') . '-' . 
        $overrideRow->item_id;
  $BF->cache->removeValue($cacheKey);
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Pricing Override was removed.');
  header('Location: ./?act=dealers&mode=browse_overrides&id=' . $BF->inInteger('uid'));
}

?>