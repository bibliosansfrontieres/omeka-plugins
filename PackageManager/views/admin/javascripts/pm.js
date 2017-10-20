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
        var disabled = false; //($('.pm_batch_add_to_package input[type=submit]:disabled').length==0);
        $('.pm_batch_add_to_package select').prop('disabled', disabled);
        $('.pm_batch_add_to_package span').css('opacity', disabled?0.35:1);
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

		if($(this).hasClass('submit_add_to_package')){
			$post_data += '&submit_add_to_package=true&pm_target_package='+$('.pm_target_package').val();
		}
		
		$.post($target, $post_data,
		   function(data) {
				var obj = jQuery.parseJSON(data);
				var data = obj.result;
				var status = obj.status;
				if(status=="ok" || status=="redirect"){
					if(jQuery('.batch-all-toggle').hasClass('active')) jQuery('.batch-all-toggle:first').trigger('click');
					if(jQuery('.batch-edit-heading input[type=checkbox]').is(':checked')) jQuery('.batch-edit-heading input[type=checkbox]:first').trigger('click');
					if(jQuery('.batch-edit-check input:checked').length > 0) jQuery('.batch-edit-check input:checked').prop('checked', false);
                    if(status=="ok") {swal(data, null, "success");}
				}
				else{
					swal(data, null, "error");
				}
           		if(status=="redirect"){
			   		window.location = data;
               	}
		   }
		   
		);
		
		return false;
	});
});