function vote(action,type,id){
	new Request.JSON({
	url: '/ajax/points/vote/',
	data: 'action='+action+'&type='+type+'&id='+id,
	onComplete: function(re){
		msg(re.msg,re.success);
		if(re.success){
			switch(action){
				case 'up':
					$('vote-down-'+type+'-'+id).getParent().addClass('passive');
					break;
				case 'down':
					$('vote-up-'+type+'-'+id).getParent().addClass('passive');
				break;
			}
			$('votes-'+type+'-'+id).set('text',re.points);
			if(re.points >= 0){
				$('votes-'+type+'-'+id).removeClass('bad').addClass('good');
			}
			else {
				$('votes-'+type+'-'+id).removeClass('good').addClass('bad');
			}
			if($('cpanel-charge')){
				var charge_info = $('cpanel-charge').getElements('a');			
				charge_info[0].getElement('span').set('text',re.charge);
				charge_info[1].set('text',re.charge_plural);
			}
			if($('points-counter-'+type+'-'+id) && re.points_counter){
				$('points-counter-'+type+'-'+id).set('text',re.points_counter);
			}
		}
	}
	}).post();
}

function add_charge(uid){
	var charge = prompt(lang.points.add_charge,10);
	if(charge){
		new Request.JSON({
		url: '/ajax/points/add_charge/',
		data: 'uid='+uid+'&charge='+charge,
		onComplete: function(re){
			if(re.success){
				msg(lang.points.add_charge_success);
			}
			else msg(lang.points.add_charge_failure); 
		} 
		}).post();
	}
}