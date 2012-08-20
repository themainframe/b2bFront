<?php
/**
 * Module: Orders
 * Mode: Do Attach Note to Unprocessed Order
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
if($BF->db->count != 1 || trim($BF->in('f_content')) == '')
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
$result = $BF->db->insert('bf_order_notes', array(
                     'author_name' => $BF->admin->getInfo('full_name'),
                     'content' => $BF->in('f_content'),
                     'timestamp' => time(),
                     'staff_only' => $BF->inInteger('f_staff_only'),
                     'author_is_staff' => 1,
                     'order_id' => $orderID
                   ))
           ->execute();
            


if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The note was attached to order ' . $fullOrderID);
  header('Location: ./?act=orders&mode=auto_choose_display&id=' . $orderID);
}

?>