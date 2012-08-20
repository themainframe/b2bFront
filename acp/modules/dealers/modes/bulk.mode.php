<?php
/**
 * Module: Dealers
 * Mode: Bulk Mail
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

  // Attach WYSIWYG Editor
  $(function() {
    CKEDITOR.replace( 'f_content' );
  });
  
</script>
<script type="text/javascript">

  /**
   * Confirmed form submission
   * @var boolean
   */
  var confirmSubmission = false;
  
  var previewMode = false;
  
  $(function() { 
    
    // Update preview link
    $('#f_template').change(function() {
      $('#template_preview').attr('href', 
        './external/preview_mail_template.external.php?name=' + $(this).val());
    });
    
    // Update on load
    $('#f_template').change();
    
    // Form submission confirmation
    $('form').submit(function() {
      if(confirmSubmission || previewMode)
      {
        return true;
      }
      
    $('form').attr('action', './?act=dealers&mode=bulk_send_do')
             .attr('target', '');

      
      // Show confirmation
      confirmation('Are you sure you want to send this message to <strong>all dealers</strong>?', function() { 
        confirmSubmission = true; $('form').submit();
      });
      
      return false;
    });
    
  });
  
  function preview()
  {
    previewMode = true;
    $('form').attr('action', './?act=dealers&mode=bulk_preview')
             .attr('target', '_blank');
    $('form').submit();
    previewMode = false;
  }
  
</script>

<h1>Bulk Email Dealers</h1>
<br />

<div class="panel">
  <div class="title">Important Information</div>
  <div class="warning">
    <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
    <strong>Important</strong> - Please pay special attention to the contents of this panel.
    <br class="clear">
  </div>
  <div class="contents">
    <p>
      This feature allows you to send email to all of the dealers on the website.<br />
      You should use this feature in moderation to avoid your messages constituting unsolicited Email.
    </p>
  </div>
</div>    
     
<br />

<form method="post" action="./?act=dealers&mode=bulk_send_do">

<div class="panel">
  <div class="title">Email Template</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Template</strong><br />
            The template to use for the message.
          </td>
          <td class="value">
            <select name="f_template" id="f_template" />
            <?php
              
              // Get the file listing
              $fileListing = Tools::listDirectory(BF_ROOT . '/extensions/mail_templates/');
              
              // No templates?
              if(count($fileListing) == 0)
              {
                $BF->log('Mail Templates', 'No mail templates installed or /extensions/ missing.');
                $BF->go('./?act=error');
              }
              
              // Load XML for each
              foreach($fileListing as $file)
              {
                $XMLfile = BF_ROOT . '/extensions/mail_templates/' . $file . '/template.xml';
                $XMLdata = simplexml_load_file($XMLfile);
                print '              <option value="' . $file . '">' . 
                      $XMLdata->description . '</option>' . "\n";
              }
              
            ?>
            </select> &nbsp; 
            <a href="./external/preview_mail_template.external.php" title="Preview" target="_blank"
               id="template_preview" class="new">Preview</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>  
     
<br />

<div class="panel">
  <div class="title">Bulk Email Parameters</div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Subject</strong><br />
            The subject of the email message.
          </td>
          <td class="value">
            <input name="f_subject" id="f_subject" type="text" style="width: 260px;" />
          </td>
        </tr>
        <tr class="last">
          <td class="key">
            <strong>From Address</strong><br />
            The 'From:' location of the email message.<br />
            <span class="grey">These options can be changed in
              <a href="./?act=system&mode=config_modify&id=2" class="new" target="_blank">Mail Settings</a>.</span>
          </td>
          <td class="value">
            <tt>
              <?php print $BF->config->get('com.b2bfront.mail.from', true); ?>
              &nbsp;&lt;<?php print $BF->config->get('com.b2bfront.mail.from-address', true); ?>&gt;
            </tt>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>  

<br />

<div class="panel">
  <div class="title">Content</div>
  <div class="contents" style="padding: 2px 0 0 0 ;">
    
    <!-- Description -->

    <textarea id="f_content" name="f_content" style="width:100%; height: 200px; border: 0; background:transparent;"></textarea>    
    
    <div style="padding: 5px;">
      <a href="#" onclick="preview();" class="new">Click here to preview the email without sending.</a>
    </div>
        
  </div>
  
</div>


<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to send this message to all dealers now.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Send..." />
    <br class="clear" />
  </div>
</div>

</form>