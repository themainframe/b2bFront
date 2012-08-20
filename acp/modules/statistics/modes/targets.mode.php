<?php
/**
 * Module: Statistics
 * Mode: Targets
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

?>

<h1>Targets</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Targets</h3>
    
    <p>
      Targets are values that you expect specific statistics will reach each period.<br />
      You can set targets and receive notifications when they are reached and predictions of shortfalls or overshoots.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=statistics&mode=custom_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)"></span>
        &nbsp;New Target...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve targets
  $query = $BF->db->query();
  $query->select('*', '`bf_targets`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this custom statistic?<br />' . 
                    'All associated recorded data will be removed.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'targets_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a href="./?act=statistics&mode=targets_predictions&id={id}" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/eye.png" alt="Remove" />' . "\n";
  $toolSet .= 'Predictions</a>' . "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the records
  $targets = new DataTable('tg1', $BF, $query);
  $targets->setOption('alternateRows');
  $targets->setOption('showTopPager');
  $targets->addColumns(array(
                        array(
                          'dataName' => 'description',
                          'niceName' => 'Description'
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '140px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $targets->render();
?>