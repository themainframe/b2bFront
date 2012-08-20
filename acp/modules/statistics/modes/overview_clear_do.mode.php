<?php
/**
 * Module: Statistics
 * Mode: Do Clear Statistics History
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

// Remove all snapshots
$BF->db->delete('bf_statistic_snapshots')
       ->execute();
       
// Remove all snapshot data
$BF->db->delete('bf_statistic_snapshot_data')
       ->execute();

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'All statistical history has been cleared.');
  header('Location: ./?act=statistics&mode=overview');
}

?>