<?php
/**
 * Module: Data
 * Mode: Import Do
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

// Get new item create request value
$createNewSKUs = $BF->inInteger('f_new_skus') == 1;

// Switch based on the application time
switch($BF->in('f_import_time'))
{

  case 'scheduled':
    
    // Fix time values
    $BF->setIn('f_schedule_date', str_replace('-', '.', $BF->in('f_schedule_date')));

    // Obtain time
    $time = str_pad($BF->in('f_schedule_time_hours'), 2, '0', STR_PAD_LEFT) . ':' . 
            str_pad($BF->in('f_schedule_time'), 2, '0', STR_PAD_LEFT);

    // Build the validation array
    $validation = array(
      
        'schedule_name' => array(
        
                   'validations' => array(
                                     'unique' => array('bf_scheduled_imports'),
                                     'done' => array()
                                    ),
                                    
                   'value' => $BF->in('f_schedule_name'),
                   
                   'name' => 'Schedule Name'
                       
                  ),
                       
        'schedule_date' => array(
        
                       'validations' => array(
                                         'done' => array(),
                                         'futureDate' => array($time)
                                        ),
                                        
                       'value' => $BF->in('f_schedule_date'),
                       
                       'name' => 'Schedule Date and Time'
                           
                      )
    
    );
    
    // Check each field
    foreach($validation as $fieldName => $fieldData)
    {
      // Create a validator
      $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);
    
      // Check
      if($validator->batch($fieldData['validations'])->failed())
      {
        // Failed - Pack up fields and redirect
        $BF->admin->packAndRedirect('./?act=data&mode=import&show_schedule_ui=1',
                                        $fieldName, (string)$validator);
                                        
        exit();
      }
    }
    
    // Get the time value
    $timestamp = strtotime($BF->in('f_schedule_date') . ' ' . $time);
    
    // Get the name of the import
    $importName = $BF->in('f_schedule_name');
    
    // Get any notifications
    $notifySMS = $BF->in('f_schedule_notify_sms');
    $notifyEmail = $BF->in('f_schedule_notify_email');
    
    // Get details
    $temporaryFile = $_FILES['f_file']['tmp_name'];
    $temporaryName = $_FILES['f_file']['name'];
        
    // Upload the file
    $result = $BF->admin->api('Files')
                            ->upload($temporaryFile, $temporaryName, false);
                  
    if(!$result)
    {
      $BF->admin->packAndRedirect('./?act=data&mode=import&show_schedule_ui=1',
                                      'file', 'Could not upload the file.');
                                      
      exit();
    }
    
    // Obtain path
    $path = $result;

    // Check it can be loaded
    $BF->admin->api('Data')
                  ->initiate();
                  
    $dataResult = $BF->admin->api('Data')
                            ->canImport($result);
    
    if($dataResult !== true)
    {
      $BF->admin->packAndRedirect('./?act=data&mode=import&show_schedule_ui=1',
                                      'file', (string)$dataResult);
                                      
      exit();
    }
    
    // Create the schedule
    $result = $BF->admin->api('Data')
                            ->schedule($importName,
                                       $timestamp,
                                       $path,
                                       $createNewSKUs,
                                       $notifySMS,
                                       $notifyEmail,
                                       $BF->admin->AID);

    if($result !== false)
    {
      $BF->admin->notifyMe('OK', 'The Scheduled Import ' . $BF->in('f_schedule_name') . ' was added.');
      header('Location: ./?act=data&mode=scheduled');
    }
        
    break;
    
  case 'now':
  
    // Get details
    $temporaryFile = $_FILES['f_file']['tmp_name'];
    $temporaryName = $_FILES['f_file']['name'];
        
    // Upload the file
    $result = $BF->admin->api('Files')
                        ->upload($temporaryFile, $temporaryName, 3600);
                  
    if(!$result)
    {
      $BF->admin->packAndRedirect('./?act=data&mode=import',
                                      'file', 'Could not upload the file.');
                                      
      exit();
    }
    
    // Check it can be loaded
    $BF->admin->api('Data')
                  ->initiate();
                  
    $BF->log('Data', $temporaryName . ': Validating import...');
    $dataResult = $BF->admin->api('Data')
                            ->canImport($result);
                            
    $BF->log('Data', $temporaryName . ': Validation complete.');
    
    if($dataResult !== true)
    {
      $BF->log('Data', $temporaryName . ' cannot be imported.');
      $BF->admin->packAndRedirect('./?act=data&mode=import',
                                  'file', (string)$dataResult);
                                      
      exit();
    }
    
    // File is OK
    $BF->log('Data', $temporaryName . ': Starting import...');
    $importResult = $BF->admin->api('Data')
                              ->import($result, $createNewSKUs);
    
    // Notify
    $BF->admin->notifyMe('OK', 'Upload Successful.');
    
    break;
    
}

?>

<h1>Import Complete</h1>
<br />

<div class="panel">
  <div class="title">Import Summary</div>
  <div class="contents">
    <h3><?php print $importResult['total']; ?> SKUs were found in <?php print Tools::safe($temporaryName); ?></h3>

    <table style="margin: 15px 0px 0px 12px;">
    
      <tbody>
        
        <tr>
          <td style="width: 180px;">Updated</td>
          <td style="font-weight: bold;">
            <abbr title="These SKUs were found and the data was applied to them."><?php print count($importResult['updated']); ?></abbr>
          </td>
        </tr>

        <tr>
          <td>Created</td>
          <td style="font-weight: bold;">
            <abbr title="These SKUs were created."><?php print count($importResult['created']); ?></abbr>
          </td>
        </tr>
        
        <!--
        <tr>
          <td>New User Pricing Overrides</td>
          <td style="font-weight: bold;">
            <abbr title="These SKUs were affected by new pricing overrides."><?php print count($importResult['user']); ?></abbr>
          </td>
        </tr>-->
        
        <tr>
          <td>No Action Taken</td>
          <td style="font-weight: bold;">
            <abbr title="These SKUs were not found in the Inventory and were not created or updated."><?php print count($importResult['noaction']); ?></abbr>
          </td>
        </tr>

      </tbody>
    
    </table>
    
    <br />
    
  </div>
</div>    

<?php
  
  if(count($importResult['noaction']) > 0)
  {

?>

<br />

<div class="panel">
  <div class="title">No Action Taken</div>
  <div class="contents">
    
    <p>
      These SKUs were not found in the Inventory and were not created or updated.
    </p>
    
    <div style="padding: 10px; margin: 10px; overflow: auto; height: 170px; border: 1px solid #afafaf; background: #fff">
      <table style="margin: 0px 0px 0px 10px;">
        <tbody>
      <?php
      
        foreach($importResult['noaction'] as $SKU => $reason)
        {
          ?>
          
      <tr>
        <td style="width: 70px;"><strong style="color: #f00;"><?php print $SKU; ?></strong></td>
        <td><?php print $reason; ?></td>
      </tr>
          
          <?php
        }
      
      ?>
      
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php

  }

  if(count($importResult['created']) > 0)
  {

?>

<br />

<div class="panel">
  <div class="title">Created</div>
  <div class="contents">

    <p>
      These SKUs were not found in the Inventory and were created automatically.
    </p>
    
    <div style="padding: 10px; margin: 10px; overflow: auto; height: 170px; border: 1px solid #afafaf; background: #fff">
      <table style="margin: 0px 0px 0px 10px;">
        <tbody>
      <?php
      
        foreach($importResult['created'] as $SKU => $reason)
        {
          ?>
          
      <tr>
        <td style="width: 70px;"><strong><?php print $SKU; ?></strong></td>
        <td><?php print $reason; ?></td>
      </tr>
          
          <?php
        }
      
      ?>
      
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php

  }
  
?>


<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to return to the Import Data interface.</strong>
    </p>
    <input class="submit ok" type="button" onclick="window.location='./?act=data';" style="float: right;" value="OK" />
    <br class="clear" />
  </div>
</div>