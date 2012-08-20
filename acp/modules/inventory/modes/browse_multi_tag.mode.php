<?php
/**
 * Module: Inventory
 * Mode: Browse Multi Tag 
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

// Count all items
$items = $BF->in('dv_inventory');
if(!$items)
{
  // Return to inventory
  header('Location: ./?act=inventory');
  exit();
}

// Split
$itemsArray = explode(',', $items);

// Get Checkboxes
foreach($itemsArray as $key => $item)
{
  if($BF->inInteger('inventory_' . $item) != 1)
  {
    // Not Selected
    unset($itemsArray[$key]);
  }
}

// Count 
$itemsCount = count($itemsArray);

// Empty set?
if($itemsCount == 0)
{
  // Return to inventory
  $BF->admin->notifyMe('Instruction', 'Select one or more items first.', 'property.png');
  header('Location: ./?act=inventory');
  exit();
}

?>

<h1>Multiple Item Tagging</h1>
<br />

<div class="panel">
  <div class="title">Tagging Information</div>
  <div class="contents">
    
    <p>
      Choose an action for each of the item tags below.<br />
      The possible actions are as follows:<br />

      <ul style="margin: 0px 0px 0px 10px;">
        <li><strong>Remove</strong> will remove the tag from the selected items.</li>
        <li><strong>Leave</strong> will not change the tag on the selected items.</li>
        <li><strong>Add</strong> will add the tag to the selected items.</li>
      </ul>

    </p>

  </div>
</div>

<br />

<form method="post" action="./?act=inventory&mode=browse_multi_tag_do">
<input type="hidden" name="dv_inventory" value="<?php print Tools::CSV($itemsArray); ?>" />


  
            <table class="suboptions" style="width: 100%;">
            
              <thead>
              
                <tr class="header">
                  <td>
                    <strong>Item Tag</strong>
                  </td>
                  <td style="width: 60px; text-align: center;" class="value">
                    <strong>Remove</strong>
                  </td>
                  <td style="width: 60px; text-align: center;" class="value">
                    <strong>Leave</strong>
                  </td>
                  <td style="width: 60px; text-align: center;" class="value">
                    <strong>Add</strong>
                  </td>
                </tr>
                
              </thead>
            
              <tbody>
                
<?php
  
  // Find all tags
  $query = $BF->db->query();
  $query->select('*', 'bf_item_tags')
       ->order('name', 'asc')
       ->execute();
       
  // Show options for each tag
  while($tag = $query->next())
  {
    ?>
    
                <tr>
                  <td style="background: #fff;">
                    &nbsp;<img class="middle" src="<?php print $tag->icon_path; ?>" alt="Icon" />
                    &nbsp;&nbsp;&nbsp;<?php print $tag->name; ?>
                  </td>
                  <td class="value" style="width: 60px;">
                    <input type="radio" name="f_tag_<?php print $tag->id; ?>" value="remove" />
                  </td>
                  <td class="value" style="width: 60px;">
                    <input checked="checked" type="radio" name="f_tag_<?php print $tag->id; ?>" value="leave" />
                  </td>
                  <td class="value" style="width: 60px;">
                    <input type="radio" name="f_tag_<?php print $tag->id; ?>" value="add" />
                  </td>
                </tr>
    
    <?php
  }

?>
                
              </tbody>
            </table>


<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click one of the buttons to the right to proceed.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <input onclick="history.back()" class="submit bad" type="button" style="float: right; margin-right: 10px;" value="Cancel and Exit" />
    <br class="clear" />
  </div>
</div>

</form>