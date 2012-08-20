<?php
/**
 * Admin Module Menu File : Data
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
        <li class="<?=Tools::conditional('import', $primaryMode, 'selected')?>">
          <a href="./?act=data&mode=import" style="background-image: url(/acp/static/icon/document-excel.png);">Import</a>
        </li>
        <li class="<?=Tools::conditional('scheduled', $primaryMode, 'selected')?>">
          <a href="./?act=data&mode=scheduled" style="background-image: url(/acp/static/icon/calendar-insert.png);">Scheduled Imports</a>
        </li>
        <li class="<?=Tools::conditional('documents', $primaryMode, 'selected')?>">
          <a href="./?act=data&mode=documents" style="background-image: url(/acp/static/icon/document-office.png);">Create Documents</a>
        </li>
        <li class="<?=Tools::conditional('jobs', $primaryMode, 'selected')?>">
          <a href="./?act=data&mode=jobs" style="background-image: url(/acp/static/icon/table-delete-row.png);">Jobs</a>
        </li>