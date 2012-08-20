<?php
/**
 * Module: Orders
 * Mode: Remove note from order
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

// Load the order note
$noteID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_order_notes')
           ->where('id = \'{1}\'', $noteID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=orders&mode=unprocessed');
  exit();
}

$noteRow = $BF->db->next();

// Remove note
$result = $BF->db->delete('bf_order_notes')
                 ->where('`id` = \'{1}\'', $noteRow->id)
                 ->limit(1)
                 ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The note was removed');
  header('Location: ./?act=orders&mode=auto_choose_display&id=' . $noteRow->order_id);
}

?>