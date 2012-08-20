<?php
/**
 * Module: System
 * Mode: Draft View
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

// Find the selected event
$draftID = $BF->inInteger('id');
$BF->db->select('*', 'bf_admin_drafts')
           ->where('id = \'{1}\' AND admin_id = \'{2}\'', $draftID, $BF->admin->AID)
           ->limit(1)
           ->execute();
          
// Found draft?
if($BF->db->count != 1)
{
  header('Location: ' . Tools::getModifiedURL(array('mode' => 'drafts')));
  exit();
}
          
$draft = $BF->db->next();

?>

<h1><?php print $draft->description; ?></h1>
<br />

<div class="panel">
  <div class="title">Draft Hypertext</div>
  <div class="contents" style="background: #fff">
      <?php print $draft->content; ?>  
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Draft HTML</div>
  <div class="contents" style="background: #fff">
    <code>
      <?php print htmlentities($draft->content); ?>
    </code>    
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to return to drafts.</strong>
    </p>
    <input class="submit ok" type="button" onclick="window.location='./?act=system&mode=drafts';" style="float: right;" value="OK" />
    <br class="clear" />
  </div>
</div>