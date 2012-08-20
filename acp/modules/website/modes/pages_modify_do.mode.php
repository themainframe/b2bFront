<?php
/**
 * Module: Website
 * Mode: Pages Modify Do
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

// Load the page to modify
$pageID = $BF->inInteger('f_id');

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

// Save changes to the page
$BF->db->update('bf_pages', array(
                 'content' => $BF->inUnfiltered('content')
               ))
       ->where('`id` = \'{1}\'', $pageID)
       ->limit(1)
       ->execute();

// Notify      
$BF->admin->notifyMe('OK', 'Changes to the page \'' . $pageRow->title . '\' were saved.');
header('Location: ./?act=website&mode=pages');

?>