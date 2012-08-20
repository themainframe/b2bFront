<?php
/**
 * Module: Dashboard
 * Mode: Remove Notification Do
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

// Remove the specified notification
$BF->db->delete('bf_admin_notifications')
       ->where('`id` = \'{1}\' AND `admin_id` = \'{2}\'',
          $BF->inInteger('id'), $BF->admin->AID)
       ->limit(1)
       ->execute();
               
// Redirect
$BF->go(($BF->in('from_list') ? './?act=dashboard&mode=notifications' : './?'));

?>