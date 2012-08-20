<?php
/**
 * Module: System
 * Mode: Modify Staff Account
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

//
// Permissions:
// Need to be supervisor.
//
if(!$BF->admin->isSupervisor)
{
  print $BF->admin->notSupervisor();
  exit();
}


// Load the admin to modify
$adminID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_admins')
           ->where('id = \'{1}\'', $adminID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  header('Location: ./?act=system&mode=admins');
  exit();
}

$adminRow = $BF->db->next();

?>

<script type="text/javascript">

  $(function() {
  
    $('#dd_f_profile').change(function() {
      $('#profile_modify').attr('href', '/acp/?act=system&mode=profiles_modify&id=' + $(this).val());
    }).change();

  });

</script>

<h1><?php print $adminRow->full_name; ?></h1>
<br />

<form action="./?act=system&mode=admins_modify_do" method="post">
<input type="hidden" value="<?php print $adminRow->id; ?>" name="f_id" id="f_id" />

<div class="panel">
  <div class="title">Staff Account Information</div>
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
            <strong>Account Name</strong><br />
            A unique username for the staff account.
          </td>
          <td class="value">
            <input value="<?php print $adminRow->name; ?>" name="f_name" id="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
        
        <tr>
          <td class="key">
            <strong>Email Address</strong><br />
            A valid email address for this staff account.<br />
          </td>
          <td class="value">
            <input value="<?php print $adminRow->email; ?>" name="f_email" id="f_email" type="text" style="width: 200px;" />
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Full Name</strong><br />
            The full name of the person that holds this account.<br />
          </td>
          <td class="value">
            <input value="<?php print $adminRow->full_name; ?>" name="f_full_name" id="f_full_name" type="text" style="width: 200px;" />
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <strong>Description</strong><br />
            A description of the role of this account.<br />
          </td>
          <td class="value">
            <input value="<?php print $adminRow->description; ?>" name="f_description" id="f_description" type="text" style="width: 200px;" />
          </td>
        </tr>
        
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Password Change</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last" id="change_password">
          <td colspan="2" style="text-align: center;" class="value">
            <span class="button">
              <a onclick="$('#change_password').hide(); $('#password_box').show(); $('#f_password').focus();">
                <span class="img" style="background-image:url(/acp/static/icon/key.png)">&nbsp;</span>
                Change Password...
              </a>
            </span>
          </td>
        </tr>
        <tr class="last" style="display: none;" id="password_box">
          <td class="key">
            <strong>Password</strong><br />
            A password for the staff account.<br />
            <span class="grey">Think secure! Choose a <abbr title="Mix of alphanumeric and punctuation, 6 characters or more.">strong</abbr> password.</span>
          </td>
          <td class="value">
            <input name="f_password" id="f_password" type="password" style="width: 200px;" />
          </td>
        </tr>
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Permissions</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Staff Profile</strong><br />
            A profile that determines which actions this account can perform.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_admin_profiles')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_profile', $query, 'id', 'name');
  $dropDown->setOption('defaultSelection', $adminRow->profile_id);
  print $dropDown->render();

?>

            &nbsp;
            <a href="#" id="profile_modify" class="new" target="_blank">Modify Profile</a>

          </td>
        </tr>  
        
<?php
  
  if($adminRow->name != 'root')
  {
  
    // Supervisor is optional

?>   
        <tr class="last">
          <td class="key">
            <strong>Supervisor Account</strong><br />
            Allow this account to modify and create other staff accounts.
          </td>
          <td class="value">
            <input<?php print Tools::booleanToCheckState($adminRow->supervisor); ?>
              type="checkbox" name="f_supervisor" value="1" />
          </td>
        </tr>      
<?php
        
  }
  else
  {
  
?>
        <tr class="last">
          <td class="key">
            <strong>Supervisor Account</strong><br />
            This account (root) must be a supervisor.
          </td>
          <td class="value">
            <input type="checkbox" checked="checked" name="f_dummy_supervisor" disabled="disabled" value="1" />
            <input type="hidden" name="f_supervisor" value="1" />
          </td>
        </tr>    
<?php
  
  }
  
?>
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Notifications</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
      
        <tr style="height: 40px">
          <td class="key" style="width: 250px;">
          </td>
          <td class="value">
            <table style="width: 100%;">
              <tr style="height: 40px" class="last">
                <td style="width: 25%; text-align: center;">
                  <img src="./static/icon/slash.png" class="middle" /> &nbsp; None
                </td>
                <td style="width: 25%; text-align: center;">
                  <img src="./static/icon/ui-tooltip-balloon.png" class="middle" /> &nbsp; ACP Popup
                </td>
                <td style="width: 25%; text-align: center;">
                  <img src="./static/icon/mail.png" class="middle" /> &nbsp; Email
                </td>
                <td style="width: 25%; text-align: center;">
                  <img src="./static/icon/mobile-phone-cast.png" class="middle" /> &nbsp; SMS + Email
                </td>
              </tr>
            </table>
          </td>
        </tr>   
      
<?php

  // Load notification options
  $staffNotificationFields = new PropertyList();
  $fields = $staffNotificationFields->parseFile(
    BF_ROOT . '/acp/definitions/staff_notification_fields.plist');
    
  // Failure?
  if(!$fields)
  {
    $BF->log('Unable to load /acp/definitions/staff_notification_fields.plist');
  }  
  
  foreach($fields as $key => $value)
  {

?>
     
        <tr style="height: 40px">
          <td class="key" style="width: 250px;">
            <strong>When <?php print lcfirst($value); ?>..</strong>
          </td>
          <td class="value">
            <table style="width: 100%;">
              <tr style="height: 40px" class="last">
                <td style="width: 25%; text-align: center;">
                  <input <?php print Tools::booleanToCheckState($adminRow->{'notification_' . $key} == 0); ?>
                  type="radio" value="none" name="no_<?php print $key; ?>" />
                </td>
                <td style="width: 25%; text-align: center;">
                  <input <?php print Tools::booleanToCheckState($adminRow->{'notification_' . $key} == 1); ?>
                  type="radio" value="1" name="no_<?php print $key; ?>" />
                </td>
                <td style="width: 25%; text-align: center;">
                  <input <?php print Tools::booleanToCheckState($adminRow->{'notification_' . $key} == 2); ?>
                  type="radio" value="2" name="no_<?php print $key; ?>" />
                </td>
                <td style="width: 25%; text-align: center;">
                  <input <?php print Tools::booleanToCheckState($adminRow->{'notification_' . $key} == 3); ?>
                  type="radio" value="3" name="no_<?php print $key; ?>" />
                </td>
              </tr>
            </table>
          </td>
        </tr>    

<?php

  }
  
?>
          
      </tbody>
    </table>
    
    <br />
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Other Details</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
         <tr class="last">
          <td class="key">
            <strong>Mobile Number</strong><br />
            A mobile telephone number for this person.
          </td>
          <td class="value">
            <input value="<?php print $adminRow->mobile_number; ?>"
              name="f_phone_mobile" id="f_phone_mobile" type="text" style="width: 200px;" />
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
      <strong>Click the button to the right to save this staff account.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Save and Exit" />
    <br class="clear" />
  </div>
</div>

</form>