<?php
/**
 * Categories
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Categories extends API
{
  /**
   * Add a category (Top Level)
   * @param string $name The name of the category.
   * @param string $parentChildDisplayMode Optionally the mode of displaying parentised items, Default dropdowns.
   * @return boolean|integer The row ID in the categories table that was created.
   */
  public function addCategory($name, $parentChildDisplayMode = 'dropdowns')
  {
    // Remove tags
    $name = strip_tags($name);
    
    // Enum
    switch($parentChildDisplayMode)
    {
      case 'dropdowns':
      case 'table':
        // OK
        break;
        
      default:
        // Default mode:
        $parentChildDisplayMode = 'dropdowns';
        break;
    }
  
    $this->db->insert('bf_categories', array(
                       'name' => $name,
                       'parent_child_display_mode' => $parentChildDisplayMode
                     ))->execute();
                     
    return $this->db->insertId;
  }
  
  /**
   * Add a subcategory (2nd nested Level)
   * @param string $name The name of the subcategory
   * @param integer $parentID The ID of the parent category
   * @return boolean|integer The row ID in the subcategories table that was created.
   */
  public function addSubcategory($name, $parentID)
  {
    // Remove tags
    $name = strip_tags($name);
    $viewFile = strip_tags($viewFile);
    
    // Parent ID to Integer only
    $parentID = intval($parentID);
  
    $this->db->insert('bf_subcategories', array(
                       'name' => $name,
                       'category_id' => $parentID
                     ))->execute();
                     
    return $this->db->insertId;
  }
   
  /**
   * Remove a category and uncategorise the items inside it.
   * @param integer $categoryID The ID of the category
   * @return boolean
   */
  public function removeCategory($categoryID)
  {
    // IDs to Integer only
    $categoryID = intval($categoryID);
    
    // Move all items
    $this->db->update('bf_items', array(
                 'category_id' => -1
               ))
             ->where('category_id = \'{1}\'', $categoryID)
             ->execute(); 
    
    // Delete
    $this->db->delete('bf_categories')
             ->where('id = \'{1}\'', $categoryID)
             ->execute();      
                            
    return true;
  }
  
  /**
   * Make changes to a category
   * @param integer $categoryID The ID of the category to modify
   * @param string $name The new name for the category
   * @param integer $imageID Optionally the new Image ID.
   * @param string $parentChildDisplayMode Optionally the mode of displaying parentised items, Default dropdowns.
   * @param boolean $visible Optionally the category visibility, default true.
   * @param integer $groupID Optionally the category group ID, default none (-1).
   * @return boolean
   */
  public function modifyCategory($categoryID, $name, $imageID = '', $parentChildDisplayMode = 'dropdowns',
    $visible = true, $groupID = -1)
  {
    // IDs to Integer only
    $categoryID = intval($categoryID);
    
    // Build an array of modifications
    $modifications = array();
    $modifications['name'] = $name;
    
    // Enum
    switch($parentChildDisplayMode)
    {
      case 'dropdowns':
      case 'table':
        // OK
        break;
        
      default:
        // Default mode:
        $parentChildDisplayMode = 'dropdowns';
        break;
    }
    
    // Change parent/child display mode option
    $modifications['parent_child_display_mode'] = $parentChildDisplayMode;
    $modifications['visible'] = ($visible ? '1' : '0');
    $modifications['category_group_id'] = $groupID;
    
    if($imageID)
    {
      $modifications['image_id'] = $imageID;
    }
    
    // Modify
    $this->db->update('bf_categories', $modifications)
             ->where('id = \'{1}\'', $categoryID)
             ->limit(1)
             ->execute();     
                            
    return true;
  }
  
  /**
   * Remove a subcategory and unsubcategorise the items inside it.
   * @param integer $subcategoryID The ID of the subcategory
   * @return boolean
   */
  public function removeSubcategory($subcategoryID)
  {
    // IDs to Integer only
    $subcategoryID = intval($subcategoryID);
    
    // Move all items
    $this->db->update('bf_items', array(
                 'category_id' => -1
               ))
             ->where('subcategory_id = \'{1}\'', $subcategoryID)
             ->execute(); 
    
    // Delete
    $this->db->delete('bf_subcategories')
             ->where('id = \'{1}\'', $subcategoryID)
             ->execute();      
                            
    return true;
  }
  
  /**
   * Modify a subcategory, setting a new name
   * @param integer $subcategoryID The ID of the subcategory
   * @param integer $name The new name for the subcategory
   * @return boolean
   */
  public function modifySubcategory($subcategoryID, $name)
  {
    // IDs to Integer only
    $subcategoryID = intval($subcategoryID);
    
    // Rename
    $this->db->update('bf_subcategories', array(
                 'name' => $name
               ))
             ->where('`id` = \'{1}\'', $subcategoryID)
             ->execute(); 
 
    return true;
  }
}
?>