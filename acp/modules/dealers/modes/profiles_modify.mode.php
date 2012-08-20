<?php
/**
 * Module: Dealers
 * Mode: Profiles Modify
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

// Load the dealer profile to modify
$dealerProfileID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_user_profiles')
           ->where('id = \'{1}\'', $dealerProfileID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=dealers&mode=profiles');
  exit();
}

$dealerProfileRow = $BF->db->next();

// Convert each of the can_ values to checkbox check states
foreach($dealerProfileRow as $key => $value)
{
  if(strpos($key, 'can_') === 0)
  {
    $dealerProfileRow->{$key} = Tools::booleanToCheckState($value);
  }
}

?>

<h1><?php print $dealerProfileRow->name; ?></h1>
<br />

<form action="./?act=dealers&mode=profiles_modify_do&id=<?php print $dealerProfileRow->id; ?>" method="post">

<div class="panel">
  <div class="title">Profile Information</div>
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
        <tr class="last">
          <td class="key">
            <strong>Name</strong><br />
            A unique name for the profile.
          </td>
          <td class="value">
            <input value="<?php print $dealerProfileRow->name; ?>" name="f_name" id="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Profile Permission Details</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Permissions</strong><br />
            These options specify what actions dealers with this profile may perform.<br />
            <span class="grey">These permissions may be modified at a later date.</span>
          </td>
          <td class="value">
            <table class="suboptions" style="width: 100%;">
            
              <thead>
              
                <tr class="header">
                  <td>
                    <strong>Dealers with this profile may...</strong>
                  </td>
                  <td class="value" style="text-align: center;">
                    <strong>Allowed?</strong>
                  </td>
                </tr>
                
              </thead>
            
              <tbody>
                
                <tr>
                  <td style="background: #fff;">
                    See RRP Prices
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_see_rrp; ?> type="checkbox" name="f_see_rrp" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    See Prices
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_see_prices; ?> type="checkbox" name="f_see_prices" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Order at Wholesale Prices
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_wholesale; ?> type="checkbox" name="f_see_wholesale" value="1" />
                  </td>
                </tr>
                
                <tr>
                  <td style="background: #fff;">
                    Always use Pro Net Prices
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_pro_rate; ?> type="checkbox" name="f_pro_rate" value="1" />
                  </td>
                </tr>

                <tr>
                  <td style="background: #fff;">
                    Place Orders
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_order; ?> type="checkbox" name="f_order" value="1" />
                  </td>
                </tr>
              
                <tr>
                  <td style="background: #fff;">
                    Submit Questions
                  </td>
                  <td class="value">
                    <input<?php print $dealerProfileRow->can_question; ?> type="checkbox" name="f_question" value="1" />
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
      <strong>Click the button to the right to save this dealer profile.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>