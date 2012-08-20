<?php
/**
 * Module: Data
 * Mode: Import
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

<script type="text/javascript">

  // Automatically show the scheduled UI?
  <?php
  
    if($BF->in('show_schedule_ui'))
    {
      ?>
      
  $(function() {
  
    showScheduleUI();
    
  });
      
      <?php
    }
  
  ?>
  
  $(function() {
  
    // Render datepicker
    $('#f_schedule_date').datepicker();
    
    // Check for Scheduled mode
    $('#f_import_time_now, #f_import_time_scheduled').change(function() {
      
      var mode = $("input[@name=f_import_time]:checked").val();
        
      if(mode == 'scheduled')
      {
        showScheduleUI();
      }
      else
      {
        hideScheduleUI();
      }
    });
  
  });
  
  function hideScheduleUI()
  {
    $('#f_import_time_now').attr('checked', 'checked'); 
    $('#schedule').hide();
    $('#next_step').html('Click the button to the right to upload the data and apply it now.');
    $('#submit_button').val('Upload and Apply');
    
    return true;
  }
  
  function showScheduleUI()
  {
    $('#f_import_time_scheduled').attr('checked', 'checked'); 
    $('#schedule').show();
    $('#next_step').html('Click the button to the right to upload the data and schedule it to be applied.');
    $('#submit_button').val('Upload and Schedule');
    
    // Scroll to the UI
    $.scrollTo('#schedule', 500);
  
    return true;
  }

</script>

<h1>Import Data</h1>
<br />

<form action="./?act=data&mode=import_do" method="post" enctype="multipart/form-data">

<div class="panel">
  <div class="title">Information about Importing Data</div>
  <div class="warning">
    <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
    <strong>Important</strong> - Please pay special attention to the contents of this panel.
    <br class="clear">
  </div>
  <div class="contents">
    <p>
      You need to make sure the spreadsheets you upload have the correct column headers.<br />
      For information about setting up a spreadsheet and to download a template spreadsheet file, 
      <a href="./?act=data&mode=import_help" title="Help with Importing" target="_blank" class="new">Click Here</a>
    
      <br /><br />
    
      Data file formats that you can upload include:
      Excel&reg; (.xls or .xlsx), XML (.xml), Comma/Tab Separated (.csv or .txt),
      Open Document Spreadsheet (.ods) and more formats.
    
    </p>
  </div>
</div>    

<br />

<div class="panel">
  <div class="title">Import Data</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
    <table class="fields">
      <tbody>
      
        <tr>
          <td class="key">
            <strong>Data File</strong><br />
            A data file from your computer to upload.
          </td>
          <td class="value">
            <input type="file" name="f_file" id="f_file" />
          </td>
        </tr>

        <tr>
          <td class="key">
            <strong>Data Application Time</strong><br />
            When should the imported data be applied to the inventory?
          </td>
          <td class="value">
            <div style="margin-bottom: 6px;">
            <input type="radio" checked="checked" name="f_import_time" id="f_import_time_now" value="now" /> &nbsp; Now</div>
            
            <input type="radio" name="f_import_time" id="f_import_time_scheduled" value="scheduled" /> &nbsp; At a later date
          </td>
        </tr>

        <tr class="last">
          <td class="key">
            <br />
            <strong>Create New Items</strong><br />
            Automatically create any SKUs that do not exist.<br /><br />
            
            <span class="grey" style="font-weight: bold;">Note:</span><br />
            <span class="grey">This can lead to an untidy inventory if your upload data is not strict.</span><br />
            <span class="grey">It is not possible to add Pricing Overrides while using this option.</span><br /><br />
          </td>
          <td class="value">
            <input type="checkbox" name="f_new_skus" id="f_new_skus" value="1" />
          </td>
        </tr>


      </tbody>
    </table>
        
  </div>
</div>

<br />

<div class="panel" id="schedule" style="display:none; margin-bottom: 15px">
  <div class="title">Create an Import Schedule</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents fieldset">
    
    <table class="fields">
      <tbody>
    
        <tr>
          <td class="key">
            <strong>Scheduled Import Name</strong><br />
            Your reference for the Scheduled Import.<br />
            <span class="grey">E.g. <?php print date('F'); ?> Offer</span>
          </td>
          <td class="value">
            <input type="text" name="f_schedule_name" id="f_schedule_name" />
          </td>
        </tr>
    
        <tr>
          <td class="key">
            <strong>Scheduled Date</strong><br />
            Choose a date on which the data will be applied.
          </td>
          <td class="value">
            <input type="text" style="width: 100px;" name="f_schedule_date" id="f_schedule_date" />
          </td>
        </tr>
  
        <tr>
          <td class="key">
            <strong>Scheduled Time</strong><br />
            Choose the time at which the data will be applied.<br />
            <span class="grey">24 Hour: &nbsp; 0 - Midnight &nbsp; 12 - Noon</span>
          </td>
          <td class="value">
              
            <select name="f_schedule_time_hours">
              <?php
                for($hour = 0; $hour < 24; $hour ++)
                {
                  $paddedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
                  print '              <option value="' . $hour . '">' . $paddedHour . '</option>';
                }
              ?>
            </select>
            
            &nbsp; Hours &nbsp; &nbsp;
            
            <select name="f_schedule_time">
              <?php
                for($minute = 0; $minute < 60; $minute ++)
                {
                  print '              <option value="' . $minute . '">' . $minute . '</option>';
                }
              ?>
            </select>
            
            &nbsp; Minutes
            
          </td>
        </tr>
        
        <tr class="last">
          <td class="key">
            <strong>Notifications</strong><br />
            Choose how you wish to be notified when the data is applied.
          </td>
          <td class="value">
           
            <table class="suboptions">
            <thead>

            
              <tr class="header">
                <td>
                  <strong>Notification Type</strong>
                </td>
                <td class="value">
                  <strong>Enable</strong>
                </td>
              </tr>

              
            </thead>
          
            <tbody>
      
              <tr>
                <td style="background: #dfdfdf;">
                  Email</td>
                <td class="value">
                  <input type="checkbox" name="f_schedule_notify_email" value="1" />
                </td>
              </tr>
               
              <?php
                if($BF->admin->getInfo('mobile_number') != '')
                {
              ?>   
                      
              <tr>
                <td style="background: #dfdfdf;">
                  SMS</td>
                <td class="value">
                  <input type="checkbox" name="f_schedule_notify_sms" value="1" />
                </td>
              </tr>
              
              <?php
                }
              ?>
    
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
      <strong id="next_step">Click the button to the right to upload the data and apply it now.</strong>
    </p>
    <input id="submit_button" class="submit ok" type="submit" style="float: right;" value="Upload and Apply" />
    <br class="clear" />
  </div>
</div>

</form>