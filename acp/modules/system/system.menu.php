<?php
/**
 * Admin Module Menu File : System
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
        <li class="<?=Tools::conditional('info', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=info" style="background-image: url(/acp/static/icon/information.png);">Info</a>
        </li>
        <li class="<?=Tools::conditional('config', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=config" style="background-image: url(/acp/static/icon/wrench.png);">Configuration</a>
        </li>
        <li class="<?=Tools::conditional('admins', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=admins" style="background-image: url(/acp/static/icon/user-business.png);">Staff</a>
        </li>
        <li class="<?=Tools::conditional('profiles', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=profiles" style="background-image: url(/acp/static/icon/users.png);">Staff Profiles</a>
        </li>
<!--
        <li class="<?=Tools::conditional('restore', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=restore" style="background-image: url(/acp/static/icon/arrow-curve-180-left.png);">Restore</a>
        </li>
-->
        <li class="<?=Tools::conditional('locales', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=locales" style="background-image: url(/acp/static/icon/locale.png);">Locales</a>
        </li>
        <li class="<?=Tools::conditional('drafts', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=drafts" style="background-image: url(/acp/static/icon/document-shred.png);">Drafts</a>
        </li>
        <li class="<?=Tools::conditional('events', $primaryMode, 'selected')?>">
          <a href="./?act=system&mode=events" style="background-image: url(/acp/static/icon/system-monitor.png);">Events</a>
        </li>