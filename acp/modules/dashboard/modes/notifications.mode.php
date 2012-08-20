<?php
/**
 * Module: System
 * Mode: Notifications 
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

<h1>My Notifications</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Notifications</h3>
    <p>
      Staff are automatically notified about important events.<br />
      Emails containing recent notifications are regularly sent to staff, as well as pop-up notifications within the 
      <abbr name="Admin Control Panel">ACP</abbr>.<br />
      <br />
      A list of your recent notifications can be viewed here.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_notifications')
        ->where('`logged` = 1')
        ->order('timestamp', 'desc');

  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a href="./?act=dashboard&mode=notifications_remove_do&id={id}&from_list=1" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Dismiss</a>' . "\n";

  // Create a data table view
  $notifications = new DataTable('no1', $BF, $query);
  $notifications->setOption('alternateRows');
  $notifications->setOption('showTopPager');
  $notifications->addColumns(array(
                          array(
                            'dataName' => '',
                            'niceName' => '',
                            'content' => '/acp/static/icon/{icon_url}',
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
                            'niceName' => 'Title',
                            'options' => array(
                                          'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '180px'
                                     )
                          ),
                          array(
                            'dataName' => 'content',
                            'niceName' => 'Content',
                            'options' => array(
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
                        )
                       );
  
  // Render & output content
  print $notifications->render();
  
?>
