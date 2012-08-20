<?php
/**
 * Admin Module Menu File : Website
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
        <li class="<?=Tools::conditional('pages', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=pages" style="background-image: url(/acp/static/icon/document-copy.png);">Pages</a>
        </li>
        <li class="<?=Tools::conditional('menus', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=menus" style="background-image: url(/acp/static/icon/ui-menu-blue.png);">Menus</a>
        </li>
<!--
        <li class="<?=Tools::conditional('views', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=views" style="background-image: url(/acp/static/icon/blueprints.png);">Views</a>
        </li>
-->
        <li class="<?=Tools::conditional('articles', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=articles" style="background-image: url(/acp/static/icon/newspapers.png);">Articles</a>
        </li>
        <li class="<?=Tools::conditional('downloads', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=downloads" style="background-image: url(/acp/static/icon/navigation-270-grn.png);">Downloads</a>
        </li>
<!--
        <li class="<?=Tools::conditional('modifiers', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=modifiers" style="background-image: url(/acp/static/icon/receipt-invoice.png);">Invoice Modifiers</a>
        </li>
-->
        <li class="<?=Tools::conditional('skin', $primaryMode, 'selected')?>">
          <a href="./?act=website&mode=skin" style="background-image: url(/acp/static/icon/palette.png);">Skin</a>
        </li>