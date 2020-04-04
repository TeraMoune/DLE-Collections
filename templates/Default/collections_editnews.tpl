<div id="collections-menu" class="edit">
<button type="button" class="mfp-close">×</button>
<center>
<button class="btn btn-green" style="margin-bottom:10px;" onclick="saveCollections();">Отправить</button>
</center>
<div>
{collections}
</div>
</div>
<script>
function saveCollections(){
	ShowLoading('');
	
	var collections = $('#collections').val() ? $('#collections').val().join(',') : '';
	
	$.post('engine/ajax/controller.php?mod=collections_menu', { news_id: {id}, action: 'save', collections: collections, user_hash: dle_login_hash }, function(data){
	
		if( data == 'error' ){
			alert(data);
		} else {
				  
			$.magnificPopup.close();					  
			
		}	
	
	HideLoading('');
	
	});

};
$(function(){
	$('#collections').chosen({allow_single_deselect:true, no_results_text: 'Ничего не найдено'});
});
</script>
