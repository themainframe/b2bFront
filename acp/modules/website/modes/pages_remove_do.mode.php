<?php
/**
 * Module: Website
 * Mode: Pages Remove Do
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

// Load the page to delete
$pageID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_pages')
           ->where('id = \'{1}\'', $pageID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=website&mode=pages');
  exit();
}

$pageRow = $BF->db->next();

// Delete the page
$BF->db->delete('bf_pages')
       ->where('`id` = \'{1}\'', $pageID)
       ->limit(1)
       ->execute();

// Notify      
$BF->admin->notifyMe('OK', 'The page \'' . $pageRow->title . '\' was removed.');
header('Location: ./?act=website&mode=pages');

?>