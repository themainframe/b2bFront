<?php
/**
 * Module: Dealers
 * Mode: Modify
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_users')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=dealers&mode=browse');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

// Find all talks with this dealer
$orderNotes = $BF->db->select('*', 'bf_order_notes')
                     ->where('`order_id` IN (SELECT `id` FROM `bf_orders` WHERE ' . 
                             '`owner_id` = \'{1}\')', $row->id)
                     ->execute();

// Build a collection of notes
$notes = array();                   
while($note = $orderNotes->next())
{
  $notes[$note->timestamp] = array(
    'from_dealer' => !$note->author_is_staff,
    'from_staff' => $note->author_is_staff,
    'from' => $note->author_name,
    'to' => ($note->author_is_staff ? $row->description : 'Staff'),
    'message' => $note->content
  );
}

// Order by time
ksort($notes);

?>

<h1>Correspondence with <?php print $row->description; ?></h1>
<br />

<div class="panel">
  <div class="title">Correspondence Viewer</div>
  <table style="width: 100%;">
    <tbody>
      <tr>
        <td style="width: 400px;">
          <div style="width: 100%; background: #fff; height: 300px; overflow: auto; border-right: 1px solid #afafaf;">
<?php

  foreach($notes as $note)
  {
    ?>
    
    <div class="cv-message">
      <table style="width: 100%;">
        <tr>
          <td style="width: 33%;">
            <?php print $note['from']; ?>
            <?php print ($note['from_staff'] ? '&nbsp; <img src="./static/image/aui-staff.png" />' : ''); ?>
          </td>
          <td style="width: 33%; text-align: center;"><img src="./static/image/aui-right.png" /></td>
          <td style="width: 33%; text-align: right;">
            <?php print $note['to']; ?>
            <?php print ($note['from_staff'] ? '' : '&nbsp; <img src="./static/image/aui-staff.png" />'); ?>
          </td>
        </tr>
      </table>
    </div>
    
    <?php
  }

?>
          </div>
        </td>
        <td style="vertical-align: top; padding: 20px;">

        </td>
      </tr>
    </tbody>
  </table>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right when you have finished viewing correspondence.</strong>
    </p>
    <input class="submit" type="submit" style="float: right;" onclick="window.location='./?act=dealers'" value="Exit" />
    <br class="clear" />
  </div>
</div>

</form>