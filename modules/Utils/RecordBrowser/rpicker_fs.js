rpicker_fs_init = function(id,checked,path){
	checkbox = jQuery('#leightbox_rpicker__'+id);

	if(checked==1)
		checkbox.prop('checked', true);
	else
		checkbox.prop('checked', false);

	checkbox.on('click', function(e){
		new Ajax.Request('modules/Utils/RecordBrowser/RecordPickerFS/select.php', {
			method: 'post',
			parameters:{
				select: this.checked,
				row: id,
				path: Object.toJSON(path),
				cid: Epesi.client_id
			}
		});
	});
}
