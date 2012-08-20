<?php
/**
 * Module: System
 * Mode: Events 
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

<h1>Event Log</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Event Logging</h3>
    <p>
      The system automatically records background errors and other events here.<br />
      Information recorded includes the dealer or administrator that was logged in, the time and date and the nature of the event.<br />
      <br />
      Also recorded is any action the system took after the event occurred.<br /><br />
      
      For security reasons the event log is a rolling log and cannot be cleared by staff.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_events')
        ->order('timestamp', 'desc');

  // Create a data table view
  $events = new DataTable('ev1', $BF, $query);
  $events->setOption('alternateRows');
  $events->setOption('showTopPager');
  $events->addColumns(array(
                          array(
                            'dataName' => '',
                            'niceName' => '',
                            'content' => '/acp/static/icon/event_levels/{level}.png',
                            'css' => array(
                                            'width' => '16px'
                                          ),
                            'options' => array(
                                           'formatAsImage' => true,
                                           'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => 'title',
                            'niceName' => 'Event',
                            'options' => array(
                                           'formatAsLink' => true,
                                           'linkURL' => Tools::getModifiedURL(array(
                                                          'mode' => 'events_view',
                                                          'event' => '{id}'
                                                        )),
                                          'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => 'timestamp',
                            'niceName' => 'Date',
                            'options' => array(
                                           'formatAsDate' => true,
                                           'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '150px'
                                     )
                          )
                        )
                       );
  
  // Render & output content
  print $events->render();
  
?>
