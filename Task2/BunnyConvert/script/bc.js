var bcUID = undefined;
var filesPresent = false;

var COMMAND_KEEPALIVE = 'keepalive';
var COMMAND_REGISTER_CLIENT = 'register_client';
var COMMAND_DECODE = 'decode';
var COMMAND_ENCODE = 'encode';
var COMMAND_DELETE = 'delete';

function generateFileTableRow(fileName, target, subFolder) {
  return '<tr class="display-none ' + subFolder + '"><td><a href="' + target + '" target="_blank">' + fileName + '</a></td><td>' + subFolder + '</td></tr>';
}

$(document).ready(function documentReady() {
  // Check the requirements for this application to work.
  var requirements = true;
  var applicationErrorMessage = '';
  var $fileTable = $('#file-table');
  var $fileTableBody = $('#file-table tbody');
  if(document.getElementById('no-files') == null) {
    filesPresent = true;
  }
  bcUID = $.cookie('BCUID');

  if(!bcUID) {
    requirements = false;
    applicationErrorMessage = 'This application needs cookies to be activated. Please do so.';
  }

  if(!('WebSocket' in window)) {
    requirements = false;
    applicationErrorMessage = 'WebSockets are not available in your browser. Try upgrading to a modern one.';
  }

  if(requirements) {
    var webSocketConnection = new WebSocket('ws://heimdall.multimediatechnology.at:6666');
    webSocketConnection.onopen = function webSocketConnect(event) {
      console.log('WebSocket connection established!');
      var registerMessage = {
                              command: COMMAND_REGISTER_CLIENT,
                              clientId: bcUID
                            };
      console.log('Trying to register client...');
      webSocketConnection.send(JSON.stringify(registerMessage));
    };

    webSocketConnection.onmessage = function webSocketMessage(event) {
      if(event.data) {
//        console.log('Received message: ' + event.data);
        var data = $.parseJSON(event.data);
        if(data && data.command) {
          switch(data.command){
            case COMMAND_REGISTER_CLIENT:
              if(data.success) {
                console.log('Client registered.');
                // Call keepalive function in ten second intervals.
                window.setInterval(function webSocketKeepalive(){
                  webSocketConnection.send(JSON.stringify({
                                                            command: COMMAND_KEEPALIVE
                                                          }));
                }, 10000);
              }
              break;
            case COMMAND_KEEPALIVE:
              console.log('Keepalive succeeded.');
              break;
            case COMMAND_DECODE:
              if(data.success) {
                // Append the newly available file to the table.
                if(!filesPresent) {
                  $('#no-files').fadeOut('slow').remove();
                }
                $(generateFileTableRow(data.file_name, data.file_target, data.sub_folder)).appendTo($fileTableBody).fadeIn('slow');
                // Update sort order
                $fileTable.trigger('update');
                console.log('WAV conversion succeeded. File: ' + data.file_name + ' Folder: ' + data.sub_folder);
              } else {
                console.log('WAV convsersion failed. Message: ' + data.message);
              }
              break;
            case COMMAND_ENCODE:
              if(data.success) {
                // Append the newly available file to the table.
                $(generateFileTableRow(data.file_name, data.file_target, data.sub_folder)).appendTo($fileTableBody).fadeIn('slow');
                // Update sort order
                $fileTable.trigger('update');
                console.log(data.target_format + ' conversion succeeded. File: ' + data.file_name + ' Folder: ' + data.sub_folder);
              } else {
                console.log(data.target_format + ' convsersion failed. Message: ' + data.message);
              }
              break;
            case COMMAND_DELETE:
              if(data.folders) {
                console.log('Folders deleted: ' + data.folders);
                data.folders.forEach(function(element){
                  var trClass = '.' + element;
                  $(trClass + ' a').attr('href', '#');
                  $(trClass).addClass('struck-out').delay(5000).fadeOut('slow', function(){$(trClass).remove();});
                });
              }
              break;
            default:
              console.log('Unrecognized command.');
          }
        } else {
          console.log('Illegal message format.');
        }
      }
    };

    webSocketConnection.onerror = function webSocketError(event) {
      console.log('WebSocket error.');
    };

    webSocketConnection.onclose = function webSocketClose(event) {
      console.log('WebSocket closed. Retrying connection...');
      new WebSocket('ws://heimdall.multimediatechnology.at:6666');
    };

    // Fade in the content on page load.
    $('#content').fadeIn('slow');
    // Bind the event handler for the tags expander.
    $('#tags-expander').click(function toggleTagsFieldset(event) {
      event.preventDefault();
      $('#tags-fieldset').toggle('slow');
      $('#tags-expander-icon').toggleClass('icon-arrow-right');
      $('#tags-expander-icon').toggleClass('icon-arrow-down');
      return false;
    });

    // Apply the jQuery table sorter to the table
      $('#file-table').tablesorter({ sortList: [[1,0], [0,0]] });

    // Apply the jQuery form plugin to the form.
    var formOptions = {
      beforeSubmit: function showRequest(formData, jqForm, options) {
        $('#form-message').hide('slow');
        $('#form :input, #form :button').attr('disabled', 'disabled');
        $('#upload-progress').show('fast');
      },
      // TODO: uncomment
      //dataType: 'json',
      error: function(responseText, statusText, xhr, $form) {
        $('#upload-progress').hide('fast');
        $('#form :input, #form :button').removeAttr('disabled');
        var $msgDiv = $('#form-message');
        $msgDiv.removeClass();
        $msgDiv.addClass('badge badge-important');
        $msgDiv.text('An unexpected error occured. Please try again.');
        $msgDiv.show('slow');
        console.log('Error: ' + JSON.stringify(responseText) + ' ' + statusText);
      },
      success: function(response, statusText, xhr, $form) {
        $('#upload-progress').hide('fast');
        $('#form :input, #form :button').removeAttr('disabled');
        var $msgDiv = $('#form-message');
        if (!response.success) {
          $msgDiv.removeClass();
          $msgDiv.addClass('badge badge-important');
        } else {
          $msgDiv.removeClass();
          $msgDiv.addClass('badge badge-success');
        }
        $msgDiv.text(response.message);
        $msgDiv.show('slow');
        $('#form').resetForm();
        console.log(response);
      },
      //TODO: delete, if application is finished.
      target: '#debug',
      uploadProgress: function(event, position, total, percentage) {
        $('#upload-progress').attr('value', percentage);
      }
    };
    $('#form').ajaxForm(formOptions);
  } else {
    // Requirements are not met. Display error message.
    $applicationMessage = $('#application-message');
    $applicationMessage.text(applicationErrorMessage);
    $applicationMessage.show('slow');
  }

});