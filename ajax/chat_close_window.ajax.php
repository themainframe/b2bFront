<?php
/**
 * Chat System - Close Window
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_INDEX', true);

// Relative path for this - no BF_ROOT yet.
require_once('../startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Verify that I am logged in
if(!$BF->security->loggedIn())
{
  // Not authenticated
  $BF->shutdown();
  exit();
}


// Delete all my messages
$BF->db->delete('bf_chat')
       ->where('`user_id` = \'{1}\'', $BF->security->UID)
       ->execute();
       
// Add a meta to notify ACP that the window has closed.
$BF->db->insert('bf_chat', array(
           'content' => 'closed',
           'meta' => 'closed',
           'timestamp' => time(),
           'read' => 0,
           'direction' => 1,
           'admin_id' => $BF->inInteger('user_id'),
           'user_id' => $BF->security->UID
         ))
       ->execute();

// Nothing to report
print json_encode(array(
  'id' => -1
));

// Finished
$BF->shutdown();

?>