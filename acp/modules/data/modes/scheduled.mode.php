<?php
/**
 * Module: Data
 * Mode: Scheduled Imports
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

<h1>Scheduled Imports</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Scheduled Imports</h3>
    
    <p>
      Scheduled Imports are Inventory Data Imports that will take place in the future.<br />
      You can upload a data file and have it imported automatically at a specified time and date.<br /><br />
      
      For example, you could create two Scheduled Imports to adjust prices at the start and end of an offer period.<br /><br />
    </p>
    
    <span class="button">
      <a href="./?act=data&mode=import&show_schedule_ui=1">
        <span class="img" style="background-image:url(/acp/static/icon/calendar--plus.png)">&nbsp;</span>
        Create a New Scheduled Import...
      </a>
    </span>
    
    <br /><br />
  </div>
</div>

<br />

<?php

  // Create a new query to retreieve scheduled imports
  $query = $BF->db->query();
  $query->select('*', 'bf_scheduled_imports');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this scheduled import?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'scheduled_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=data&mode=scheduled_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the brands
  $schedules = new DataTable('sh1', $BF, $query);
  $schedules->setOption('alternateRows');
  $schedules->setOption('showTopPager');
  $schedules->addColumns(array(
                          array(
                            'dataName' => 'completed',
                            'niceName' => '',
                            'options' => array(
                                           'formatAsToggleImage' => true,
                                           'toggleImageTrue' => '/acp/static/icon/tick-circle.png',
                                           'toggleImageFalse' => '/acp/static/icon/hourglass.png',
                                           'toggleImageTrueTitle' => 'This import has been applied',
                                           'toggleImageFalseTitle' => 'This import is scheduled',
                                           'fixedOrder' => true
  
                                         ),
                            'css' => array(
                                       'width' => '16px',
                                       'text-align' => 'center',
                                       'padding-left' => '0px'
                                     )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Schedule Name'
                          ),
                          array(
                            'dataName' => 'timestamp',
                            'niceName' => 'Date',
                            'options' => array(
                                           'formatAsDate' => true
                                         ),
                            'css' => array('width' => '135px')
                          ),
                          array(
                            'dataName' => 'notification_sms',
                            'niceName' => 'SMS',
                            'options' => array(
                                           'formatAsToggleImage' => true,
                                           'toggleImageTrue' => '/acp/static/icon/tick-circle.png',
                                           'toggleImageFalse' => '/acp/static/icon/cross-circle.png',
                                           'toggleImageTrueTitle' => 'You will be notified via SMS',
                                           'toggleImageFalseTitle' => 'You will not be notified via SMS',
                                           'fixedOrder' => true
  
                                         ),
                            'css' => array(
                                       'width' => '45px',
                                       'text-align' => 'center',
                                       'padding-left' => '0px'
                                     )
                          ),
                          array(
                            'dataName' => 'notification_email',
                            'niceName' => 'Email',
                            'options' => array(
                                           'formatAsToggleImage' => true,
                                           'toggleImageTrue' => '/acp/static/icon/tick-circle.png',
                                           'toggleImageFalse' => '/acp/static/icon/cross-circle.png',
                                           'toggleImageTrueTitle' => 'You will be notified via Email',
                                           'toggleImageFalseTitle' => 'You will not be notified via Email',
                                           'fixedOrder' => true
  
                                         ),
                            'css' => array(
                                       'width' => '45px',
                                       'text-align' => 'center',
                                       'padding-left' => '0px'
                                     )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => false),
                            'css' => array(
                                       'width' => '130px',
                                       'text-align' => 'right',
                                       'padding-right' => '10px'
                                     ),
                            'content' => $toolSet
                          )
                        ));
  
  // Render & output content
  print $schedules->render();
?>