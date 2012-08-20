<?php
/**
 * Module: Inventory
 * Mode: Do Remove Label
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

// Find the label
$label = $BF->db->getRow('bf_item_labels', $BF->inInteger('id'));

// Remove applications of the label
$labelApplicationsRemoval = $BF->db->query();
$labelApplicationsRemoval->delete('bf_item_label_applications')
                         ->where('`item_label_id` = \'{1}\'', $BF->inInteger('id'))
                         ->limit(1)
                         ->execute();

// Remove it
$result = $BF->db->query();
$result->delete('bf_item_labels')
       ->where('`id` = \'{1}\'', $BF->inInteger('id'))
       ->limit(1)
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The label \'' . $label->name . '\' was removed.');
  header('Location: ./?act=inventory&mode=browse_labels');
}

?>
