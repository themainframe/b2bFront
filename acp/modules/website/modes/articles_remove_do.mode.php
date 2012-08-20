<?php
/**
 * Module: Website
 * Mode: Articles Remove Do
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

// Load the article to delete
$articleID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_articles')
           ->where('id = \'{1}\'', $articleID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=website&mode=articles');
  exit();
}

$articleRow = $BF->db->next();

// Delete the article
$BF->db->delete('bf_articles')
       ->where('`id` = \'{1}\'', $articleID)
       ->limit(1)
       ->execute();

// Notify      
$BF->admin->notifyMe('OK', 'The article \'' . $articleRow->name . '\' was removed.');
header('Location: ./?act=website&mode=articles');

?>