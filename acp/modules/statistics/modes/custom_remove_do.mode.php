<?php
/**
 * Module: Statistics
 * Mode: Do Remove Custom Statistic
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

// Get ID
$customStatisticID = $BF->inInteger('id');

// Remove statistic
$BF->db->delete('bf_statistics')
       ->where('`id` = \'{1}\'', $customStatisticID)
       ->limit(1)
       ->execute();
       
// Clean up any snapshot data
$BF->db->delete('bf_statistic_snapshot_data')
       ->where('`statistic_id` = \'{1}\'', $customStatisticID)
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The custom statistic was removed.');
  header('Location: ./?act=statistics&mode=custom');
}

?>