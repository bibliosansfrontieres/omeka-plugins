function copyTagContentToClipboard(contentTagId) {
	var contentNode = document.getElementById(contentTagId);
    contentNode.select();
    var copySuccess = false;
    var copyFailureReason = '';
    try {
        copySuccess = document.execCommand('copy');
    } catch (err) {
        copyFailureReason = err.message;
    }
    if(copySuccess){
        swal({
            text: 'Content copied to clipboard',
            showConfirmButton: false,
            timer: 1000,
            type: 'success',
        })
    }else{
        alert('Unable to copy content to clipboard. '+copyFailureReason);
    }
}

jQuery(document).ready(function($) {
    $('.bsfcompanion_show_errors').click(function(e){
        swal({
            title: 'Export error report',
            html: $('#bsfcompanion_error_report').html(),
            width: 600,
            animation: false
        })
        e.preventDefault();
        e.stopPropagation();
        return false;
    })
});
