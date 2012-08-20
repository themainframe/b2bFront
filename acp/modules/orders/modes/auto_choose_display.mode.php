<?php
/**
 * Module: Orders
 * Mode: Automatically redirect to appropriate view for order.
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

// Load the order
$orderID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_orders')
           ->where('id = \'{1}\'', $orderID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=orders&mode=unprocessed');
  exit();
}

$orderRow = $BF->db->next();

// Redirect
if($orderRow->processed == 1)
{
  // Processed
  $BF->go('./?act=orders&mode=processed_view&id=' . $orderID);
}
else
{
  // Unprocessed
  $BF->go('./?act=orders&mode=unprocessed_view&id=' . $orderID);
}

exit();

?>