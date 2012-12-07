<?php
/**
 * Module: System
 * Mode: Configuration maintenance exit
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

// Update
$BF->db->update('bf_config', array(
                     'value' => 0
                   ))
           ->where('`name` = \'{1}\'', 'com.b2bfront.site.maintenance')
           ->limit(1)
           ->execute();

// Sync config hive
$BF->config->sync();

// Generate a notification
$BF->admin->notifyMe('OK', 'Maintenance mode disabled.');

$BF->go('./?');

?>