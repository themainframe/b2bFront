<?php
/**
 * Module: Statistics
 * Mode: Custom Statistics
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

<h1>Custom Statistics</h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Custom Statistics</h3>
    
    <p>
      Custom Statistics allow you to keep track of events associated with specific inventory items.<br />
      For example, you can track how many times a specific item has been viewed.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=statistics&mode=custom_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)"></span>
        &nbsp;New Custom Statistic...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve custom statistics
  $query = $BF->db->query();
  $query->select('`bf_statistics`.*, COUNT(`bf_statistic_snapshot_data`.`id`) AS `cycles`', 'bf_statistics')
        ->text('LEFT OUTER JOIN `bf_statistic_snapshot_data` ON `bf_statistics`.`id` = `bf_statistic_snapshot_data`.`statistic_id`')
        ->where('`bf_statistics`.`aftermarket` = 1')
        ->group('`bf_statistics`.`id`');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this custom statistic?<br />' . 
                    'All associated recorded data will be removed.\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'custom_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data table view to show the records
  $custom = new DataTable('cu1', $BF, $query);
  $custom->setOption('alternateRows');
  $custom->setOption('showTopPager');
  $custom->addColumns(array(
                        array(
                          'dataName' => 'description',
                          'niceName' => 'Description'
                        ),
                        array(
                          'dataName' => 'cycles',
                          'niceName' => 'Periods',
                          'css' => array(
                                     'width' => '70px'
                                   )
                        ),
                        array(
                          'dataName' => '',
                          'niceName' => 'Actions',
                          'options' => array('fixedOrder' => false),
                          'css' => array(
                                     'width' => '60px',
                                     'text-align' => 'right',
                                     'padding-right' => '10px'
                                   ),
                          'content' => $toolSet
                        )
                      ));
  
  // Render & output content
  print $custom->render();
?>