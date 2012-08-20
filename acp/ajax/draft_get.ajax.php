<?php
/**
 * Drafts Getter
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

// Get the draft description first
$draftDescription = $BF->in('description');

// Try to find a draft
$BF->db->select('*', 'bf_admin_drafts')
           ->where('admin_id = {1} AND description = \'{2}\'',
                   $BF->admin->AID, $draftDescription)
           ->limit(1)
           ->execute();
        
if($BF->db->count == 1)
{
  // Output the text
  $draft = $BF->db->next();
  print $draft->content;
}

?>