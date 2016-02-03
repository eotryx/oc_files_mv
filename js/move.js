/**
 * ownCloud - files_mv
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author eotryx <mhfiedler@gmx.de>
 * @copyright eotryx 2015
 */

if(!OCA.Files_mv){
	/**
	 * Namespace for the files_mv app
	 * @namespace OCA.Files_mv
	 */
	OCA.Files_mv = {};
}
/**
 * @namespace OCA.Files_mv.move
 */
OCA.Files_mv.Move = {
	/**
	 * @var string appName used for translation file
	 * as transifex uses the github project name, use this instead of the appName
	 */
	appName: 'oc_files_mv',
	registerFileAction: function(){
		var img = OC.imagePath('core','actions/play');
		OCA.Files.fileActions.register(
			'all',
			t(this.appName,'Move'),
			OC.PERMISSION_READ,
			OC.imagePath('core','actions/play'),
			function(file) {
				if (($('#mvDrop').length > 0)) {
					$('#mvDrop').detach();
				}
				else{
					OCA.Files_mv.Move.createUI(true,file,false);
				}
			}
		);
		//TODO: what?
		var el = $('#headerName .selectedActions');
		$('<a class="move" id="move" href=""><img class="svg" src="'+img+'" alt="'+t(this.appName,'Move')+'">'+t(this.appName,'Move')+'</a>').appendTo(el);
		el.find('.move').click(this.keks)
	},

	initialize: function(){
		this.registerFileAction();

		//TODO: wtf does this?
		$(this).click(function(event){
			if(
				(!
					($(event.target).hasClass('ui-corner-all'))
					&& $(event.target).parents().index($('.ui-menu'))==-1
				)
				&& (!
					($(event.target).hasClass('mvUI'))
					&& $(event.target).parents().index($('#mvDrop'))==-1
				)
			){
				$('#mvDrop').detach();
			}
		});

		$('#mvForm').live('submit',this.submit);
	},	

	submit: function(){
		var dest = $('#dirList').val();
		var file = $('#dirFile').val();
		var dir  = $('#dir').val();
		var copy = $('#dirCopy').attr('checked')=='checked';
		$.ajax({
			type: 'POST',
			url: OC.generateUrl('/apps/files_mv/move'),
			cache: false,
			data: {srcDir: dir, srcFile: file, dest: dest, copy: copy},
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
					OC.Notification.showTemporary(data.message);
				}
				console.log(data)
			}
		});
		$('#dirList').autocomplete("close");
		$('#mvDrop').detach();
		return false;
	},
	
	/**
	 * TODO: why is this called keks? And what does it do?
	 */
	keks: function(event){
		// move multiple files
		event.stopPropagation();
		event.preventDefault();
		if($('#mvDrop').length>0){
			$('#mvDrop').detach();
			return;
		}
		var files = FileList.getSelectedFiles();
		var file='';
		for( var i=0;i<files.length;++i){
			file += (files[i].name)+';';
		}
		OCA.Files_mv.Move.createUI(false,file,false);
		return false;
	},

	/**
	 * draw the move-dialog; if file is readonly, activate copy
	 *
	 * @local - true for single file, false for global use
	 * @file - filename in the local directory
	 */
	createUI: function (local,file){
		// check for update permission
		file2 = file.split(';');
		var permUpdate = true;
		for(var i=0;i<file2.length;++i){
			if(file2[i]== "") continue;
			var tmp = $('tr[data-file="'+file2[i]+'"]');
			if((OC.PERMISSION_UPDATE&parseInt(tmp.attr('data-permissions')))==0){ // keine updaterechte
				permUpdate=false;
				break;
			}
		}

		//set copy as default when current directory is located in shared dir 
		var copy = ($('#dir').val().substring(0,7)=="/Shared"); 

		var html = '<div id="mvDrop" class="mvUI">';
		html += '<form action="#" id="mvForm"><input type="checkbox" id="dirCopy"';
		if(!permUpdate || copy) html += ' checked';
		if(!permUpdate) html += ' disabled';
		html += '></input><label for="dirCopy">'+t(this.appName,'Copy')+'</label><br>';

		html += '<input id="dirList" placeholder="'+t(this.appName,'Destination directory')+'"><br>';
		html += '<input type="hidden" id="dirFile" value="'+file+'" />';
		html += '<input type="submit" id="dirListSend" value="'+t(this.appName,'Move')+'" />';
		html += '<strong id="mvWarning"></strong></form>';
		html += '</div>';
		if(local){
			$(html).appendTo($('tr').filterAttr('data-file',file).find('td.filename'));
		}
		else{
			$(html).addClass('mv').appendTo('#headerName .selectedActions');
		}
		$('#dirList').focus(function(){
			$('#dirList').autocomplete("search","/")
		});
		// get autocompletion names
		$('#dirList').autocomplete({minLength:0, 
			source: function(request, response) {
				$.post(
					OC.generateUrl('/apps/files_mv/complete'),
					{
						file: $('#dir').val()+'/'+file,
						StartDir: $('#dirList').val(), // using current input to allow access to more than n levels depth
					},
					function(dir){
						$('#dirList').autocomplete('option','autoFocus', true);
						if(dir['status'] && dir['status']=='error'){
							response(dir['message']);
						}
						else{
							response(dir);
						}
					},
					'json'
				);
			}, 
		});
		$('#dirList').focus();
	},
}
$(document).ready(function() {
	/**
	 * check whether we are in files-app and we are not in a public-shared folder
	 */
	if(!OCA.Files){ // we don't have the files app, so ignore anything
		return;
	}
	if(/(public)\.php/i.exec(window.location.href)!=null){
		return; // escape when the requested file is public.php
	}

	/**
	 * Init Files_mv
	 */
	OCA.Files_mv.Move.initialize();
});
