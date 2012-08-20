<?php
/**
 * Module: Inventory
 * Mode: Requests Ignore Do
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

// Ignore item
$itemID = $BF->inInteger('id');

// Delete requests
$deleteRequests = $BF->db->query();
$deleteRequests->delete('bf_user_stock_notifications')
               ->where('`item_id` = \'{1}\'', $itemID)
               ->execute();
               
// Done
$BF->admin->notifyMe('OK', 'The request' . Tools::plural($deleteRequests->affected) . 
  ' ' . ($deleteRequests->affected > 1 ? 'have' : 'has') . ' been cancelled.');
header('Location: ./?act=inventory&mode=requests');

?>