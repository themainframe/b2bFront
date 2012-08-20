<?php
/**
 * Module: System
 * Mode: Restore Points
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

<h1>Restore Points</h1>
<br />
<div class="panel">
  <div class="contents">
    <h3>About Restore Points</h3>
    <p>
      This software automatically produces <em>restore points</em> in the background when you make changes in the Admin Control Panel.<br />
      Restore points allow you to '<em>go back</em>' to a time in the past before you made a mistake or a malfunction occurred.
      <br /><br />
      
      Restore Points cannot be deleted, they will be removed automatically.  You can modify this behaviour in the 
      <a href="./?act=system&mode=config" title="Config">Config</a> tab.
      <br /><br />
      
      You should choose the restore point <em>associated with the event that caused the changes you want to undo</em>.<br />
      There will be further options available after selecting a Restore Point below.
    </p>
  </div>
</div>

<br />
        
<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_restore_points')
        ->order('timestamp', 'desc');
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" href="' . Tools::getModifiedURL(array('mode' => 'restore_confirm'))
              . '&id={id}" title="Restore to this point">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/arrow-curve-180-left.png" alt="Restore" />' . "\n";
  $toolSet .= 'Restore...</a>' . "\n";  

  // Create a data table view
  $restores = new DataTable('rp1', $BF, $query);
  $restores->setOption('alternateRows');
  $restores->setOption('showTopPager');
  $restores->addColumns(array(
                          array(
                            'dataName' => 'timestamp',
                            'niceName' => 'Created',
                            'options' => array(
                                           'fixedOrder' => true,
                                           'formatAsDate' => true,
                                           'formatAsDateFormat' => 'F j, Y, g:i a'  
                                         )
                          ),
                          array(
                            'dataName' => 'creation_reason',
                            'niceName' => 'Associated Event',
                            'options' => array(
                                           'fixedOrder' => true 
                                         )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'content' => $toolSet ,
                            'options' => array(
                                           'fixedOrder' => true 
                                         )
                          ),
                        )
                       );
  
  // Render & output content
  print $restores->render();
  
?>