<?php
/**
 * Module: Dashboard
 * Mode: Remove Download Do
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined('BF_CONTEXT_ADMIN') || !defined('BF_CONTEXT_MODULE'))
{
  exit();
}

// Get the download
$downloadRow = $BF->db->getRow('bf_admin_downloads', $BF->inInteger('id'));

// Remove file
unlink(BF_ROOT . '/' . $downloadRow->path);

// Remove the specified download
$BF->db->delete('bf_admin_downloads')
       ->where('`id` = \'{1}\' AND `admin_id` = \'{2}\'',
          $BF->inInteger('id'), $BF->admin->AID)
       ->limit(1)
       ->execute();
               
// Notify
$BF->admin->notifyMe('OK', 'The download has been deleted.');
               
// Redirect
$BF->go('./?act=dashboard&mode=downloads');

?>