unloadStop = true;
if($('save')) $('save').addEvent('click',function(){
	unloadStop = false;
});
if($('publish')) $('publish').addEvent('click',function(){
	unloadStop = false;
});
if($('delete')) $('delete').addEvent('click',function(){
	unloadStop = false;
});
window.onbeforeunload = function(){
	if(!unloadStop) return;
	return '';
}
