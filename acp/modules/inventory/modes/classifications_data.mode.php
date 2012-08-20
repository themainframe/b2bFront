<?php
/**
 * Module: Inventory
 * Mode: Classification Data
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

// Get the classification
$classification = $BF->db->getRow('bf_classifications', $BF->inInteger('id'));

if(!$classification)
{
  // Not valid
  $BF->go('./?act=inventory&mode=classifications');
}

// Get the attributes
$attributes = $BF->db->query();
$attributes->select('*', 'bf_classification_attributes')
           ->where('`classification_id` = \'{1}\'', $classification->id)
           ->execute();
           
// No Attributes?
if($attributes->count == 0)
{
  // Can't use this view
  $BF->admin->notifyMe('No Attributes', 'The classification \'' . $classification->name . 
    '\' has no attributes.');
  $BF->go('./?act=inventory&mode=classifications');
}

?>

<h1>Items Classified as <?php print $classification->name; ?></h1>
<br />

<div class="panel">
  <div class="contents">
    
    
    <h3>About Editing Item Classifications Attribute Values</h3>
    
    <p>
      This view allows you to assign values to the Classification Attributes for each item in the Classification.<br />
      Click on the text under the Attribute Titles for each row to edit it.<br /><br />
      You can use the Tab key to move to the next cell along (hold Shift to reverse).
      
      <br /><br />
      
      <a href="./?act=inventory&mode=classifications" title="Classifications">Back to Classifications</a>
      
    </p>

  </div>
</div>

<br />


<?php

  // Build columns
  $columns = array(
      array(
       'dataName' => '',
       'niceName' => '',
       'options' => array(
                      'callback' => $this->parent->images->loadThumbnail,
                      'formatAsImage' => true,
                      'cellCss' => array(
                                   'background' => '#fff',
                                   'padding-right' => '10px'
                                   ),
                    'fixedOrder' => true
                  ),
       'css' => array(
                  'width' => '40px'
                )
     ),
    array(
      'dataName' => 'sku',
      'niceName' => 'SKU',
      'options' => array(
        'callback' => function($row, $parent, $value, $index)
                      {
                        return '<span title="' . str_replace('"', '\'\'', $row->name) . 
                          '">' . $row->sku . '</span>';
                      },
      'formatAsLink' => true,
      'linkNewWindow' => true,
      'linkURL' => $BF->config->get('com.b2bfront.site.url', true)
                   . '?option=item&id={id}',
      )
    )
  );
  
  $GLOBALS['cellIndex'] = 0;
  $attribs = $attributes->assoc();
  
  // Arrange linearly
  $GLOBALS['attribs'] = array();
  foreach($attribs as $attrib)
  {
    $GLOBALS['attribs'][] = $attrib;
  }

  $GLOBALS['new_count'] = 0;

  // Add each attribute
  while($attribute = $attributes->next())
  {
      $columns[] = array(
      'dataName' => 'sku',
      'niceName' => $attribute->name,
      'css' => array(
                 'width' => '10%' 
               ),
      'options' => array(
        'virtualEditable' => true,
        'fixedOrder' => true,
        'callback' => function($row, $parent, $value, $index)
                      {
                        // Increment new count
                        $GLOBALS['new_count'] ++;
                      
                        // Create a BOM item to get attributes
                        $item = new BOMItem($row->id, $parent);
                        $GLOBALS['cellIndex'] ++;
                        
                        return '<span item="' . $row->id . '" cf="' . $GLOBALS['attribs'][$index - 2]['id'] . '" id="attr_' . ($item->linearProperties[$index - 2]['id'] == '' ? $GLOBALS['new_count'] . '-n' : $item->linearProperties[$index - 2]['id']) . '" cellid="' . $GLOBALS['cellIndex'] . '" unselectable="on" table="bf_item_attribute_applications" field="value" rowid="' .
                          $item->linearProperties[$index - 2]['id'] . '" class="editable ui-draggable ui-droppable">' . 
                          ($item->linearProperties[$GLOBALS['attribs'][$index - 2]['id']]['value'] == '' ? '' :
                           $item->linearProperties[$GLOBALS['attribs'][$index - 2]['id']]['value']) . '</span>';   
                      }
      )
    );
  }

  // Create a new query to retreieve classifications
  $query = $BF->db->query();
  
  $query->select('*', 'bf_items')
        ->where('`classification_id` = \'{1}\'', $classification->id);
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this classification?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'classifications_remove_do')) . '&clid={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=inventory&mode=classifications_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the items
  $items = new DataTable('items_classified', $BF, $query);
  $items->setOption('alternateRows');
  $items->setOption('showTopPager');
  $items->addColumns($columns);
  
  // Render & output content
  print $items->render();
?>