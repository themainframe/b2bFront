<?php
/**
 * Module: Dealers
 * Mode: Profiles
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

<h1>Dealer Profiles</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Dealer Profiles</h3>
    <p>
      Dealer Profiles allow you to restrict which parts of your catalogue dealers can view.<br />
      They can be used to control the visibility of categories, pages and other parts of the system to dealers.
      <br /><br />
      Click on a Profile name to modify it's details.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=dealers&mode=profiles_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Dealer Profile
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_user_profiles');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '70px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this profile?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'profiles_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=dealers&mode=profiles_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";

  // Create a data table view
  $profiles = new DataTable('pr1', $BF, $query);
  $profiles->setOption('alternateRows');
  $profiles->setOption('showTopPager');
  $profiles->addColumns(array(
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Profile Name'
                          ),
                          array(
                            'dataName' => 'can_see_rrp',
                            'niceName' => 'Can See RRPs',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'dataName' => 'can_see_prices',
                            'niceName' => 'Can See Prices',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_wholesale',
                            'niceName' => 'Wholesale Prices',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_pro_rate',
                            'niceName' => 'Always Pro Net Price',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_order',
                            'niceName' => 'Submit Orders',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         ),
                          ),
                          array(
                            'dataName' => 'can_question',
                            'niceName' => 'Submit Questions',
                            'css' => $columnCSS,
                            'options' => array(
                                           'formatAsBoolean' => true
                                         )
                          ),
                          array(
                            'niceName' => 'Options',
                            'content' => $toolSet ,
                            'css' => array(
                                       'width' => '150px'
                                     )
                          )
                        )
                      );
  
  // Render & output content
  print $profiles->render();
  
?>