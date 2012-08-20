<?php
/**
 * Module: Logout
 * Mode: Do Log Out
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

// Tell everyone I am logging out
$BF->admin->sendBulkNotification($BF->admin->getInfo('full_name'),
                                'just logged out of the ACP.', true, 'status-offline.png');

// Log Out
$BF->admin->logOut();

// Redirect
$BF->go('./?');

?>