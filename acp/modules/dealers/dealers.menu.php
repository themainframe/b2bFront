<?php
/**
 * Admin Module Menu File : Dealers
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Gain access to BFClass
global $BF;

// Get primary mode
$primaryMode = Tools::valueAt(explode('_', $BF->in('mode')), 0);

?>
        <li class="<?=Tools::conditional('browse', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=browse" style="background-image: url(./static/icon/magnifier.png);">Browse</a>
        </li>
        <li class="<?=Tools::conditional('add', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=add" style="background-image: url(./static/icon/plus-circle.png);">Add</a>
        </li>
        <li class="<?=Tools::conditional('profiles', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=profiles" style="background-image: url(./static/icon/users.png);">Profiles</a>
        </li><!--
        <li class="<?=Tools::conditional('unapproved', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=unapproved" style="background-image: url(./static/icon/user--exclamation.png);">Requests</a>
        </li>-->
        <li class="<?=Tools::conditional('questions', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=questions" style="background-image: url(./static/icon/question-balloon.png);">Questions</a>
          
<?php

  // Badges?
  $questionCount = $BF->admin->api('Questions')->countUnanswered();
  if($questionCount > 0)
  {
    // Print badge
    print '          <span class="badge">' . 
          $questionCount . '</span>';
  }

?>
          
        </li>
        <li class="<?=Tools::conditional('cctv', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=cctv" style="background-image: url(./static/icon/binocular.png);">CCTV</a>
        </li>
        <li class="<?=Tools::conditional('bulk', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=bulk" style="background-image: url(./static/icon/mails-stack.png);">Email</a>
        </li>
        <li class="<?=Tools::conditional('bands', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=bands" style="background-image: url(./static/icon/point-all.png);">Discount Bands</a>
        </li>
        <li class="<?=Tools::conditional('matrix', $primaryMode, 'selected')?>">
          <a href="./?act=dealers&mode=matrix" style="background-image: url(./static/icon/grid.png);">Discount Matrix</a>
        </li>