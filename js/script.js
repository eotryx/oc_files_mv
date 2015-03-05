/**
 * ownCloud - filesmv
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

(function ($, OC) {

	$(document).ready(function () {
		if(/(public)\.php/i.exec(window.location.href)!=null) return; // escape when the requested file is public.php
		$('#echo').click(function () {
			var url = OC.generateUrl('/apps/filesmv/echo');
			var data = {
				echo: $('#echo-content').val()
			};

			$.post(url, data).success(function (response) {
				$('#echo-result').text(response.echo);
			});

		});
		//-----------------------------------
		var img = OC.imagePath('core','actions/play');
		if (typeof FileActions !== 'undefined') {
			FileActions.register('all', t('files_mv','Move'),OC.PERMISSION_READ, OC.imagePath('core','actions/play'), function(file) {
				if (($('#mvDrop').length > 0)) {
					$('#mvDrop').detach();
				}
				else{
					mvCreateUI(true,file,false);
				}
			});
		};
		var el = $('#headerName .selectedActions');
		$('<a class="move" id="move" href=""><img class="svg" src="'+img+'" alt="'+t('files_mv','Move')+'">'+t('files_mv','Move')+'</a>').appendTo(el);
		el.find('.move').click(keks)
			//alert(Filelist.$el)


			$(this).click(function(event){
				if( (!($(event.target).hasClass('ui-corner-all')) && $(event.target).parents().index($('.ui-menu'))==-1) &&
					(!($(event.target).hasClass('mvUI')) && $(event.target).parents().index($('#mvDrop'))==-1)){
						$('#mvDrop').detach();
					}
			});
		$('#mvForm').live('submit',function(){
			var dest = $('#dirList').val();
			var file = $('#dirFile').val();
			var dir  = $('#dir').val();
			var copy = $('#dirCopy').attr('checked')=='checked';
			$.ajax({
				type: 'POST',
				url: OC.linkTo('files_mv','ajax/move.php'),
				cache: false,
				data: {dir: dir, src: file, dest: dest, copy: copy},
				success: function(data){
					//remove each moved file
					console.log(data.name, data);
					$.each(data.name,function(index,value){
						console.log("each",index,value)
						FileList.remove(value);
					//procesSelection();
					});
					// show error messages when caught some
					if(data.status=="error"){
						$('#notification').hide();
						$('#notification').text(data.message);
						$('#notification').fadeIn();
					}
					console.log(data)
				}
			});
			$('#dirList').autocomplete("close");
			$('#mvDrop').detach();
			return false;
		});
	});

})(jQuery, OC);
