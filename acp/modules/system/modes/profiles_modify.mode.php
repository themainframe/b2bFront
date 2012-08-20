<?php
/**
 * Module: System
 * Mode: Staff Profiles Modify
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

// Load the staff profile to modify
$staffProfileID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_admin_profiles')
           ->where('id = \'{1}\'', $staffProfileID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=system&mode=profiles');
  exit();
}

$staffProfileRow = $BF->db->next();

// Convert each of the can_ values to checkbox check states
foreach($staffProfileRow as $key => $value)
{
  if(strpos($key, 'can_') === 0)
  {
    $staffProfileRow->{$key} = Tools::booleanToCheckState($value);
  }
}

?>

<h1><?php print $staffProfileRow->name; ?></h1>
<br />

<form action="./?act=system&mode=profiles_modify_do&id=<?php print $staffProfileRow->id; ?>" method="post">

<div class="panel">
  <div class="title">Staff Profile Information</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Name</strong><br />
            A unique name for the profile.
          </td>
          <td class="value">
            <input value="<?php print $staffProfileRow->name; ?>" name="f_name" id="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
        <tr class="last">
          <td class="key">
            <strong>Enabled</strong><br />
            Allow staff with this profile to log in to the
            <abbr title="Admin Control Panel">ACP</abbr>
          </td>
          <td class="value">
            <input<?php print $staffProfileRow->can_login; ?> type="checkbox" name="f_login" value="1" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Staff Profile Permission Details</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Permissions</strong><br />
            These options specify what actions staff with this profile may perform.
          </td>
          <td class="value">
            <table class="suboptions" style="width: 100%;">
            
              <thead>
              
                <tr class="header">
                  <td>
                    <strong>Staff with this profile may...</strong>
                  </td>
                  <td class="value" style="text-align: center;">
                    <strong>Allowed?</strong>
                  </td>
                </tr>
                
              </thead>
            
              <tbody>
                
                <tr>
                  <td style="background: #fff;">
                    Modify dealers
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_account; ?> type="checkbox" name="f_account" value="1" />
                  </td>
                </tr>
                
                <tr>
                  <td style="background: #fff;">
                    Modify categories/subcategories
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_categories; ?> type="checkbox" name="f_categories" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Modify inventory items
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_items; ?> type="checkbox" name="f_items" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Process/modify orders
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_orders; ?> type="checkbox" name="f_orders" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Modify website content
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_website; ?> type="checkbox" name="f_website" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Modify system configuration
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_system; ?> type="checkbox" name="f_system" value="1" />
                  </td>
                </tr>
              
                <tr>
                  <td style="background: #fff;">
                    Access statistics
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_stats; ?> type="checkbox" name="f_stats" value="1" />
                  </td>
                </tr>  

                <tr>
                  <td style="background: #fff;">
                    Import Data
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_data; ?> type="checkbox" name="f_data" value="1" />
                  </td>
                </tr> 

                <tr>
                  <td style="background: #fff;">
                    Can use Live Chat / IM
                  </td>
                  <td class="value">
                    <input<?php print $staffProfileRow->can_chat; ?> type="checkbox" name="f_chat" value="1" />
                  </td>
                </tr> 
              
    
              
              </tbody>
            </table>

          </td>
        </tr>      
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to save this staff profile.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>