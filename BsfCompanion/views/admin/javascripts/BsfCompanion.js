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
        //alert('Content has been copied to clipboard. Use Ctrl-V to paste it.');
    }else{
        alert('Unable to copy content to clipboard. '+copyFailureReason);
    }
}
