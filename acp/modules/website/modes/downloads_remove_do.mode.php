<?php
/**
 * Module: Website
 * Mode: Downloads Remove Do
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

// Load the download to delete
$downloadID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_downloads')
       ->where('id = \'{1}\'', $downloadID)
       ->limit(1)
       ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=website&mode=downloads');
  exit();
}

$downloadRow = $BF->db->next();

// Delete the page
$BF->db->delete('bf_downloads')
       ->where('`id` = \'{1}\'', $downloadID)
       ->limit(1)
       ->execute();

// Remove the file too
unlink(BF_ROOT . '/' . $downloadRow->path);

// Notify      
$BF->admin->notifyMe('OK', 'The download \'' . $downloadRow->name . '\' was removed.');
header('Location: ./?act=website&mode=downloads');

?>