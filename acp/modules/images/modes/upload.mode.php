<?php
/**
 * Module: Images
 * Mode: Upload
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

  // The list of images
  var imageList = new Array();

  $(function() {
  
    // Start image uploader (Uploadify)				
	  $('#f_image').uploadify({
      'uploader'  : '/acp/js_libs/jquery_uploadify/uploadify.swf',
      'script'    : '/acp/ajax/uploadify.ajax.php',
      'cancelImg' : '/acp/static/icon/cross-circle.png',
      'folder'    : '/uploads',
      'removeCompleted' : true,
      'buttonImg' : '/acp/static/image/aui-add-image.png',
      'width'		: '211',
	    'height'	: '32',
	    'auto' : true,
	    'simUploadLimit' : 3,
	    'multi'		: true,
	    'scriptData' : {
	                     'PHPSESSID' : '<?php print session_id(); ?>'
	                   },
	    'onComplete' : function(event, ID, fileObj, response, data)
	                   {
	                     // Hide hint
	                     $('#images-none-yet').hide();
	                     
	                     // Parse text
	                     var jsonReply = eval('(' + response + ')');
	                    
	                     // Show the thumbnail
	                     $('<img rel="' + jsonReply.id + '" src="' + 
	                       jsonReply.thumbnails.thm + '" />')
	                       .appendTo('#images-added');
	                     
	                     // Add to the list
	                     imageList.push(jsonReply.id);
	                     
	                     // Sync form
                       $('#f_image_list').val(imageList.join(','));
	                   }
    });						
  
    // Allow removal of images
    $('div.images img').live('click', function() {
      
      // Get the ID
      var id = $(this).attr('rel');
      
      // Remove image from list
      var newImages = Array();
      $.each(imageList, function(i, v) {
        if(v != id)
        {
          newImages.push(v);
        }
      });
      imageList = newImages;
    
      // Remove from DB
      $.get('./ajax/image_remove.ajax.php', {'id' : $(this).attr('rel') });
      
      // Hide element
      $(this).remove();
      
      // Sync form
      $('#f_image_list').val(imageList.join(','));
      
    });
  
  });

</script>

<h1>Upload Images</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>About Uploading Images</h3>
    <p>
      Using this feature, you can manually upload images to the website.<br />
      Simply select one or more images to upload, and b2bFront will provide you with <abbr title="Web addresses">URLs</abbr>
      that can be accessed publicly from anywhere.
    </p>
  </div>
</div>

<br />

<form method="post" action="./?act=images&mode=upload_do">
<input type="hidden" name="f_image_list" id="f_image_list" value="" />

<table style="width: 100%">
  
  <tbody style="">
    
    <tr>
    
      <td style="width: 240px;">    
        <div style="height: 300px; background: #fff;" class="panel">
          <div class="title">Upload Queue</div>
          <div class="contents" id="images-uploading">
          
            <input name="f_image" id="f_image" type="file" />

          </div>
        </div>
      </td>
      <td style="">
        <div style="height: 300px; margin-left: 15px; background: #fff;" class="panel">
          <div class="title">Images</div>
          <div class="message" id="images-none-yet">
            <p style="padding: 0;">
              <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-circle-triangle-w"></span>
              Click the button to the left to upload an image from your computer.
              <br class="clear" />
            </p> 
          </div>
          <div class="contents images" id="images-added"></div>
        </div>
      </td>
      
    </tr>
    
  </tbody>
  
</table>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Once you are finished uploading images, click the button to the right to continue.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Finish" />
    <br class="clear" />
  </div>
</div>

</form>
