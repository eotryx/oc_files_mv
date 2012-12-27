$(document).ready(function() {
	var img = OC.imagePath('core','actions/play');
	if (typeof FileActions !== 'undefined') {
		FileActions.register('all', t('files_mv','Move'),OC.PERMISSION_UPDATE, function(){return OC.imagePath('core','actions/play')}, function(file) {
			if (($('#mvDrop').length > 0)) {
				$('#mvDrop').detach();
			}
			else{
				mvCreateUI(true,file);
			}
		});
	};
	$('<a class="move" id="move" title="'+t('files_mv','Move')+'" href="#"><img class="svg" src="'+img+'" alt="Download"></a>').appendTo('#headerName .selectedActions');

	$('#move').click(function(event){
		if($('#mvDrop').length>0){
		$('#mvDrop').detach();
		return;
		}
		//event.preventDefault();
		event.stopPropagation();
		var files = getSelectedFiles('name');
		var file='';
		for( var i=0;i<files.length;++i){
		file += files[i]+';';
		}
		mvCreateUI(false,file);
		});
	$(this).click(function(event){
		if(!($(event.target).hasClass('mvUI')) && $(event.target).parents().index($('#mvDrop'))==-1){
			$('#mvDrop').detach();
		}
	});
	$('#dirList').live('change',function(){
		var dest = $('#dirList').val();
		var file = $('#dirFile').val();
		var dir  = $('#dir').val();
		$.ajax({
			type: 'POST',
			url: OC.linkTo('files_mv','ajax/move.php'),
			cache: false,
			data: {dir: dir, src: file, dest: dest},
			success: function(data){
				if(data.status=="success"){
				$.each(data.name,function(index,value){
					FileList.remove(value);
					procesSelection();
					});
				}
			}
		});
		$('#mvDrop').detach();
	});
});
function mvCreateUI(local,file){
	var html = '<div id="mvDrop" class="mvUI">';
	html += '<select data-placeholder="'+t('files_mv','Destination directory')+'" style="width:200px;" id="dirList" class="chzen-select">';
	html += '<option value=""></option>';
	html += '</select><input type="hidden" id="dirFile" value="'+file+'" />';
	html += '<strong id="mvWarning"></strong>';
	html += '</div>';
	if(local){
		$(html).appendTo($('tr').filterAttr('data-file',file).find('td.filename'));
	}
	else{
		$(html).addClass('mv').appendTo('#headerName .selectedActions');
	}
	$.getJSON(OC.linkTo('files_mv', 'ajax/autocompletedir.php'), {file: $('#dir').val()+'/'+file}, function(dir){
		var actDir = $('#dir').val();
		if(dir){
			$.each(dir, function(index, row){
				if($(row).val()!=actDir) $(row).appendTo('#dirList');
			});
			$('#dirList').trigger('liszt:updated');
		}
	});
	$('#dirList').chosen();
}
