<?php
/**
 * Module: Data
 * Mode: Blacklist Item for Data Jobs
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

// Get the item
$itemRow = $BF->db->getRow('bf_items', $BF->inInteger('id'));

// Remove the data jobs for that item
$BF->db->delete('bf_data_jobs')
       ->where('`item_id` = \'{1}\'', $itemRow->id)
       ->execute();
       
// Add to the blacklist
$BF->db->insert('bf_data_jobs_ignore', array(
                 'item_id' => $itemRow->id
               ))
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'No more Data Jobs will be generated for ' . $itemRow->sku . '.');
  header('Location: ./?act=data&mode=jobs');
}
    
?>