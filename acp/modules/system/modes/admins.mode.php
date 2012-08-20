<?php
/**
 * Module: System
 * Mode: Admins
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

//
// Permissions:
// Need to be supervisor.
//
if(!$BF->admin->isSupervisor)
{
  print $BF->admin->notSupervisor();
  exit();
}

?>

<h1>Staff Accounts</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Staff Accounts</h3>
    <p>
      Staff constitute users that can make changes to the system using the ACP (Administration Control Panel).<br />
      You can assign various permission levels to control which sections of the
      <abbr title="Admin Control Panel">ACP</abbr> staff can use.
    </p>
  
    <br />
    <span class="button">
      <a href="./?act=system&mode=admins_add">
        <span class="img" style="background-image:url(/acp/static/icon/user--plus.png)">&nbsp;</span>
        New Staff Account...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_admins')
        
        // Exclude cron services account
        ->where('`name` <> \'{1}\'', 'cron');
  
  // Button callback function
  $buttonFunction = function($row) {
    
    // Define a tool set HTML
    $confirmationJS = 'confirmation(\'Really remove this staff account?\', function() { window.location=\'' .
                      Tools::getModifiedURL(array('mode' => 'admins_remove_do')) . '&id=' . $row->id . '\'; })';
        
    if($row->name != 'root')
    {
      $toolSet  = "\n";
      $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
      $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
      $toolSet .= 'Remove</a>' . "\n";
    }
    
    $toolSet .= '<a class="tool" title="Modify" href="./?act=system&mode=admins_modify&id=' . $row->id . '">' . "\n";
    $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
    $toolSet .= 'Modify</a>' . "\n";
    
    return $toolSet;
    
  };


  // Create a data table view
  $restores = new DataTable('rp1', $BF, $query);
  $restores->setOption('alternateRows');
  $restores->setOption('showTopPager');
  $restores->addColumns(array(
                          array(
                            'dataName' => 'full_name',
                            'niceName' => 'Name'
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'ACP User Name'
                          ),
                          array(
                            'dataName' => 'description',
                            'niceName' => 'Role'
                          ),
                          array(
                            'dataName' => 'email',
                            'niceName' => 'Email Address',
                            'options' => array(
                                           'formatAsMailto' => true
                                         )
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'content' => $toolSet,
                            'options' => array(
                                           'callback' => $buttonFunction
                                         ),
                            'css' => array(
                                       'width' => '140px'
                                     )
                          )
                        )
                       );
  
  // Render & output content
  print $restores->render();
  
?>