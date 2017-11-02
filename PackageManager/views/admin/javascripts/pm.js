jQuery(document).ready(function($) {
	if("undefined" != typeof swal){
		// swal.setDefaults({ html: true });
	}
	else{
		swal = function(){
			return false;
		}
	}
	
	$(".table-actions.batch-edit-option").each(function(){
 		$(this).append('<input type="submit" class="small batch-action button pm_add_to_cart" name="submit-batch-cart" value="Add to cart">');
	});

    $(".table-actions.batch-edit-option").first().append($('.pm_batch_add_to_package'));

    $("table#items tbody input[type=checkbox]").change(function(){
        var checked = false;
        $("table#items tbody input[type=checkbox]").each(function() {
            if (this.checked) {
                checked = true;
                return false;
            }
        });
        $('.pm_batch_add_to_package select').prop('disabled', !checked);
        $('.pm_batch_add_to_package span').css('opacity', checked?1:0.35);
    });


	$('#content').on('click', '.pm_add_to_cart', function(e){
		e.preventDefault();
		e.stopPropagation();
		
		var $post_data = "";
		var $target = Omeka.pm_cart_url;
		if($(this).hasClass('batch-action')){
			$post_data = $(this).closest("form").serialize();
		}
		else $target = $(this).attr('href');
		
		$.post($target, $post_data,
		   function(data) {
				var obj = jQuery.parseJSON(data);
				var data = obj.result;
				var status = obj.status;
				if(status=="ok"){
					if(jQuery('.batch-all-toggle').hasClass('active')) jQuery('.batch-all-toggle:first').trigger('click');
					if(jQuery('.batch-edit-heading input[type=checkbox]').is(':checked')) jQuery('.batch-edit-heading input[type=checkbox]:first').trigger('click');
					if(jQuery('.batch-edit-check input:checked').length > 0) jQuery('.batch-edit-check input:checked').prop('checked', false);
					swal(data, null, "success");
				}
				else{
					swal(data, null, "error");
				}					
		   }
		   
		);
		
		return false;
	});

    $('input[name="submit-batch-add-to-package"]').click(function(event) {
        event.preventDefault();
        var itemsIds = [];
        $("table#items tbody input[type=checkbox]:checked").each(function() {
            itemsIds.push(this.value);
        });
        var targetPackageId = $('.pm_target_package').val();
        if(targetPackageId<=0){
            alert("Please choose a target package first.")
            return false;
        }
        if($('.batch-edit-check input:checked').length > 0) $('.batch-edit-check input:checked').prop('checked', false);
        $.post(
        	Omeka.pm_addItems_url.replace("%d", targetPackageId),
            {itemsIds: itemsIds},
            function(data) {
                var obj = jQuery.parseJSON(data);
                var status = obj.status;
                var result = obj.result;
                if(status=="error"){
                    swal(data, null, "error");
                }else{
                    if(jQuery('.batch-all-toggle').hasClass('active')) jQuery('.batch-all-toggle:first').trigger('click');
                    if(jQuery('.batch-edit-heading input[type=checkbox]').is(':checked')) jQuery('.batch-edit-heading input[type=checkbox]:first').trigger('click');
                    if(jQuery('.batch-edit-check input:checked').length > 0) jQuery('.batch-edit-check input:checked').prop('checked', false);
                    if(status=="redirect"){window.location = result;}
                }
            }
        );
    });
});