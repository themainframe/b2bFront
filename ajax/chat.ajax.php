<?php
/**
 * Chat System
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.2
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

// Set content type of output
header('Content-Type: text/json');

// Get my chat messages
$chat = $BF->db->query();
$chat->select('*', 'bf_chat')
     ->where('`user_id` = \'{1}\' AND (`meta` = NULL OR `meta` = \'\')', $BF->security->UID)
     ->order('timestamp', 'asc')
     ->execute();
     
// Collect chat messages
$chatMessages = array();

// Settings
$reveal = $BF->config->get('com.b2bfront.crm.reveal-staff-names', true);
$title = $BF->config->get('com.b2bfront.site.title', true);
     
while($chatMessage = $chat->next())
{
  // Reveal staff?
  $admin = $BF->db->getRow('bf_admins', $chatMessage->admin_id);
  $firstName = explode(' ', $admin->full_name);
  $firstName = $firstName[0];

  $chatMessages[] = array(
    'id' => $chatMessage->id,
    'content' => Tools::makeClickable($chatMessage->content),
    'time' => date('G:i', $chatMessage->timestamp),
    'user_id' => $chatMessage->admin_id,
    'direction' => $chatMessage->direction,
    'name' => ($reveal ? $firstName : $title . ' Staff')
  );      
}

// Build grouped array 
// (This responder is multi-functional)
$respondData = array(
  'messages' => $chatMessages,
  'requests' => array()
);  

// Output
print json_encode($respondData);

// Exit
$BF->shutdown();

?>