$(document).ready(function documentReady(){
    // Fade in the content on page load
    $('#content').fadeIn('slow');
    // Bind the event handler for the tags expander
    $('#tags-expander').click(function toggleTagsFieldset(event) {
        event.preventDefault();
        $('#tags-fieldset').toggle('slow');
        $('#tags-expander-icon').toggleClass('icon-arrow-right');
        $('#tags-expander-icon').toggleClass('icon-arrow-down');
        return false;
    });

    // Apply the jQuery form plugin to the form
    var formOptions = {
        beforeSubmit:  function showRequest(formData, jqForm, options){
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
            console.log('Error: ' + responseText + ' '+ statusText);
        },
        success: function(response, statusText, xhr, $form) {
            $('#upload-progress').hide('fast');
            $('#form :input, #form :button').removeAttr('disabled');
            //TODO: check for errors, set error text
            //TODO: reset form on success and set success message
            var $msgDiv = $('#form-message');
            if(response.error) {
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
        //TODO: delete, if application is finished
        target: '#debug',
        uploadProgress: function(event, position, total, percentage){
            $('#upload-progress').attr('value', percentage);
        }
    };
    $('#form').ajaxForm(formOptions);

});