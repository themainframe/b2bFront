<?php
/**
 * Module: System
 * Mode: Event View
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
$eventID = $BF->inInteger('event');
$BF->db->select('*', 'bf_events')
           ->where('id = \'{1}\'', $eventID)
           ->limit(1)
           ->execute();
          
// Found event?
if($BF->db->count != 1)
{
  header('Location: ' . Tools::getModifiedURL(array('mode' => 'events')));
  exit();
}

          
$event = $BF->db->next();

// Requires "seeing" ?
if($event->attention_required)
{
  // Update event
  $BF->db->update('bf_events', array(
                       'attention_required' => 0
                     ))
             ->where('id = \'{1}\'', $event->id)
             ->limit(1)
             ->execute();
}

?>

<h1>Review Event</h1>
<br />

<div class="panel">
  <div class="title">Event Information</div>
  <div class="contents">
    <p style="font-family:monospace;">
      <strong><?php print $event->title; ?></strong>
      <br /><br />
      <?php print Tools::longDate($event->timestamp); ?>
      <br /><br />
      <?php print $event->contents; ?>
      <?php if($event->level > 1) print "<br />Transcend support desk was informed."; ?>
    </p>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to return to the Event Log.</strong>
    </p>
    <input class="submit ok" type="button" onclick="window.location='./?act=system&mode=events';" style="float: right;" value="OK" />
    <br class="clear" />
  </div>
</div>