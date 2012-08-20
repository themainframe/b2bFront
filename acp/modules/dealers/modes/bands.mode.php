<?php
/**
 * Module: Dealers
 * Mode: Bands
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

<h1>Discount Bands</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Discount Bands</h3>
    <p>
      Discount bands are levels of discount that are applied to the prices of items.<br />
      Dealers that are not assigned to a discount band are not discounted at all.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=dealers&mode=bands_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Discount Band...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('`bf_user_bands`.*, COUNT(`bf_users`.`id`) AS `users`', 'bf_user_bands')
        ->text('LEFT OUTER JOIN `bf_users` ON `bf_user_bands`.`id` = `bf_users`.`band_id`')
        ->group('`bf_user_bands`.`id`');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '100px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this discount band?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'bands_remove_do')) . '&id={id}\'; })';
  $confirmationResetJS = 'confirmation(\'Really reset this discount band?<br />' . 
                         'This will set all matrix multipliers for the band to 1.00000\', function() { window.location=\'' .
                         Tools::getModifiedURL(array('mode' => 'bands_reset_do')) . '&id={id}\'; })';
  
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Reset"  onclick="' . $confirmationResetJS . '">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/arrow-undo.png" alt="Reset" />' . "\n";
  $toolSet .= 'Reset</a>' . "\n";

  // Create a data table view
  $bands = new DataTable('pr1', $BF, $query);
  $bands->setOption('alternateRows');
  $bands->setOption('showTopPager');
  $bands->addColumns(array(
                      array(
                        'dataName' => 'name',
                        'niceName' => 'Code',
                        'css' => array(
                                   'width' => '80px'
                                 )
                      ),
                      array(
                        'dataName' => 'description',
                        'niceName' => 'Name'
                      ),
                      array(
                        'dataName' => 'users',
                        'niceName' => 'Dealers',
                        'css' => array(
                                   'width' => '90px'
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
  print $bands->render();
  
?>