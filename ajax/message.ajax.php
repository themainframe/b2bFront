<?php
/**
 * Message Send - Dealers
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

// Set content type of output
header('Content-Type: text/json');

// Get the user data
$messageTo = $BF->inInteger('userID');
$messageContent = strip_tags(stripslashes($BF->in('content')));

// Get my data
$myID = $BF->security->UID;

// Insert
$newMessage = $BF->db->query();
$newMessage->insert('bf_chat', array(
                'content' => Tools::makeClickable($messageContent),
                'timestamp' => time(),
                'user_id' => $myID,
                'read' => 0,
                'admin_id' => $messageTo,
                'direction' => 1,        // User -> Admin
                'meta' => $BF->in('meta')
             ))
            ->execute();

// Output message ID
print json_encode(array(
  'id' => $newMessage->insertID
));

// Finished
$BF->shutdown();

?>