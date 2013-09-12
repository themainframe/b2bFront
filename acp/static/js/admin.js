/**
 * Admin Scripts
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bFront
 * @version 1.0
 * @author Damien Walsh
 */
 
/**
 * Enable Notifications
 * @var boolean
 */
var enableNotifications = false;

/**
 * Show or hide the messages window
 * @var boolean
 */
var messagesVisible = false;

/**
 * Last interaction timer
 * @var integer
 */
var lastInteraction = 5;

//
// IE Patches
//

if(!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(needle) {
        for(var i = 0; i < this.length; i++) {
            if(this[i] === needle) {
                return i;
            }
        }
        return -1;
    };
}

// Checksum of the last quick-go JSON received
var lastQuickGoChecksum = null;

/**
 * Hide quick go
 */
function startHide()
{

    $('#quickGoResults').slideUp(400, null, function() {
      $('#quickGo').hide()
           .val('');
    });
        
    $('div.quick_search_box').removeClass('quick_search_selected');
}

// The current chat user
var currentChat = -1;

/**
 * Execute on page load finished
 */
$(function() {


  /**
   * Set up quick search
   */
   
    $('div.quick_search_box').click(function() {
      $('#quickGo').show()
                   .focus();
      $('#quickGoResults').slideDown();
      $(this).addClass('quick_search_selected');
    });
   
    // Set up Quick Go bar
    $('#quickGo').keyup(function() {
      
      // Get search
      $.getJSON('./ajax/dashboard_quick_go.ajax.php',
        {'query' : $(this).val() },
        function(data)
        {
              
          // Worth showing?
          if(data.checksum == lastQuickGoChecksum)
          {
            return;
          }
          else
          {
            // Clear box
            $('#quickGoResults').html('');
            
            // Store
            lastQuickGoChecksum = data.checksum;
          }
        
          $.each(data, function(i, v)
          {
            // Defined objects?
            if(v.objects != undefined && v.objects.length > 0)
            {
              // Add header
              var newTitle = $('<div/>').addClass('quickGoTitle')
                                        .css('padding-top', '12px')
                                        .html('<img class="middle" src="./static/icon/' + 
                                              v.icon + '" /> &nbsp;' + v.name);
              $('#quickGoResults').append(newTitle);
              
              // Objects present
              $.each(v.objects, function(index, value)
              {
                var newItem = $('<div/>').addClass('notification_log')
                                         .addClass('gradient')
                                         .hide()
                                         .html('<a style="color: #000; " href="' + v.linkroot + 
                                               value.id + '">' + value.long + '</a>')
                                         .click(function() { 
                                           window.location = v.linkroot + value.id;
                                         });
                $('#quickGoResults').append(newItem);
                $(newItem).slideDown(400);
              });
            }
          });
          
          // Fix final row
          $('#quickGoResults').children(':last')
                              .css('border-bottom', 'none');
        }
      );
    }).blur(function() {
    
      setTimeout(startHide, 150);
      
    });

  /**
   * Form submitted
   * Stop loading notifications
   */
  $('form').live('submit', function() {
    stopNotifications();
  });
  

  window.onfocus = function() {
    
      enableNotifications = true;
      console.log('Enabling notifications.');
    
  };
  
  window.onblur = function() {
  
    enableNotifications = false;
    console.log('Disabling notifications.');
  
  };
  
  /**
   * "External" link clicked
   * Opens a new window
   */
  $('a.new').click(function() {
    // Set timer low
    lastInteraction = 2;
  });

  /**
   * Page Loaded
   */

  // Set jQuery [UI] defaults
  $.datepicker.setDefaults({
     'dateFormat' : 'dd M yy'
  });


  // Render date pickers
  $("input.date").datepicker();
  
  // Set up "new" links
  $("a.new").attr("target", "_blank");
  
  // Autoselect text in marked fields
  $("input.autoselect").focus(function() {
    $(this).select();
  });
  
  // Set up notifications
  setUpNotifications();
  
  // Set up UI controls
  setUpUIControls();
  
  // Testing:
  runTests();

});

/**
 * Count down last interaction
 * @return boolean
 */
/*function decrementLastInteraction()
{
  lastInteraction --;
  
  if(lastInteraction < 0 && enableNotifications)
  {
    enableNotifications = false;
    console.log('Disabling notifications.');
  }
  
  // Reschedule self
  setTimeout('decrementLastInteraction()', 1000);
  
  return;
}*/

/**
 * Stop notifications
 * @return boolean
 */
function stopNotifications()
{
  enableNotifications = false;

  return true;
}

/**
 * Set up the "toaster" notification display
 * @return boolean
 */
function setUpNotifications()
{
  // Notifications currently ON
  enableNotifications = true;
  
  // Start jNotify to display notifications
  $('#notifications')
                   .jnotifyInizialize({       // !!?
                       oneAtTime: false,
                       appendType: 'append'
                   })
                   .css({ 'position':   'absolute',
                          'marginTop':  '20px',
                          'right':      '20px',
                          'width':      '250px',
                          'z-index':    '9999'
                   });
                   
  
  // Start checking
  setTimeout('checkForNotifications()', 100);
  
  return true;
} 

/**
 * Droppable Argument set
 * @var hash
 */
var droppableArgs = {
  'over' : function(event, ui) {
  
    // Same column only
    if($(this).attr('field') != $(ui.draggable).attr('field'))
    {
      return false;
    }
  
    // Update UI
    $(this).html($(ui.draggable).html());
    
    // Finished
    $(this).animate({color: "#50954B"}, 500)
           .animate({color: "#000000"}, 500);
    
    // Update DB
    $.post('ajax/editable.ajax.php',
           {
             'value' : $(ui.draggable).text(),
             'table' : $(this).attr('table'),
             'rowid' : $(this).attr('rowid'),
             'field' : $(this).attr('field'),
             'cache' : $(this).attr('cache'),
             
             // Unique to Attribute Modifiers
             'classificationID' : $(this).attr('cf'),
             'itemID' : $(this).attr('item')
           },
           function(data)
           {
             // Trigger callback
           });      
  }
};


/**
 * Draggable Argument set
 * @var hash
 */
var draggableArgs = {
  'helper' : 'clone',
  'axis' : 'y'
};

var dualDraggableArgs = {
  'helper' : 'clone'
};

/**
 * Set up UI controls
 * E.g. FormListBuilder/DataDropDown/DataTable....
 * @return boolean
 */
function setUpUIControls()
{
  //
  // Item Selector Query
  //
  $('.item-selector-option').change(function() {
    itemSelectorModified();
  });
  
  $('#item-selector-term').keyup(function() {
    itemSelectorModified();
  });
  
  $('#item-selector-all').change(function() {
    if($('#item-selector-all').is(':checked'))
    {
      $('.item-selector-cb').attr('checked', 'checked')
                            .change();
    }
    else
    {
      $('.item-selector-cb').removeAttr('checked')
                            .change();
    }
  });
  
  //
  // Start hinted UI components
  //
  $('.hinted').focus(function() {
    if($(this).val() == $(this).attr('hint'))
    {
      $(this).val('')
             .css('color', '#000');
    }
  }).blur(function() {
    if($(this).val() == '')
    {
      $(this).val($(this).attr('hint'))
             .css('color', '#afafaf');
    }
  }).each(function(i, v) {
    $(this).attr('hint', $(this).val());
  });


  //
  // FormListBuilder Inits...
  //
  
  $('a.flb_remove').live('click', function() {
  
    // Remove the text
    var relData = $(this).attr('rel');
    var nameWithValue = relData.split("_", 2);
    var flbId= '#flb_' + nameWithValue[0] + '_text';
    var fieldValue = nameWithValue[1];
    
    // Remove by RegExp
    $(flbId).val($(flbId).val().replace(fieldValue, ''));
      
    // Remove the row
    $(this).parent().parent().remove();
    
    // Empty now?
    if($(flbId).val() == '')
    {
      // Show "empty"
      $('#flb_' + nameWithValue[0] + '_no_data').show();
    }
    
  });
  
  //
  // Try to copy H1 Name in to Shortcut Add
  //
  $('#scut_name').val($.trim($('h1:first').text()));
  
  //
  // Editable Inits...
  //
  
  $('span.editable input[type="checkbox"]').click(function() {
    
    var cb = this;
    
    // Send request
    $.post('ajax/editable.ajax.php',
     {
       'value' : $(this).is(':checked') ? 1 : 0,
       'table' : $(this).parent().attr('table'),
       'rowid' : $(this).parent().attr('rowid'),
       'field' : $(this).parent().attr('field'),
       'cache' : $(this).parent().attr('cache')
     },
     function(data) { 
      
                   
     });
                      
    
  });

  $('span.editable').live('click', function() {
    
    // Unfocus all other editables
    $('input.edit').blur();
    
    var targetSpan = $(this);
    var target = $(this).parent();
    
    
    if($(this).hasClass('editable_cb'))
    {
      // Checkbox mode
      return;
    }
    
    $(this).replaceWith('<input item="' + $(this).attr('item') + '" cf="' + $(this).attr('cf') + '" cellid="' + $(this).attr('cellid') + '" cache="' + $(this).attr('cache') + '" id="' + $(this).attr('id') + 
                        '" rowid="' + $(this).attr('rowid') + '" table="' + 
                        $(this).attr('table') + '" field="' + $(this).attr('field') + 
                        '" class="edit" value="' + $(this).text() + '" />');
                      
                        
    $(target).children().first()
             .focus()
             .attr('original', $(target).children().first().val())
             .blur(finishEditing)
             .keydown(function(e) {
               if(e.keyCode == 9)
               {
                 $(this).blur();
                 $('span.editable[cellid="' + (parseInt($(targetSpan).attr('cellid')) + (event.shiftKey ? -1 : 1)) +  '"]')
                  .click();
                 return false;
               }
               if(e.keyCode == 27)
               {
                 console.log('Original: ' + $(this).attr('original'));
                 $(this).val($(this).attr('original'));
                 $(this).blur();
                 return false;
               }
             })
             .keyup(function(e) {
               if(e.keyCode == 13)
               {
                 $(this).blur();
               }
             });
             
    if($(target).children().first().attr('cf') != 'undefined')
    {
      $(target).children().first()
        .autocomplete({
        		  source: function(request, response) {
                  $.ajax({
                      url: "ajax/autocomplete_classification_value.ajax.php",
                      dataType: "json",
                      data: {
                          term: request.term,
                          id: $(target).children().first().attr('cf')
                      },
                      success: function(data) {
                          response($.map(data, function(item) {
                              return {
                                  label: item.value,
                                  value: item.value
                              }
                          }))
                      }
                  })
              },
        			minLength: 0,
        			delay: 1,
        			data: {
        			  'id' : $(target).children().first().attr('id')
        			},
        			focus: function(event, ui) {
        			  $(target).children().first().val(ui.item.label);	 
        			},
        			select: function( event, ui ) {
        				event.preventDefault();
        				$(target).children().first().val(ui.item.label);	 
        				return false;
        		  },
        		  search: function( event, ui ) { }
        		});
    }

  }).css('MozUserSelect', 'none')
    .droppable(droppableArgs);
  

  $('span.editable').each(function() {
      
      if($(this).attr('dual') == 'true')
      {
        $(this).draggable(dualDraggableArgs);
      }
      else
      {
        $(this).draggable(draggableArgs);
      }
      
  });

  return true;
}

/**
 * Callback function for finishing editing a field
 * @return boolean
 */
function finishEditing() {
    
  $(this).replaceWith('<span item="' + $(this).attr('item') + '" cf="' + $(this).attr('cf') + '" cellid="' + $(this).attr('cellid') + '" id="' + $(this).attr('id') + '" rowid="' + $(this).attr('rowid') + '" table="' + 
                      $(this).attr('table') + '" cache="' + $(this).attr('cache') + '" field="' + 
                      $(this).attr('field') + '" class="editable" unselectable="on">' + $(this).val() + '</span>');
                
  // Store ID
  var id = $(this).attr('id');
                
  // Make a request to update the table as specified
  $.post('ajax/editable.ajax.php',
         {
           'value' : $(this).val(),
           'table' : $(this).attr('table'),
           'rowid' : $(this).attr('rowid'),
           'field' : $(this).attr('field'),
           'cache' : $(this).attr('cache'),
           
           // Unique to Attribute Modifiers
           'classificationID' : $(this).attr('cf'),
           'itemID' : $(this).attr('item')
         },
         function(data) { 
          
           // Show new value
           $('#' + id).html(data)
                      .draggable(draggableArgs)
                      .droppable(droppableArgs);
                      
            // Finished
            $('#' + id).animate({color: "#50954B"}, 500)
                       .animate({color: "#000000"}, 500);
         });
                          
}

// First dealer load?
var firstLoad = true;


/**
 * Check for notifications via AJAX
 * Rescehdules self.
 * @return boolean
 */
function checkForNotifications()
{
  // Enabled?

    // Read each notification
    $.getJSON('ajax/notifications.ajax.php',
    {'notifications' : ('enableNotifications' ? 'true' : 'false')},
    function(data) {
 
      // Chat status
      if(data.status == 'true')
      { 
        if(!online)
        {
          nowOnline();
        }
      
        online = true;
      }
      else
      {
        if(online)
        {
          nowOffline();
        }
        
        online = false;
        
      }
      
      // Update status
      statusChange();
      
      // Generate notifications if required
      $.each(data.notifications, function(index, notification) {
        if(enableNotifications)
        {
          notify(
            notification.title, 
            notification.content, 
            notification.icon, 
            (notification.persist == '1')
          );
        }
      });

      // Refresh online dealers
      onlineUsers = new Array();
      userCount = 0;
      
      $.each(data.onlineUsers, function(index, user) {
        
        // Log seeing the dealer
        onlineUsers.push(user.id);
        
        // Already exists?
        if($('div.dealer[dealerid="' + user.id + '"]').length == 0)
        {
          // Add it
          addDealer(user.description, user.id);
        }
        
        // Current?
        if(currentChat == user.id)
        {
          $('#cctv_state').html(user.activity);
        }
        
        // Taken?
        if(user.state != 'false')
        {
          // Using other admin
          console.log(user.state)
          $('div.dealer[dealerid="' + user.id + '"]').addClass('taken')
            .attr('title', 'This dealer is currently talking to ' + user.state); 
          $('div.dealer[dealerid="' + user.id + '"] div.d-status').addClass('d-busy'); 
          
          // Selected?
          if($('div.dealer[dealerid="' + user.id + '"]').hasClass('selected'))
          {
            // Select a different one
            $('div.dealer:not(.taken)').click();
          }
        }
        else
        {
          // Free
          userCount ++;
          $('div.dealer[dealerid="' + user.id + '"]').removeClass('taken')
            .attr('title', 'This user is available to chat.'); 
          $('div.dealer[dealerid="' + user.id + '"] div.d-status').removeClass('d-busy'); 
        }
        
      });
      
      if(online)
      {
        if(userCount == 0)
        {
          $('div.info_panel').show();
          $('div.comm_panel').hide();
        }
        else
        {
          $('div.info_panel').hide();
          $('div.comm_panel').show();
        }
      }
            
      // Show chat messages
      $.each(data.chatMessages, function(index, message) {
        
        // Meta message?
        if(message.meta != '')
        {
          dealerName = $('.dealer[dealerid="' + message.user_id + 
            '"]').children('.d-status').html();
        
          if(dealerName == 'null' || dealerName == null || dealerName == undefined)
          {
            dealerName = 'The user';
          }
        
          switch(message.meta)
          {
            case 'request':
                
              // Add meta message for this user
              chatMessage(
                message.id,
                dealerName + ' requests your assistance.',
                message.user_id,
                message.time,
                message.read,
                true,
                message.direction
              );
              
              break;
              
            case 'closed':
                
              // Add meta message for this user
              chatMessage(
                message.id,
                dealerName + ' has closed their chat window.',
                message.user_id,
                message.time,
                message.read,
                true,
                message.direction
              );
              
              break;
          } 
        }
        else
        {        
          chatMessage(
            message.id,
            message.content, 
            message.user_id,
            message.time,
            message.read,
            false,
            message.direction
          );
        }
      });
      
      // Now remove non-online users
      $('div.dealer').each(function() {
        if($.inArray($(this).attr('dealerid'), onlineUsers) == -1)
        {
          // Current?
          if($(this).hasClass('selected'))
          {
            if($('div.dealer.taken').length == 0)
            {
              $('div.info_panel').show();
              $('div.comm_panel').hide();
            }
            else
            {
              $('div.info_panel').hide();
              $('div.comm_panel').show();
            }
          }
        
          // Remove
          $(this).remove();
        }
        
      });
      
      // First load?
      if(firstLoad || currentChat == -1)
      {        
        // Click the first dealer
        $('div.dealer:eq(0)').click();
        firstLoad = false;
      }
        
      // Update
      updateChat();
          
    
      // Clear admins
      $('div.onlineAdmin').remove();
          
      // Admin list
      adminCount = 0;
      $.each(data.admins, function(i, k) {
        newItem = $('<div />')
                  .addClass('gradient')
                  .addClass('notification_log')
                  .addClass('onlineAdmin');
                  
        if(i == data.admins.length - 1)
        {
          $(newItem).css('border', 'none');
        }
                  
        imgOnline = $('<img />')
                    .attr('src', 'static/icon/status.png')
                    .css('vertical-align', 'middle')
                    .attr('title', 'Staff member using ACP.');
                    
        if(k.online == '1')
        {
          // Show "Live Chat" too
          imgLiveIM = $('<img />')
                      .attr('src', 'static/icon/balloon.png')
                      .css('vertical-align', 'middle')
                      .attr('title', 'Staff member online for IM Chat.');
        }
        else
        {
          imgLiveIM = false;
        }
                    
        strong = $('<strong />')
                 .html('&nbsp;' + k.full_name);
                 
        // Compile
        $(newItem).append(imgOnline);
                  
                  
        if(imgLiveIM)
        {
          $(newItem).append('&nbsp;')
                    .append(imgLiveIM)
                    .append('&nbsp;');
        }
        
        $(newItem).append(strong);
                  
        // Add to list
        $('#im-system').append(newItem);
        
        adminCount ++;
      });
      
      if(adminCount > 0)
      {
        $('#no-staff').css('display', 'none');
      }
      else
      {
        $('#no-staff').css('display', 'block');
      }
          
    });
    
  // Reschedule
  setTimeout(checkForNotifications, 5000);

  return true;
}

/**
 * Switch online/offline status
 * @return boolean
 */
function switchOnlineOffline()
{
  // Change mode
  $.getJSON('ajax/chat_status.ajax.php',
    {'status' : (online ? '0' : '1')},
    function(data) {
    
      online = !online;
      
      statusChange();
      
      if(online)
      {
        nowOnline(true);
      }
      else
      {
        nowOffline(true);
      }
      
  });

  return true;
}

//
// Chat setup
//

// My Status
var online = false;

$(function() {

  /**
   * Set up chat
   */
   
  $('#mystatus').click(function() {
    
    switchOnlineOffline();
  
  });
   
  $('#message-system').dialog({
    'autoOpen' : false,
    'width' : 800,
    'resizable':false,
    'height' : 500,
    'open' : function() { },
    'close' : function() {
      messagesVisible = false;
      $('#messages-toggle').removeClass('selected');
    }
  });
  
  $('#messages-toggle').click(function() {
    
    if(!messagesVisible)
    {
      // Show messages display
      $('#message-system').dialog('open');
      $(this).addClass('selected');
      messagesVisible = true;
    }
    else
    {
      // Hide messages display
      $('#message-system').dialog('close');
      $(this).removeClass('selected');
      messagesVisible = false;
    }
  });
   
  $('textarea.inputArea').keydown(function(e) {
    
    if(e.keyCode == 13 && currentChat != -1)
    {
      if($.trim($(this).val()) == '')
      {
        return false;
      }
    
      sendMessage($(this).val(), currentChat);
      $(this).val('');
      updateChat();
      return false;
    }
    
  });
  
  // Call button
  $('div.dealer:not(.taken)').live('click', function() {
    currentChat = $(this).attr('dealerid');
    $('textarea.inputArea:first').focus();
    $('#cctv_state').html('');
    $(this).removeClass('d-alert');
    updateChat();
  });
  
  

});

/**
 * Define chat messages
 */
var seenMessages = new Array();

/**
 * Go online
 * @param boolean userInvoked Indicates if the user invoked the status change, or the server
 * @return boolean
 */
function nowOnline(userInvoked) 
{
  // Set LED
  $('#mystatus-led').addClass('d-online');
  $('a.messages-menu').removeClass('messages-offline');

  if(userInvoked)
  {
    // Show notification too
    notify('You are now online.', 
       'You may receive chat messages from dealers.', 'status.png', false);
  }
    
  return true;
}

/**
 * Go offline
 * @param boolean userInvoked Indicates if the user invoked the status change, or the server
 * @return boolean
 */
function nowOffline(userInvoked) 
{
  // Set LED
  $('#mystatus-led').removeClass('d-online');
  $('a.messages-menu').addClass('messages-offline');

  if(userInvoked)
  {
    // Show notification too
    notify('You are now offline.', 
       'New chat messages will not be directed to you.', 'status-offline.png', false);
  }
  
  return true;
}

/**
 * General status change
 * @return boolean
 */
function statusChange()
{
  if(online)
  {
    $('div.d-overlay').hide();
    $('div.comm_panel').show();
  }
  else
  {
    $('div.d-overlay').hide();
    $('div.comm_panel').hide();
    $('div.offline_panel').show();
  }
  
  return true;
}

/**
 * Add a new online dealer to the list
 * @param string description The description of the dealer account
 * @param integer userID The ID of the dealer
 * @return boolean
 */
function addDealer(description, userID)
{
  // Build a message object to show on screen
  dealer = $('<div />')
           .attr('dealerid', userID)
           .addClass('dealer');
           
  stat = $('<div />')
           .addClass('d-status')
           .addClass('d-online')
           .html(description.toString());
                 
  br = $('<br />')  
       .addClass('clear');

  // Build
  $(dealer).append(stat)
           .append(br);
           
  // Add to the list
  $('#dealers-list').append(dealer);
  
  return true;
}

/**
 * Handle a chat message
 * @param integer id The ID of the message
 * @param string content The content of the message
 * @param integer userID The user ID that sent the message
 * @param string time The time that the message arrived
 * @param string read The state of the message
 * @param boolean meta Display as a meta message
 * @param integer direction The direction of the message
 * @return boolean
 */
function chatMessage(id, content, userID, time, read, meta, direction)
{
  // Online?
  if(!online)
  { 
    return false;
  }

  if($.inArray(id, seenMessages) == -1)
  {
    // Build a message object to show on screen
    message = $('<div />')
              .attr('owner', userID)
              .attr('id', 'msg' + id)
              .addClass('cmessage')
              .addClass('usr' + userID)
              .addClass('otheruser');
    
    image = $('<div />')
            .addClass('img')
            .html((meta ? '&nbsp;' : content));
            
    time = $('<div />')
           .addClass('time')
           .html(time);
           
    contents = $('<div />') 
               .addClass('contents')
               .html((!meta ? '&nbsp;' : content));
               
    br = $('<br />')  
         .addClass('clear');
        
    if(direction == 0)
    {
      // Sent by me
      $(message).removeClass('otheruser'); 
    }
         
    if(meta)
    {
      $(message).addClass('meta');
      $(image).css('display', 'none');
    }
         
    // Construct
    $(message).append(image)
              .append(time)
              .append(contents)
              .append(br);
    
    // Add to the window
    $('div.chatArea').append(message);

    // Update
    updateChat();
    
    // Remember that the message has been seen
    seenMessages.push(id);
 
    if(read == 'false')
    {
      // Show an alert
      showNewMessageAlert();
    
      // Animate user?
      if(currentChat != userID)
      {
        $('div.dealer[dealerid="' + userID + '"]').addClass('d-alert');
      }
      
      // Change to that chat if not open
      if(!messagesVisible)
      {
        $('div.dealer[dealerid="' + userID + '"]').click();
        
        // Sound
        doPlay();
        
        // Show notification too
        notify($('div.dealer[dealerid="' + userID + '"]').text(), 
           'New chat message.', 'balloon-ellipsis.png', true,
           function() {
           
              $('#message-system').dialog('open');
              $('#messages-toggle').addClass('selected');
              messagesVisible = true;
           
           $('div.dealer[dealerid="' + userID + '"]').click(); });
      }

    }
  }

  return true;
}

/**
 * Send a message
 * @param string content The message to send
 * @param integer userID The user ID to send to
 * @return true
 */
function sendMessage(content, userID)
{
  $.getJSON('ajax/chat.ajax.php', {'content' : content, 'userID' : userID},
    function(data) {
      seenMessages.push(data.id.toString());
    });
    
  // Build a message object to show on screen
  message = $('<div />')
            .attr('admin', 'true')
            .addClass('cmessage')
            .addClass('usr' + userID);
  
  image = $('<div />')
          .addClass('img')
          .html(content);
          
  currentTime = new Date();
  time = $('<div />')
         .addClass('time')
         .html((currentTime.getHours() < 10 ? "0" + currentTime.getHours() : currentTime.getHours()) +
          ":" + (currentTime.getMinutes() < 10 ? "0" + currentTime.getMinutes() : currentTime.getMinutes()));   
      
  contents = $('<div />') 
             .addClass('contents')
             .html('&nbsp;');
             
  br = $('<br />')  
       .addClass('clear');
       
  // Construct
  $(message).append(image)
            .append(time)
            .append(contents)
            .append(br);
  
  // Add to the window
  $('div.chatArea').append(message);
    
  return true;
}

/**
 * Show an alert for a new message
 * @return boolean
 */
function showNewMessageAlert()
{
  // Show animation
  $('a.messages-menu').addClass('messages-animate');
  
  // Hide after a period of time
  setTimeout(hideNewMessageAlert, 5000);
  
  return true;
}

/**
 * Hide the alert for a new message
 * @return boolean
 */
function hideNewMessageAlert()
{
  // Hide animation
  $('a.messages-menu').removeClass('messages-animate');

  return true;
}

/**
 * Auto-Scroll and refresh the chat area.
 * @return boolean
 */
function updateChat()
{ 
  // Hide all non-current messages
  $('div.cmessage:not(.usr' + currentChat + ')').hide();
  $('div.cmessage.usr' + currentChat).show();

  // Scroll
  $('div.chatArea').scrollTo('max', 100);

  // Set chat title
  $('#chatTitle').html($('.dealer[dealerid="' + currentChat + '"]').children('.d-status').html());
  $('.dealer:not([dealerid="' + currentChat + '"])').removeClass('selected');
  $('.dealer[dealerid="' + currentChat + '"]').addClass('selected');
  $('.dealer[dealerid="' + currentChat + '"]').addClass('selected');

  return true;
}

/**
 * Show a notification
 * @param string title The title of the notification
 * @param string message The content of the notification
 * @param string callback A callback to execute on click
 * @return boolean
 */
function notify(title, message, iconURL, persist, callback)
{
  if(iconURL == '')
  {
    iconURL = 'information.png';
  }
  
  if(callback == 'undefined' || !callback)
  {
    callback = function() { };
  }

  $('#notifications').jnotifyAddMessage({
                      text: '<strong>' + title + '</strong><br /><p>' + message + '</p>',
                      permanent: persist,
                      icon: 'static/icon/' + iconURL,
                      click: callback
                    });
      
  return true;
}

/**
 * Set the favicon
 * @return boolean
 */
function setIcon()
{
  var link = document.createElement('link');
  link.type = 'image/gif';
  link.rel = 'shortcut icon';
  link.href = 'static/image/aui-activity.gif';
  document.getElementsByTagName('head')[0].appendChild(link);
  
  // Change the icon back soon
  setTimeout('resetIcon()', 3000);
  
  return true;
}

/**
 * Clear icon state
 * @return boolean
 */
function resetIcon()
{
  var link = document.createElement('link');
  link.type = 'image/x-icon';
  link.rel = 'shortcut icon';
  link.href = '/favicon.ico';
  document.getElementsByTagName('head')[0].appendChild(link);
  
  return true;
}

/**
 * Blank out the display while loading takes place
 * @return boolean
 */
function loadingScreen()
{
  // Notifications will not be shown
  enableNotifications = false;

  // Show unclosable dialog
  $('#page-transition-loader').html('<p style="padding: 23px 0px 0px 20px;"><span class="ui-icon" style="background-image: ' + 
                                    'url(static/image/aui-loader.gif); float:left; margin:0 17px 20px 0;"></span>' + 
                                    'Please Wait...</p>')
                              .dialog({
    modal: true,
    resizable: false,
    draggable: false,
    width: 320,
    height: 100,
    closeOnEscape: false,
    open: function(event, ui) { $(".ui-dialog-titlebar").hide(); }
  });
  
  return true;
}

/**
 * Hide loading screen
 * @return boolean
 */
function hideLoadingScreen()
{
  // Notifications will not be shown
  enableNotifications = true;

  // Show unclosable dialog
  $('#page-transition-loader').dialog('close');
  
  // Reshow titlebars
  $(".ui-dialog-titlebar").show();
  
  return true;
}

/**
 * Show a confirmation box with the specified text and callback
 * @param string description The description of the decision the user must make
 * @param callback callback The callback to execute if the user approves the action.
 * @return boolean 
 */
function confirmation(description, callback)
{
  $('#confirmation').html('<p><span class="ui-icon ui-icon-circle-check" ' + 
                          'style="float:left; margin:0 7px 20px 0;"></span>' + 
                          description + '</p>').dialog({
                                                        modal: true,
                                                        resizable: false,
                                                        draggable: false,
                                                        width: 400,
                                                        buttons: {
                                                          'Ok' : 
                                                            function() { callback(); $(this).dialog('close'); },
                                                          'Cancel' : 
                                                            function() { $(this).dialog('close'); }
                                                        }
                                                      });
}

/**
 * Show an error on the specified field name
 * @param string name The name of the field
 * @param string message The message to display
 * @return boolean
 */
function error(name, message)
{  
  $('[name="' + name + '"]')
    .addClass('error')
    .after('<br /><br /><span class="error_message">' + message + '</span>')
    .attr('value', '');
    
  // Scroll to it after a short time
  setTimeout('scrollTo("' + name + '")', 300);
    
  return true;
}

/**
 * Scroll to the specified field
 * @param string name The name of the field
 * @return boolean
 */
function scrollTo(name)
{  
  $.scrollTo('[name="' + name + '"]', 800, {
    'offset' : {left: 0, top: -200 }
  });
  
  return true;
}

//
// FormListBuilder Functions
//

/** 
 * Set up a new FormListBuilder
 * @param string name The name of the FormListBuilder
 * @return boolean
 */
function flb_setup(name)
{
  // Bind events
  $('#flb_' + name + '_new').bind('keypress', function(e) {
    if(e.keyCode == 13)
    {
      flb_addValue(name);
      return false;
    }
  });
}

/** 
 * Add a value on a FormListBuilder
 * @param string name The name of the FormListBuilder
 * @return boolean
 */
function flb_addValue(name)
{
  // Find the FormListBuilder value
  var value = $('#flb_' + name + '_new').val();
 
  // Do not allow empty rows
  if(value.replace(/\W+/, '') == '')
  {
    $('#flb_' + name + '_new').val('');
    return false;
  }
   
  // Hide any "no_data" <div /> elements
  $('#flb_' + name + '_no_data').hide();
  
  // Add the row to the table
  $('#flb_' + name + '_rows').append('<tr><td><a rel="' + name + '_' + value + 
                                     '" class="tool flb_remove" title="Remove">' + 
                                     '&nbsp; <img src="static/icon/cross-circle.png" alt="Remove" /> Remove</a>&nbsp;' + 
                                     value + '</td></tr>');
  
  // Add to the <textarea />
  $('#flb_' + name + '_text').val($('#flb_' + name + '_text').val() + value + "\n");
  
  // Clear value
  $('#flb_' + name + '_new').val('');
  
  return true;
}

/**
 * Request to generate a DTab file
 * @param string DTabName The data table file name
 * @param string outputType The type of output generated. 
 * @return boolean
 */
function downloadDTab(DTabName, outputType)
{
  // Make the request
  $.get('ajax/dtab_download.ajax.php', {'dtab': DTabName.replace('.dtab', ''), 'type' : outputType}, 
    function(data) {
      // Finished
    });
  
  // Remind the user
  notify('Information', 
    'The file is now being generated.<br />You will be notified when it is ready for download.', 'information.png');
  
  // Hide the interface to prevent multiple copies
  $('a.download').hide();
  
  return true;
}

//
// Pop-Up Item Selector UI
//

/**
 * Show the Item Selection dialog.
 * @param boolean multipleItems Allow the selection of multiple items.
 * @param closure finishedSelecting A callback to fire when selection is finished.
 * @param array preSelected An array of IDs that are already selected when the selector opens.
 * @return boolean
 */
function selectItems(multipleItems, finishedSelecting, preSelected)
{
  // Build and auto-show the selection dialog
  $('#item-selector').dialog({
    'width': '700px',
    'height': '400',
    'min-height': '400px',
    'title': 'Select ' + (multipleItems ? 'One or More Items' : 'an Item') + '...',
    'modal' : true,
    'position' : ['center', '-100px'],
    'resizable' : false,
    'draggable' : false,
    'close' : function() {
      $(this).dialog('destroy');
    },
    'buttons' : {
                  'Ok' : function() {
                           
                           // Fire callback
                           if(itemSelectorItems.length != 0)
                           {
                             finishedSelecting(itemSelectorItems);
                           }
                           
                           // Close
                           $(this).dialog('close');
                         },
                  'Cancel' : function() {
                               $(this).dialog('close');
                               itemSelectorItems = new Array();
                             }
                }
  }).parent().children('.ui-dialog-buttonpane')
    .append($('<div />').css('padding', '10px')
      .append('&nbsp; <span class="grey" id="item-selector-items">0 items selected</span>&nbsp; &nbsp;')
      .append('<a href="#" id="item-selector-clear" onclick="itemSelectorItems = new Array(); itemSelectorModified();">Clear Selection</a>&nbsp; &nbsp;')
      .append('<a href="#" id="item-selector-show" onclick="itemSelectorModified(true);">Show Selection</a>')
    );
       
  // Pre select?
  if(preSelected != undefined)
  {
    itemSelectorItems = preSelected;
    itemSelectorModified(true);
  }
  
  multiMode = multipleItems;
        
  return true;
}

// Definitions for item selector
var itemSelectorItems = new Array();
var multiMode = true;

/**
 * Update the Item Selection dialog.
 * @param boolean showOnlySelection Only show the current selected items
 * @return boolean
 */
function itemSelectorModified(showOnlySelection)
{
  // Set fancy loader background
  $('#item-selector-contents')
    .css('background', 'url(./static/image/aui-loader.gif) no-repeat center center');
  $('#item-selector-contents table').hide();
  
  // Update count
  $('#item-selector-items').html(
    itemSelectorItems.length.toString() + ' item' + 
      (itemSelectorItems.length > 1 ? 's' : '')  + ' selected'
  );
  
  // AJAX request
  $.getJSON('./ajax/select_items.ajax.php',
    { 'term' : ($('#item-selector-term').val() == $('#item-selector-term').attr('hint') ?
        '' : $('#item-selector-term').val()),
      'category' : $('#item-selector-category').val(),
      'classification' : $('#item-selector-classification').val(),
      'label' : $('#item-selector-label').val(),
      'ids' : (showOnlySelection ? itemSelectorItems.join(',') : '')
    }, function(data) {
    
    // Finished request - clear
    $('#item-selector-contents table').show();
    $('#item-selector-contents').css('background', 'none');
    $('#item-selector-all').removeAttr('checked');
    
    // Clear rows
    $('#item-selector-contents table tbody tr').remove();
    
    // Alternation
    var alternate = false;
    var rowCount = 0;
    
    // Show new rows
    $.each(data, function(index, element) {
      
      // Build a new row
      rowCount ++;
      newRow = $('<tr />');
      
      // Checkmark
      checkmarkCell = $('<td />');
      checkmark = $('<input />').attr('type', 'checkbox');
      checkmark.addClass('item-selector-cb');
      
      // Already checked?
      if(itemSelectorItems.indexOf(element.id) != -1)
      {
        $(checkmark).attr('checked', 'checked');
      }
      
      checkmark.attr('rel', element.id);
      $(checkmark).bind('change', function() {
      
        if($(this).is(':checked')) 
        {
          // Clear first?
          if(!multiMode)
          {
            $('.item-selector-cb').removeAttr('checked');
            $(this).attr('checked', 'checked');
            itemSelectorItems = new Array();
          }
        
          // Add
          itemSelectorItems.push($(this).attr('rel'));
        }
        else
        {
          // Remove 
          newItemSelectorItems = new Array();
          currentItem = $(this).attr('rel');
          
          $.each(itemSelectorItems, function(i, v) {
            if(v != currentItem)
            {
              newItemSelectorItems.push(v);
            }
          });
          itemSelectorItems = newItemSelectorItems;
        }
        
        // Update count
        $('#item-selector-items').html(
          itemSelectorItems.length.toString() + ' item' + 
            (itemSelectorItems.length > 1 ? 's' : '')  + ' selected'
        );
        
        return true;
      });
      
      // Add checkbox to cell
      $(checkmarkCell).append(checkmark);
      
      // Build SKU
      skuCell = $('<td />');
      skuCell.html(element.sku);
      
      // Name
      nameCell = $('<td />');
      nameCell.html(element.name);
      
      // Alternate?
      if(alternate)
      {
        newRow.addClass('alt');
      }
      
      // Flip
      alternate = !alternate;
      
      // Construct the row
      newRow.append(checkmarkCell);
      newRow.append(skuCell);
      newRow.append(nameCell);
      
      // Add the new row
      $('#item-selector-contents table tbody').append(newRow);
    });
  
    if(rowCount == 0)
    {
      $('#item-selector-contents table').hide();
    }
    
  });
  
  return true;
}

//
// Debugging Functions
//

/**
 * Run testing procedures
 * These are todo features that will be moved to setUpUIControls() or another
 * main method once they have been tested.
 * @return boolean
 */
function runTests()
{
  return true;
}
