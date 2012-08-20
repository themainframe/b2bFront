<?php
/**
 * Module: Dealers
 * Mode: Approve/Decline a Request
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_users')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=dealers&mode=browse');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<script type="text/javascript">

  $(function() {
  
    $('#dd_f_dealer_profile').change(function() {
      $('#profile_modify').attr('href', '/acp/?act=dealers&mode=profiles_modify&id=' + $(this).val());
    }).change();

  });

</script>

<h1>Request for an Account from <?php print $row->description; ?></h1>
<br />


<div class="panel">
  <div class="contents">
    <p>
      In this view you can review the information this person has provided then either:
    </p>
    <ul style="list-style: circle; margin-left: 30px;">
      <li>Approve the request and provide the person with a username and password.</li>
      <li>Decline the request and delete this information.</li>
    </ul>
    <br />
  </div>
</div>    
     
<br />

<form action="./?act=dealers&mode=unapproved_approve_do" method="post">
<input type="hidden" name="f_id" value="<?php print $row->id; ?>" />

<div class="panel">
  <div class="title">Request Information</div>
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
            <strong>Email Address</strong><br />
            A valid email address for this dealer.<br />
          </td>
          <td class="value">
            <input value="<?php print $row->email; ?>" name="f_email" id="f_email" type="text" style="width: 200px;" />
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <strong>Description</strong><br />
            The person or company that owns this account.<br />
          </td>
          <td class="value">
            <input value="<?php print $row->description; ?>" name="f_description" id="f_description" type="text" style="width: 200px;" />
          </td>
        </tr>
        
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel">
  <div class="title">Other Details</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr>
          <td class="key">
            <strong>Account Number</strong><br />
            An account number reference on another system.
          </td>
          <td class="value">
            <input value="<?php print $row->account_code; ?>" name="f_account_code" id="f_account_code" type="text" style="width: 100px;" />
          </td>
        </tr> 

        <tr>
          <td class="key">
            <strong>Street Address</strong><br />
            The street address where this dealer is based.
          </td>
          <td class="value">
            <table class="suboptions" style="border: 0;">
              <tbody>
                <tr>
                  <td style="width: 100px;">Building / Number</td>
                  <td><input value="<?php print $row->address_building; ?>" 
                  name="f_address_building" id="f_address_building" type="text" style="width: 200px;" /></td>
                </tr>
                <tr>
                  <td style="width: 100px;">Street</td>
                  <td><input value="<?php print $row->address_street; ?>" 
                  name="f_address_street" id="f_address_street" type="text" style="width: 200px;" /></td>
                </tr>
                <tr>
                  <td style="width: 100px;">Town / City</td>
                  <td><input value="<?php print $row->address_city; ?>" 
                  name="f_address_city" id="f_address_city" type="text" style="width: 200px;" /></td>
                </tr>
                <tr>
                  <td style="width: 100px;">Postcode</td>
                  <td><input value="<?php print $row->address_postcode; ?>" 
                  name="f_address_postcode" id="f_address_postcode" type="text" style="width: 100px;" /></td>
                </tr>
              </tbody>
            </table>
            
          </td>
        </tr>
      
        <tr>
          <td class="key">
            <strong>Landline Number</strong><br />
            A landline telephone number for the dealer.
          </td>
          <td class="value">
            <input value="<?php print $row->phone_landline; ?>" name="f_phone_landline" id="f_phone_landline" type="text" style="width: 200px;" />
          </td>
        </tr>  
        
        <tr>
          <td class="key">
            <strong>Mobile Number</strong><br />
            A mobile telephone number for the dealer.
          </td>
          <td class="value">
            <input value="<?php print $row->phone_mobile; ?>" name="f_phone_mobile" id="f_phone_mobile" type="text" style="width: 200px;" />
          </td>
        </tr> 
                
        <tr>
          <td class="key">
            <strong>Website URL</strong><br />
            A website address for the dealer.
          </td>
          <td class="value">
            <input value="<?php print $row->url; ?>" name="f_url" id="f_url" type="text" style="width: 200px;" />
          </td>
        </tr>  
        
        
        <tr>
          <td class="key">
            <strong>Tagline / Slogan</strong><br />
            A short tagline or slogan for the dealer.
          </td>
          <td class="value">
            <input value="<?php print $row->slogan; ?>" name="f_slogan" id="f_slogan" type="text" style="width: 200px;" />
          </td>
        </tr> 
        
        
        <tr>
          <td class="key">
            <strong>Locale</strong><br />
            The default locale for this dealer.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_locales')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_locale', $query, 'id', 'name');
  $dropDown->setOption('defaultSelection', $row->locale_id);
  print $dropDown->render();

?>
          </td>
        </tr> 
        
        <tr class="last">
          <td class="key">
            <strong>Bulk Email Exclusion</strong><br />
            Exclude this dealer from bulk Emails.
          </td>
          <td class="value">
            <input <?php print Tools::booleanToCheckState(! $row->include_in_bulk_mailings); ?> 
            type="checkbox" id="f_bulk_exclude" name="f_bulk_exclude" value="1" />
          </td>
        </tr> 
             
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #95504b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #95504b;">
      <strong>If you do not wish to continue, click the button to the right to decline this request now.</strong>
    </p>
    <input class="submit bad" type="submit" style="float: right;" value="Decline Request" />
    <br class="clear" />
  </div>
</div>


<br />

<div class="panel">
  <div class="title">Account Information</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <p>
      <br />
      If you wish to approve this request, you need to provide a username and password.<br />
      Type a username and password below.
      <br /><br />
    </p>
    <table class="fields">
      <tbody>
        <tr style="border-top: 1px solid #cfcfcf;">
          <td class="key">
            <strong>User Name</strong><br />
            A unique username for the dealer.
          </td>
          <td class="value">
            <input name="f_name" id="f_name" type="text" style="width: 200px;" />
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Password</strong><br />
            A password for the dealer.<br />
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
  <div class="title">Discounting</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Discount Band</strong><br />
            A band that determines how much discount should be applied to specific items and categories.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_user_bands')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_dealer_band', $query, 'id', 'description');
  $dropDown->setOption('defaultSelection', $row->band_id);
  print $dropDown->render();

?>

            &nbsp;
            <a href="http://127.0.0.1/acp/?act=dealers&mode=matrix" class="new" target="_blank">Modify Band</a>

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
            <strong>Profile</strong><br />
            A dealer profile that dictates the parts of the site the dealer can access.
          </td>
          <td class="value">
<?php

  // Query the database for Classifications
  $query = $BF->db->query();
  $query->select('*', 'bf_user_profiles')
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_dealer_profile', $query, 'id', 'name');
  $dropDown->setOption('defaultSelection', $row->profile_id);
  print $dropDown->render();

?>

            &nbsp;
            <a href="#" id="profile_modify" class="new" target="_blank">Modify Profile</a>

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
      <strong>Click the button to the right to create a dealer account now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Approve Request" />
    <br class="clear" />
  </div>
</div>

</form>