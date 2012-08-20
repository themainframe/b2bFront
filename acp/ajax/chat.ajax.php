<?php
/**
 * Message Send
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Verify Permissions
if(!$BF->admin->can('chat'))
{
  $BF->admin->notAllowed();
  exit();
}

// Update last activity
$BF->admin->setLastActivity();

// Get the user data
$messageTo = $BF->inInteger('userID');
$messageContent = strip_tags(stripslashes($BF->in('content')));

// Get my data
$myID = $BF->admin->AID;

// Insert
$newMessage = $BF->db->query();
$newMessage->insert('bf_chat', array(
                'content' => $messageContent,
                'timestamp' => time(),
                'user_id' => $messageTo,
                'admin_id' => $myID,
                'meta' => '',
                'read' => 0,
                'direction' => 0        // Admin -> User
             ))
            ->execute();

// Output message ID
print json_encode(array(
  'id' => $newMessage->insertID
));

// Finished
$BF->shutdown();

?>