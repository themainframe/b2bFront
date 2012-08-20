<?php
/**
 * Module: Orders
 * Mode: Unprocessed
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

// Load the order to view
$orderID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_orders')
           ->where('id = \'{1}\'', $orderID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=orders&mode=unprocessed');
  exit();
}

$orderRow = $BF->db->next();
$orderID = $orderRow->id;

?>

<script type="text/javascript">
  
  $(function() {
  
    // Prevent telling the dealer about hidden notes in UI
    $('#f_staff_only').click(function(){
      if($('#f_staff_only').is(":checked"))
      {
        $('#notifyDealer').hide();
        $('#f_tell_dealer').removeAttr("checked");
      }
      else
      {
        $('#notifyDealer').show();
      }
    });
    
  });
  
</script>

<h1>
  Add a Note to Order 
  <?php print $BF->config->get('com.b2bfront.ordering.order-id-prefix', true) . $orderRow->id; ?>
</h1>
<br />

<form action="./?act=orders&mode=unprocessed_add_note_do&id=<?php print $orderID; ?>" method="post">

<div class="panel" style="min-height: 115px; background: #fff;">
  <div class="title">Attach a Note</div>
  <div class="contents" style="padding: 0; background: #fff;">
    
<?php
  
  // Find all notes
  $notes = $BF->db->query();
  $notes->select('*', 'bf_order_notes')
        ->where('`order_id` = \'{1}\'', $orderID)
        ->order('timestamp', 'asc')
        ->execute();
        
  // No notes?
  if($notes->count == 0)
  {
    print '<br /><br /><br />';
  }
        
  while($note = $notes->next())
  {
    ?>
          
    <div class="note<?php print ($notes->last() ? ' last' : ''); ?>">
      <?php
        if($note->author_is_staff)
        {
      ?>
      <img src="/acp/static/image/aui-staff.png" alt="Staff" class="middle" />
      <?php
        }
        else
        {
      ?>
      <img src="/acp/static/image/aui-dealer.png" alt="Staff" class="middle" />
      <?php
        }
      ?>
        &nbsp;
      
        On <?php print Tools::longDate($note->timestamp); ?>
        <strong><?php print $note->author_name; ?></strong> wrote:
      <br /><br />
      <?php print str_replace("\n", '<br />', strip_tags($note->content)); ?>
      <?php
        if($note->staff_only)
        {
      ?>
      <br /><br />
      <span class="grey">This note is visible to staff only.</span>
      <?php
        }
      ?>
    </div>
  
    <?php
  }

?>
  <br />
  <div style="padding: 0px 10px 0px 10px; position: relative; top: -18px;">
    <textarea 
      style="max-width: 97%; width: 97%; min-height: 20px; padding: 10px; font-size: 10pt;"
      name="f_content" id="f_content"></textarea>
  
    <br /><br />
    
    <input type="checkbox" value="1" name="f_staff_only" id="f_staff_only" /> &nbsp;
    Make note visible to staff only.
    
    &nbsp; &nbsp; &nbsp;
    
    <span id="notifyDealer">    
      <input type="checkbox" value="1" name="f_tell_dealer" id="f_tell_dealer" /> &nbsp;
      Notify the dealer about this note.
    </span>
    
  </div>

  </div>
</div>
  
<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the one of the buttons to the right to proceed.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Attach Note" />
    <input onclick="window.location='./?act=orders&mode=unprocessed_view&id=<?php print $orderID; ?>';" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel" />
    <br class="clear" />
  </div>
</div>

</form>