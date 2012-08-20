<?php
/**
 * Module: System
 * Mode: Drafts
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

<h1>Drafts</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Drafts</h3>
    <p>
      This software automatically saves regular drafts of large bodies of text such as item descriptions and emails.<br />
      If there is a problem and your web browser is closed before you have a chance to save, a draft will be kept here.
      <br /><br />
      Drafts will be kept for <?php print $BF->config->get('drafts.max-age'); ?> days after their last modification before they are deleted.
      <br /><br />
      Click on a draft to view it.
    </p>
  </div>
</div>

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_drafts')
        ->where('admin_id = \'{1}\'', $BF->admin->AID);

  // Create a data table view
  $locales = new DataTable('dr1', $BF, $query);
  $locales->setOption('alternateRows');
  $locales->setOption('showTopPager');
  $locales->addColumns(array(
                          array(
                            'dataName' => 'timestamp',
                            'niceName' => 'Last Modified',
                            'options' => array(
                                           'formatAsDate' => true,
                                           'formatAsDateFormat' => 'F j, Y, g:i a'  
                                         )
                          ),
                          array(
                            'dataName' => 'description',
                            'niceName' => 'Description',
                            'options' => array(
                                           'formatAsLink' => true,
                                           'linkURL' => Tools::getModifiedURL(array(
                                                          'mode' => 'drafts_view',
                                                          'id' => '{id}'
                                                        ))
                                         )
                          ),
                          array(
                            'dataName' => 'content',
                            'niceName' => 'Length',
                            'options' => array(
                                           'formatAsStringLength' => true
                                         )
                          )
                        )
                      );
  
  // Render & output content
  print $locales->render();
  
?>