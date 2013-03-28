var bcUID = undefined;

$(document).ready(function documentReady() {
  // Check the requirements for this application to work.
  var requirements = true;
  var applicationErrorMessage = '';
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
                              command: 'register_client',
                              clientId: bcUID
                            };
      console.log('Trying to register client...');
      webSocketConnection.send(JSON.stringify(registerMessage));
    };

    webSocketConnection.onmessage = function webSocketMessage(event) {
      if(event.data) {
        console.log('Received message: ' + event.data);
        var data = $.parseJSON(event.data);
        if(data && data.command) {
          switch(data.command){
            case 'register_client':
              if(data.success) {
                console.log('Client registered.');
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

    // Apply the jQuery form plugin to the form.
    var formOptions = {
      beforeSubmit: function showRequest(formData, jqForm, options) {
        $('#form-message').hide('slow');
        $('#form :input, #form :button').attr('disabled', 'disabled');
        $('#upload-progress').show('fast');
      },
      dataType: 'json',
      error: function(responseText, statusText, xhr, $form) {
        $('#upload-progress').hide('fast');
        $('#form :input, #form :button').removeAttr('disabled');
        var $msgDiv = $('#form-message');
        $msgDiv.removeClass();
        $msgDiv.addClass('badge badge-important');
        $msgDiv.text('An unexpected error occured. Please try again.');
        $msgDiv.show('slow');
        console.log('Error: ' + responseText + ' ' + statusText);
      },
      success: function(response, statusText, xhr, $form) {
        $('#upload-progress').hide('fast');
        $('#form :input, #form :button').removeAttr('disabled');
        var $msgDiv = $('#form-message');
        if (response.error) {
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