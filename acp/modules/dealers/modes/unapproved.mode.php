<?php
/**
 * Module: Dealers
 * Mode: Unapproved / Requests for Accounts view
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

<h1>Requests for Accounts</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Requests for Accounts</h3>
    <p>
      This view shows requests from users wishing to gain access to dealer functionality on the website.<br />
      You can accept or decline these requests.<br /><br />
      
      You can change which actions different types of user can perform using the
      <a href="./?act=dealers&mode=profiles" class="new" target="_blank" title="Dealer Profile">Dealer Profiles</a>
      view.
    </p>
  </div>
</div>    
     

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_users')
        ->where('`requires_review` = \'1\'');
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really decline this request?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'unapproved_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Decline</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=dealers&mode=unapproved_review&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Review</a>' . "\n";
  
  // Create a data table view
  $dealers = new DataTable('dealers_browse', $BF, $query);
  $dealers->setOption('alternateRows');
  $dealers->setOption('showTopPager');
  $dealers->setOption('showDownloadOption');
  $dealers->addColumns(array(
                        array(
                          'niceName' => '',
                          'dataName' => 'id',
                          'css' => array(
                                     'width' => '10px'
                                   ),
                          'options' => array(
                                              'formatAsCheckbox' => true,
                                              'fixedOrder' => true
                                            )
                        ),
                        array(
                          'dataName' => 'name',
                          'niceName' => 'User Name'
                        ),
                        array(
                          'dataName' => 'description',
                          'niceName' => 'Description'
                        ),
                        array(
                          'dataName' => 'email',
                          'niceName' => 'Email',
                          'options' => array(
                                              'formatAsMailto' => true
                                            )
                        ),
                        array(
                          'niceName' => 'Options',
                          'css' => array(
                                     'width' => '135px'
                                   ),
                          'content' => $toolSet
                        )
                      ));

  // Render & output content
  print $dealers->render();
  
?>