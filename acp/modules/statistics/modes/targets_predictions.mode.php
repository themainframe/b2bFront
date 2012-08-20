<?php
/**
 * Module: Statistics
 * Mode: Target Predictions View
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

// Load the target to modify
$targetID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_targets')
           ->where('id = \'{1}\'', $targetID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=statistics&mode=targets');
  exit();
}

$target = $BF->db->next();

?>

<script type="text/javascript">
  
  $(function() {
    
    $('#progress').progressbar({
      'max' : <?php print $target->value; ?>,
      'value': 1000
    });
    
  });
  
</script>

<h1>Predictions for <?php print $target->description; ?></h1>
<br />

<div class="panel">
  <div class="title">Current Progress</div>
  <div class="contents">

    <p>
      The current progress towards hitting this target is: &nbsp; 
      <strong>1042</strong> / <strong><?php print $target->value; ?></strong><br />
      <div style="padding: 10px 10px 0px 10px;"> 
        <div id="progress"></div>
      </div>
    </p>

  </div>
</div>

<br />

<div class="panel">
  <div class="title">Prediction</div>
  <div class="contents">

    <p>
      This software can offer a linear prediction of the likeliness of the target being hit.<br />
      The current prediction is:<br /><br />
    </p>

  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to return to the Targets list.</strong>
    </p>
    <input class="submit ok" type="button" 
      onclick="window.location='./?act=statistics&mode=targets';" style="float: right;" value="OK" />
    <br class="clear" />
  </div>
</div>