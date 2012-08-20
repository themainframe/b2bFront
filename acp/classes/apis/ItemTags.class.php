<?php
/**
 * Item Tags
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class ItemTags extends API
{
  /**
   * Apply an Item Tag to an item.
   * @param integer $itemID The ID of the item.
   * @param integer $itemTagID The ID of the item tag.
   * @return boolean
   */
  public function applyItemTag($itemID, $itemTagID)
  {
    // IDs to Integer only
    $itemID = intval($itemID);
    $itemTagID = intval($itemTagID);
  
    $this->db->insert('bf_item_tag_applications', array(
                       'item_id' => $itemID,
                       'item_tag_id' => $itemTagID
                     ))->execute();
                     
    return true;
  }  
  
  /**
   * Add an Item Tag
   * @param string $name The unique name of the item tag.
   * @param string $icon The icon path to use for the item tag.
   * @param integer $formatBold Optionally enable bold list formatting. Default 0.
   * @param integer $formatItalic Optionally enable italic list formatting. Default 0
   * @param integer $formatSmallCaps Optionally enable small caps list formatting. Default 0.
   * @param string $formatColour Optionally specify a colour for list formatting. Default white.
   * @return boolean|integer
   */
  public function add($name, $icon, $formatBold = 0, $formatItalic = 0, $formatSmallCaps = 0,
                      $formatColour = '#ffffff')
  {
    $itemTagInsert = $this->db->query();
    $itemTagInsert->insert('bf_item_tags',
                           array(
                             'name' => $name,
                             'font_list_bold' => $formatBold,
                             'font_list_italic' => $formatItalic,
                             'font_list_small_caps' => $formatSmallCaps,
                             'icon_path' => $icon,
                             'font_list_colour' => $formatColour
                           )
                          )
                  ->execute();
    
    return $itemTagInsert->insertID;
  }
  
  /**
   * Modify an existing item tag.
   * @param integer $id The ID of the existing item tag.
   * @param string $name The unique name of the item tag.
   * @param string $icon The icon path to use for the item tag.
   * @param integer $formatBold Optionally enable bold list formatting. Default 0.
   * @param integer $formatItalic Optionally enable italic list formatting. Default 0
   * @param integer $formatSmallCaps Optionally enable small caps list formatting. Default 0.
   * @param string $formatColour Optionally specify a colour for list formatting. Default white.
   * @param string $masthead Optionally display the item tag on the Masthead/Homepage.
   * @return boolean|integer
   */
  public function modify($id, $name, $icon, $formatBold = 0, $formatItalic = 0, $formatSmallCaps = 0,
                         $formatColour = '#ffffff', $masthead = false)
  {
    $itemTagModify = $this->db->query();
    $itemTagModify->update('bf_item_tags',
                           array(
                             'name' => $name,
                             'font_list_bold' => $formatBold,
                             'font_list_italic' => $formatItalic,
                             'font_list_small_caps' => $formatSmallCaps,
                             'icon_path' => $icon,
                             'font_list_colour' => $formatColour,
                             'masthead' => $masthead
                           )
                          )
                  ->where('`id` = \'{1}\'', $id)
                  ->limit(1)
                  ->execute();
    
    return true;
  }
  
  /**
   * Remove an item tag.
   * Also remove it from all items.
   * @param integer $itemTagID The ID of the item tag to remove.
   * @return boolean
   */
  public function remove($itemTagID)
  {
    // IDs to Integer only
    $itemTagID = intval($itemTagID);
  
    // Strip from all items
    $this->db->delete('bf_item_tag_applications')
             ->where('item_tag_id = \'{1}\'', $itemTagID)
             ->execute();
                     
    // Remove actual tag
    $this->db->delete('bf_item_tags')
             ->where('id = \'{1}\'', $itemTagID)
             ->limit(1)
             ->execute();
                     
    return true;
  } 
}
?>