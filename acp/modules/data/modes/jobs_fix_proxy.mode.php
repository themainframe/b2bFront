<?php
/**
 * Module: Data
 * Mode: Mark as Fixed and Redirect
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

// Remove the data jobs blacklist for that item
$BF->db->delete('bf_data_jobs')
       ->where('`id` = \'{1}\'', $BF->inInteger('job_id'))
       ->execute();
       
// Data Jobs attended + 1
$BF->stats->increment('com.b2bfront.stats.admins.data-jobs-attended', 1);

header('Location: ./?act=inventory&mode=browse_modify&id=' . $BF->inInteger('item_id'));
    
?>