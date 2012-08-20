<?php
/**
 * STaff
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Staff extends API
{
  /**
   * Add a staff user.
   * @param string $name The username of the staff account.
   * @param string $password The password of the staff account.
   * @param string $email An email address for this staff account.
   * @param string $fullName The full name for the staff account.
   * @param string $description The description of the staff accout.
   * @param integer $profileID The Staff Profile ID of the staff account.
   * @param string $phoneMobile A mobile phone number.
   * @param boolean $supervisor Make the staff account a supervisor user.
   * @param array $notifications Any notification fields for the staff account.
   * @return boolean|integer The row ID in the users table that was created.
   */
  public function add($name, $password, $email, $fullName, $description, $profileID,
                      $phoneMobile, $supervisor, $notifications = array())
  {
    $this->db->insert('bf_admins', array_merge(array(
                       'name' => $name,
                       'description' => $description,
                       'password' => md5(BF_SECRET . $password),   // Preappended Salt
                       'email' => $email, 
                       'mobile_number' => $phoneMobile,
                       'profile_id' => $profileID,
                       'full_name' => $fullName,
                       'supervisor' => ($supervisor ? 1 : 0),
                       'last_login_timestamp' => -1,              // Never
                       'inventory_default_view' => '',
                     ), $notifications))->execute();
                     
    return $this->db->insertId;
  }
  
  /**
   * Modify a staff user.
   * @param integer $staffAccountID The ID of the staff account to modify.
   * @param string $name The username of the staff account.
   * @param string $password The password of the staff account.
   * @param string $email An email address for this staff account.
   * @param string $fullName The full name for the staff account.
   * @param string $description The description of the staff accout.
   * @param integer $profileID The Staff Profile ID of the staff account.
   * @param string $phoneMobile A mobile phone number.
   * @param boolean $supervisor Make the staff account a supervisor user.
   * @param array $notifications Any notification fields for the staff account.
   * @return boolean|integer The row ID in the users table that was created.
   */
  public function modify($staffAccountID, $name, $password, $email, $fullName, $description,
                         $profileID, $phoneMobile, $supervisor, $notifications)
  {
  
    // Build modification array
    $modifications = array(
                       'name' => $name,
                       'description' => $description,
                       'email' => $email, 
                       'mobile_number' => $phoneMobile,
                       'profile_id' => $profileID,
                       'full_name' => $fullName,
                       'supervisor' => ($supervisor ? 1 : 0)
                     );
                     
    if($password != '')
    {
      // Update password too with prepended salt value
      $modifications['password'] = md5(BF_SECRET . $password);
    }
      
    $this->db->update('bf_admins', array_merge($modifications, $notifications))
             ->where('`id` = \'{1}\'', $staffAccountID)
             ->limit(1)
             ->execute();
                     
    return $this->db->insertId;
  }
  
  /**
   * Add a staff profile
   * @param string $name The name for the staff profile.
   * @param boolean $canAccount The staff user can modify/view dealer accounts
   * @param boolean $canCategories The staff user can modify/view categories
   * @param boolean $canItems The staff user can modify/view inventory
   * @param boolean $canOrders The staff user can manipulate orders
   * @param boolean $canWebsite The staff user can modify website content
   * @param boolean $canSystem The staff user can modify system configuration
   * @param boolean $canLogin The staff user can log in
   * @param boolean $canStats The staff user can view statistics
   * @param boolean $canChat The staff user can use chat/IM
   * @param boolean $canData The staff user can import data files into the inventory
   * @return boolean|integer The row ID in the staff profiles table that was created.    
   */
  public function addProfile($name, $canAccount, $canCategories, $canItems,
                             $canOrders, $canWebsite, $canSystem, $canLogin, $canStats,
                             $canChat, $canData)
  {
    $this->db->insert('bf_admin_profiles', array(
                       'name' => $name,
                       'can_account' => ($canAccount ? 1 : 0),
                       'can_categories' => ($canCategories ? 1 : 0),
                       'can_items' => ($canItems ? 1 : 0),
                       'can_orders' => ($canOrders ? 1 : 0),
                       'can_website' => ($canWebsite ? 1 : 0),
                       'can_system' => ($canSystem ? 1 : 0),
                       'can_login' => ($canLogin ? 1 : 0),
                       'can_stats' => ($canStats ? 1 : 0),
                       'can_chat' => ($canChat ? 1 : 0),
                       'can_data' => ($canData ? 1 : 0)
                     ))
             ->execute();

    return $this->db->insertId;
  } 
  
  /**
   * Make changes to a staff profile
   * @param integer $staffProfileID The ID of the staff profile to modify.   
   * @param string $name The new name for the staff profile.
   * @param boolean $canAccount The staff user can modify/view dealer accounts
   * @param boolean $canCategories The staff user can modify/view categories
   * @param boolean $canItems The staff user can modify/view inventory
   * @param boolean $canOrders The staff user can manipulate orders
   * @param boolean $canWebsite The staff user can modify website content
   * @param boolean $canSystem The staff user can modify system configuration
   * @param boolean $canLogin The staff user can log in
   * @param boolean $canStats The staff user can view statistics
   * @param boolean $canChat The staff user can use chat/IM
   * @param boolean $canData The staff user can import data files into the inventory
   * @return boolean    
   */
  public function modifyProfile($staffProfileID, $name, $canAccount, $canCategories, $canItems,
                                $canOrders, $canWebsite, $canSystem, $canLogin, $canStats,
                                $canChat, $canData)
  {
    $this->db->update('bf_admin_profiles', array(
                       'name' => $name,
                       'can_account' => ($canAccount ? 1 : 0),
                       'can_categories' => ($canCategories ? 1 : 0),
                       'can_items' => ($canItems ? 1 : 0),
                       'can_orders' => ($canOrders ? 1 : 0),
                       'can_website' => ($canWebsite ? 1 : 0),
                       'can_system' => ($canSystem ? 1 : 0),
                       'can_login' => ($canLogin ? 1 : 0),
                       'can_stats' => ($canStats ? 1 : 0),
                       'can_chat' => ($canChat ? 1 : 0),
                       'can_data' => ($canData ? 1 : 0)
                     ))
             ->where('id = \'{1}\'', $staffProfileID)
             ->limit(1)
             ->execute();
                     
    return true;
  }                          
  
  /**
   * Remove a member of staff.
   * @param integer $staffID The ID of the staff user to remove.
   * @return boolean
   */
  public function remove($staffID)
  {
    // Remove the staff user
    $this->db->delete('bf_admins')
             ->where("`id` = '{1}'", $staffID)
             ->limit(1)
             ->execute();
          
    return true;
  }
  
  /**
   * Remove a staff profile
   * @param integer $staffProfileID The ID of the staff profile to remove.
   * @return boolean
   */
  public function removeProfile($staffProfileID)
  {
    // Remove the staff profile
    $this->db->delete('bf_admin_profiles')
             ->where("`id` = '{1}'", $staffProfileID)
             ->limit(1)
             ->execute();
             
    // Change staff profile IDs on this profile to -1
    $this->db->update('bf_admins', array(
                       'profile_id' => '-1'
                     ))
             ->where('profile_id = \'{1}\'', $staffProfileID)
             ->limit(1)
             ->execute();
          
    return true;
  }
}
?>