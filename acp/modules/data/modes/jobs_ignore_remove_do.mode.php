<?php
/**
 * Module: Data
 * Mode: Unblacklist Item for Data Jobs
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

// Remove the data jobs blacklist for that item
$BF->db->delete('bf_data_jobs_ignore')
       ->where('`item_id` = \'{1}\'', $itemRow->id)
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'Data Jobs will now be generated for ' . $itemRow->sku . '.');
  header('Location: ./?act=data&mode=jobs_ignore');
}
    
?>