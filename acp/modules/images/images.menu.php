<?php
/**
 * Admin Module Menu File : Images
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
          <a href="./?act=images&mode=browse" style="background-image: url(./static/icon/magnifier.png);">Browse</a>
        </li>
        <li class="<?=Tools::conditional('upload', $primaryMode, 'selected')?>">
          <a href="./?act=images&mode=upload" style="background-image: url(./static/icon/image-import.png);">Upload</a>
        </li><!--
        <li class="<?=Tools::conditional('unused', $primaryMode, 'selected')?>">
          <a href="./?act=images&mode=unused" style="background-image: url(./static/icon/image--exclamation.png);">Unused</a>
        </li>-->
        <li class="<?=Tools::conditional('download', $primaryMode, 'selected')?>">
          <a href="./?act=images&mode=download" style="background-image: url(./static/icon/navigation-270-grn.png);">Download All</a>
        </li>