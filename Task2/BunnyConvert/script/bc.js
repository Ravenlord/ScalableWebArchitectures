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
            $('#form :input, #form :button').attr('disabled', 'disabled');
            $('#upload-progress').show('fast');
        },
        //dataType: 'json',
        error: function(responseText, statusText, xhr, $form) {
            $('#upload-progress').hide('fast');
            $('#form :input, #form :button').removeAttr('disabled');
            console.log('Error: ' + responseText + ' '+ statusText);
        },
        //resetForm: true,
        success: function(responseText, statusText, xhr, $form) {
            $('#upload-progress').hide('fast');
            $('#form :input, #form :button').removeAttr('disabled');
        },
        target: '#debug',
        uploadProgress: function(event, position, total, percentage){
            $('#upload-progress').attr('value', percentage);
        }
    };
    $('#form').ajaxForm(formOptions);
    
});