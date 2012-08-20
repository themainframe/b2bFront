<?php
/**
 * Admin Module Menu File : Statistics
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
        <li class="<?=Tools::conditional('live', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=live" style="background-image: url(/acp/static/icon/application-monitor.png);">Current</a>
        </li>
        <li class="<?=Tools::conditional('overview', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=overview" style="background-image: url(/acp/static/icon/clock-select.png);">Overview</a>
        </li>
        <li class="<?=Tools::conditional('custom', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=custom" style="background-image: url(/acp/static/icon/counter.png);">Custom</a>
        </li> 
        <li class="<?=Tools::conditional('visual', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=visual" style="background-image: url(/acp/static/icon/color.png);">Visualisations</a>
        </li><!--
       
        <li class="<?=Tools::conditional('reports', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=reports" style="background-image: url(/acp/static/icon/reports.png);">Generate Reports</a>
        </li>
        <li class="<?=Tools::conditional('targets', $primaryMode, 'selected')?>">
          <a href="./?act=statistics&mode=targets" style="background-image: url(/acp/static/icon/target.png);">Targets</a>
        </li>-->