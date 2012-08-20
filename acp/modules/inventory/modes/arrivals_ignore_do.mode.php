<?php
/**
 * Module: Inventory
 * Mode: Do Ignore Arrival
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

// Obtain the arrival ID
$arrivalID = $BF->inInteger('id');

// Remove it
$BF->db->delete('bf_stock_replenishments')
       ->where('`id` = \'{1}\'', $arrivalID)
       ->limit(1)
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Notifications will not be sent for the item.');
  header('Location: ./?act=inventory&mode=arrivals');
}

?>
