<?php
/**
 * Module: Orders
 * Mode: Unprocess Processed Order
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
$orderID = $orderRow->id;
$fullOrderID = 
  $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . $orderID;

// Add the order note
$result = $BF->db->update('bf_orders', array(
                     'processed' => 0,
                     'processed_timestamp' => 0,
                     'held' => 0
                   ))
                  ->where('`id` = \'{1}\'', $orderID)
                  ->limit(1)
                  ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Order ' . $fullOrderID . ' has been unmarked as processed.');
  header('Location: ./?act=orders&mode=processed');
}

?>