<?php
/**
 * Drafts
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

// Save a draft to disk
// Get the draft text and description first
$draftDescription = $BF->in('description');
$draftText = stripslashes($BF->inUnfiltered('text'));

// Try to find a draft
$BF->db->select('*', 'bf_admin_drafts')
           ->where('admin_id = {1} AND description = \'{2}\'',
                   $BF->admin->AID, $draftDescription)
           ->limit(1)
           ->execute();
        
if($BF->db->count == 1)
{
  // Update the draft file and time
  $BF->db->update('bf_admin_drafts', array(
                 'content' => $draftText,
                 'timestamp' => time()
               ))
             ->where('admin_id = {1} AND description = \'{2}\'',
                     $BF->admin->AID, $draftDescription)
             ->limit(1)
             ->execute();
}
else
{
  // Create a new draft
  $BF->db->insert('bf_admin_drafts', array(
                 'description' => $draftDescription,
                 'content' => $draftText,
                 'timestamp' => time(),
                 'admin_id' => $BF->admin->AID
               ))
             ->execute();
}

?>