window.addEvent('domready',function(){
	$$('#content code').each(function(code){
		new Lighter(code,{altLines: 'hover',indent: '4',mode:'ol',fuel:'php'});	
	});
})