<?php
/**
 * Module: Inventory
 * Mode: Item Tags
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

<h1>Item Tags</h1>
<br />

<div class="panel">
  <div class="contents">
    
    <h3>About Item Tags</h3>
    
    <p>
      Item Tags let you mark items so that they stand out in search results and list views.<br />
      For example, you may want to tag all the new items so that they stand out to dealers.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=inventory&mode=tags_add">
        <span class="img" style="background-image:url(/acp/static/icon/tag--plus.png)">&nbsp;</span>
        New Item Tag...
      </a>
    </span>

    <br /><br />
  </div>
</div>

<br />

<?php
  
  // Find all tags
  $query = $BF->db->query();
  $query->select('`bf_item_tags`.*, COUNT(`bf_item_tag_applications`.`id`) AS `items`', 'bf_item_tags')
        ->text('LEFT OUTER JOIN `bf_item_tag_applications` ON `bf_item_tags`.`id` = `bf_item_tag_applications`.`item_tag_id` ')
        ->group('`bf_item_tags`.`id`');
  
  // Removal confirmation
  $confirmationJS = 'confirmation(\'Really remove this Item Tag?<br />The tag will be removed from all items.\', function() { window.location=\'' .
                  Tools::getModifiedURL(array('mode' => 'tags_remove_do')) . '&id={id}\'; })';

  
  // Create tools
  $toolSet  = "\n";
  $toolSet .= '<a class="tool" href="./?act=inventory&mode=tags_modify&id={id}" title="Modify">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/tag--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  
  // Create a data view
  $itemTags = new DataTable('f_tag', $BF, $query);
  $itemTags->setOption('alternateRows');
  $itemTags->setOption('showTopPager');
  $itemTags->addColumns(array(
                          array(
                            'dataName' => 'icon_path',
                            'niceName' => '',
                            'options' => array(
                                           'formatAsImage' => true,
                                           'fixedOrder' => true
                                         ),
                            'css' => array(
                                       'width' => '16px'
                                     )
                          ),
                          array(
                            'dataName' => 'name',
                            'niceName' => 'Tag Name',
                            'options' => array(
                                           'fixedOrder' => true
                                         )
                          ),
                          array(
                            'dataName' => 'items',
                            'niceName' => 'Items',
                            'options' => array(
                              'formatAsLink' => true,
                              'linkURL' => '/acp/?act=inventory&mode=browse&f_tag={id}&no_save_default_view=1'
                            ),
                            'css' => array('width' => '100px')
                          ),
                          array(
                            'dataName' => '',
                            'niceName' => 'Actions',
                            'options' => array('fixedOrder' => true),
                            'content' => $toolSet,
                            'css' => array('width' => '150px')
                          )
                        ));
  
  // Render & output content
  print $itemTags->render();
  
?>
