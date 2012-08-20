<?php
/**
 * Admin Module Menu File : Dashboard
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
        <li class="<?=Tools::conditional('main', $primaryMode, 'selected')?>">
          <a href="./?act=dashboard&mode=main" style="background-image: url(/acp/static/icon/application-detail.png);">Overview</a>
        </li>
        <li class="<?=Tools::conditional('notifications', $primaryMode, 'selected')?>">
          <a href="./?act=dashboard&mode=notifications" style="background-image: url(/acp/static/icon/information.png);">My Notifications</a>
        </li>
        <li class="<?=Tools::conditional('downloads', $primaryMode, 'selected')?>">
          <a href="./?act=dashboard&mode=downloads" style="background-image: url(/acp/static/icon/navigation-270-grn.png);">My Downloads</a>
          
<?php

  // Badges?
  $downloadCount = $BF->db->select('`id`', 'bf_admin_downloads')
                          ->where('`admin_id` = \'{1}\'', $BF->admin->AID)
                          ->execute()
                          ->count;
                          
  if($downloadCount > 0)
  {
    // Print badge
    print '          <span class="badge">' . 
          $downloadCount . '</span>';
  }

?>
          
        </li>
        <!--<li class="<?=Tools::conditional('help', $primaryMode, 'selected')?>">
          <a href="./?act=dashboard&mode=help" style="background-image: url(/acp/static/icon/book-question.png);">Help Centre</a>
        </li>-->
