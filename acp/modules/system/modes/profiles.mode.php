<?php
/**
 * Module: System
 * Mode: Admin Profiles
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

<h1>Staff Profiles</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Staff Profiles</h3>
    <p>
      Staff Profiles allow you to control which actions individual members of staff may perform.<br />
      They can be used to limit staff access to sensitive controls or data views.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=system&mode=profiles_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Staff Profile
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_profiles');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '30px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this staff profile?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'profiles_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=system&mode=profiles_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";

  // Create a data table view
  $locales = new DataTable('pr1', $BF, $query);
  $locales->setOption('alternateRows');
  $locales->setOption('showTopPager');
  $locales->addColumns(array(
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Profile Name'
                          ),
                          array(
                            'dataName' => 'can_login',
                            'niceName' => 'Enabled',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_account',
                            'niceName' => 'Modify Dealers',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_categories',
                            'niceName' => 'Modify Categories',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_items',
                            'niceName' => 'Modify Inventory',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_orders',
                            'niceName' => 'Modify Orders',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_website',
                            'niceName' => 'Modify Website',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_system',
                            'niceName' => 'Modify System',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_stats',
                            'niceName' => 'Access Statistics',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_data',
                            'niceName' => 'Import Data',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_chat',
                            'niceName' => 'Use IM',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'niceName' => 'Options',
                            'content' => $toolSet ,
                            'css' => array(
                                       'width' => '135px'
                                     )
                          )
                        )
                      );
  
  // Render & output content
  print $locales->render();
  
?>