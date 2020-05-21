<form <?php if(!empty($action)){ echo " action=\"".$action."\"";}?> <?php if(!empty($class)){ echo " class=\"".$class."\"";}?> <?php if(!empty($name)){ echo " id=\"".$name."\"";}?> <?php if(!empty($method)){ echo " method=\"".$method."\"";}?><?php if(!empty($enctype)){ echo " enctype=\"".$enctype."\"";}?>>
<?php foreach( $elements as $elem):?>
	
	<?php if( !empty($elem['template']) && empty($elem['wrapper'])):?>
		<?php
		if(strpos($elem['template']," ")){
			$pieces = preg_split("/[^\w.-]/",$elem['template'],-1,PREG_SPLIT_NO_EMPTY);	
			if(isset($pieces[1])){
				$tpl = GEARS.$pieces[0]."/templates/".$pieces[1];
			}	
			else {
				$tpl = dirname("/home/jtr/Sites/Github/cogear/gears/form/templates/form.tpl")."/".$pieces[1];
			}
		}
		else {
			$tpl = str_replace(GEARS,"",$elem['template']);
			if(strpos($tpl,"/") !== FALSE){
				$tpl = GEARS.trim($tpl,"/");
			}
			else {
				$tpl = dirname("/home/jtr/Sites/Github/cogear/gears/form/templates/form.tpl")."/".$tpl;
			}
		}
		if(!in_array(pathinfo($tpl,PATHINFO_EXTENSION),array("tpl","php"))) $tpl .= '.tpl';
		$alt_path = str_replace("gears","templates/simple/gears",$tpl);
		if(file_exists($alt_path)){
			$tpl = $alt_path;
		}
		if(!file_exists($tpl)) return;
		$file = $this->make_cpath($tpl);
		if($file->expired){
			$this->prepare($tpl,$file->path);
		}
		include($file->path);
		?>
	<?php else: ?>
		<?php switch( $elem['type']):?><?php case  'div':?>
				<?php if(isset($elem['open']) && $elem['open']):?>
					<div <?php if(!empty($elem['name'])){ echo " id=\"".$elem['name']."\"";}?> <?php if(!empty($elem['legend'])){ echo " class=\"".$elem['legend']."\"";}?>>
					<?php else: ?>
					</div><?php endif; ?>
			<?php break;?>
			
			<?php case  'title':?>
				<h1><?php if(isset($elem['name'])){ echo $elem['name'];} ?></h1>
			<?php break;?>
			<?php case  'description':?>
				<div class="description<?php if(isset($elem['class']) && $elem['class']):?> <?php if(isset($elem['class'])){ echo $elem['class'];} ?><?php endif; ?>"><?php if(isset($elem['name'])){ echo $elem['name'];} ?></div>
			<?php break;?>
			<?php case  'fieldset':?>
				<?php if(isset($elem['open']) && $elem['open']):?>
					<fieldset<?php if(!empty($elem['name'])){ echo "  id=\"".$elem['name']."\"";}?><?php if(!empty($elem['class'])){ echo "  class=\"".$elem['class']."\"";}?>>
					<?php if(!empty($elem['legend'])){ echo " <legend>".$elem['legend']."</legend>";}?>
				<?php else: ?>
					</fieldset><?php endif; ?>
			<?php break;?>	
			
			<?php case  'submit':?>
			<?php case  'reset':?>
			<?php case  'button':?>
				<span class="button">
					<input id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>'<?php if(!empty($elem['name'])){ echo "  name=\"".$elem['name']."\"";}?> type="<?php if(isset($elem['type'])){ echo $elem['type'];} ?>"<?php if(!empty($elem['class'])){ echo "  class=\"".$elem['class']."\"";}?><?php if(!empty($elem['value'])){ echo "  value=\"".$elem['value']."\"";}?><?php if(!empty($elem['onclick'])){ echo "  onclick=\"".$elem['onclick']."\"";}?><?php if(!empty($elem['accesskey'])){ echo "  accesskey=\"".$elem['accesskey']."\"";}?>/>
				</span>
			<?php break;?>	
			<?php case  'back':?>
				 <a href="javascript: history.go(-1)" id="back-link"><?php echo  t('edit back');?></a>
			<?php break;?>
			<?php case  'br':?><br/>
			<?php break;?>
			
			<?php case  'clear':?>
				<div class="clear"></div>
			<?php break;?>
			
			<?php default:?>
			<div class='field'>
				

				<?php if( isset($elem['label']) && $elem['label'] && !isset($elem['label_hidden']) && empty($elem['label_after']) or $elem['type'] == 'radio'):?>
				<label <?php if( in_array($elem['type'],explode(',','checkbox'))):?>class="small"<?php endif; ?> for="<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>">
					<?php if(isset($elem['label'])){ echo $elem['label'];} ?> <?php if(isset($elem['required']) && $elem['required']):?>*<?php endif; ?>
				</label>
					<?php switch( $elem['type']):?><?php case  'checkbox':?>
						<?php case  'radio':?>
						<?php case  'hidden':?>
						<?php break;?>
						<?php case  'input':?>
						<?php case  'text':?>
						<?php case  'password':?>
						<?php if(isset($elem['ajax']) && $elem['ajax']):?><small>(<a href="javascript:void(0)" onclick="update('<?php if(isset($elem['ajax']['url'])){ echo $elem['ajax']['url'];} ?>','<?php if(isset($elem['ajax']['update']) && $elem['ajax']['update']):?><?php if(isset($elem['ajax']['update'])){ echo $elem['ajax']['update'];} ?><?php else: ?><?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>-error<?php endif; ?>','<?php if(isset($elem['ajax']['where'])){ echo $elem['ajax']['where'];} ?>','<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>'<?php if(isset($elem['ajax']['regexp']) && $elem['ajax']['regexp']):?>,'<?php if(isset($elem['ajax']['regexp'])){ echo $elem['ajax']['regexp'];} ?>'<?php endif; ?>)"><?php if(isset($elem['ajax']['name'])){ echo $elem['ajax']['name'];} ?></a>)</small><?php endif; ?>
						<?php default:?>
						<br/><?php endswitch; ?><?php endif; ?>
				
				<?php if(isset($elem['template']) && $elem['template']):?>
					<?php
		if(strpos($elem['template']," ")){
			$pieces = preg_split("/[^\w.-]/",$elem['template'],-1,PREG_SPLIT_NO_EMPTY);	
			if(isset($pieces[1])){
				$tpl = GEARS.$pieces[0]."/templates/".$pieces[1];
			}	
			else {
				$tpl = dirname("/home/jtr/Sites/Github/cogear/gears/form/templates/form.tpl")."/".$pieces[1];
			}
		}
		else {
			$tpl = str_replace(GEARS,"",$elem['template']);
			if(strpos($tpl,"/") !== FALSE){
				$tpl = GEARS.trim($tpl,"/");
			}
			else {
				$tpl = dirname("/home/jtr/Sites/Github/cogear/gears/form/templates/form.tpl")."/".$tpl;
			}
		}
		if(!in_array(pathinfo($tpl,PATHINFO_EXTENSION),array("tpl","php"))) $tpl .= '.tpl';
		$alt_path = str_replace("gears","templates/simple/gears",$tpl);
		if(file_exists($alt_path)){
			$tpl = $alt_path;
		}
		if(!file_exists($tpl)) return;
		$file = $this->make_cpath($tpl);
		if($file->expired){
			$this->prepare($tpl,$file->path);
		}
		include($file->path);
		?>
				<?php else: ?>
					<?php switch( $elem['type']):?><?php case  'file':?>
						
						<?php if( !empty($elem['is_image']) && !empty($elem['value']) && is_array($elem['value'])):?>
							<?php if( isset($elem['thumbs'])):?>
							 <div class="thumbs">	
								 <?php foreach( $elem['thumbnails'] as $thumb):?>
								 <a href="<?php if(isset($elem['value']['src'])){ echo $elem['value']['src'];} ?>" target="_blank"><img src="<?php if(isset($thumb['src'])){ echo $thumb['src'];} ?>" width="<?php if(isset($thumb['width'])){ echo $thumb['width'];} ?>" height="<?php if(isset($thumb['height'])){ echo $thumb['height'];} ?>" border="0"/></a><?php endforeach; ?>
							 </div>
							<?php else: ?>
								<img src="<?php if(isset($elem['value']['src'])){ echo $elem['value']['src'];} ?>" width="<?php if(isset($elem['value']['width'])){ echo $elem['value']['width'];} ?>" height="<?php if(isset($elem['value']['height'])){ echo $elem['value']['height'];} ?>" border="0"/><br/><?php endif; ?>
							<?php elseif( !empty($elem['value'])):?>
							<img src="/gears/form/img/file.png"/> <a href="<?php if(isset($elem['value'])){ echo $elem['value'];} ?>"><?php if(isset($elem['value'])){ echo $elem['value'];} ?></a>
							<br/><?php endif; ?>
						 
						 <?php case  'input':?>
						 <?php case  'text':?>
						 <?php case  'password':?>
						<input id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>' type='<?php if(isset($elem['type'])){ echo $elem['type'];} ?>' class="<?php if(isset($elem['type'])){ echo $elem['type'];} ?> <?php if(isset($elem['class']) && $elem['class']):?><?php if(isset($elem['class'])){ echo $elem['class'];} ?><?php endif; ?>" <?php if( isset($elem['value'])):?>value='<?php if(isset($elem['value'])){ echo $elem['value'];} ?>'<?php endif; ?> <?php if(isset($elem['size']) && $elem['size']):?>size='<?php if(isset($elem['size'])){ echo $elem['size'];} ?>'<?php endif; ?> <?php if(isset($elem['onclick']) && $elem['onclick']):?>onclick="<?php if(isset($elem['onclick'])){ echo $elem['onclick'];} ?>"<?php endif; ?> <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>/> <?php if( $elem['type'] == 'file' && !empty($elem['value'])):?><label><input type="checkbox" onclick="if(this.checked) this.getParent().getPrevious().set('type','hidden').set('value',''); else this.getParent().getPrevious().set('type','file')"/><small><?php echo t(' !edit delete');?></small></label><?php endif; ?>
							<?php if(isset($elem['autocomplete']) && $elem['autocomplete']):?>
							 <script type="text/javascript">
							   window.addEvent('domready',function(){
								   autocomplete('<?php if(isset($elem['name'])){ echo $elem['name'];} ?>','<?php if(isset($elem['autocomplete']['url'])){ echo $elem['autocomplete']['url'];} ?>','<?php if(isset($elem['autocomplete']['multiple'])){ echo $elem['autocomplete']['multiple'];} ?>');
								   });
							 </script><?php endif; ?>
							<?php if(isset($elem['calendar']) && $elem['calendar']):?>
							 <script type="text/javascript">
							   window.addEvent('domready',function(){
								   new Calendar({'<?php if(isset($elem['name'])){ echo $elem['name'];} ?>':'Y-m-d'});
								   });
							 </script><?php endif; ?>
						<?php if(isset($elem['multi']) && $elem['multi']):?>
						<br/>
						<button onclick="if(this.getAllPrevious().filter('input').length > 1) this.getAllPrevious().filter('input').getLast().destroy();">-</button><button onclick="var last = this.getAllPrevious().filter('input').getLast(); var cloned = last.clone(); cloned.injectBefore(this.getPrevious().getPrevious()); cloned.set('id',last.get('id').replace(/\[\d+\]/i,'['+(this.getAllPrevious().filter('input').length)+']')).set('name',last.get('id').replace(/\[\d+\]/i,'['+(this.getAllPrevious().filter('input').length)+']'))">+</button><?php endif; ?>
						<?php break;?>
						
						<?php case  'textarea':?>
						<textarea id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>' <?php if(isset($elem['class']) && $elem['class']):?>class="<?php if(isset($elem['class'])){ echo $elem['class'];} ?>"<?php endif; ?> <?php if(isset($elem['rows']) && $elem['rows']):?>rows='<?php if(isset($elem['rows'])){ echo $elem['rows'];} ?>'<?php endif; ?> <?php if(isset($elem['cols']) && $elem['cols']):?>cols='<?php if(isset($elem['cols'])){ echo $elem['cols'];} ?>'<?php endif; ?> <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>><?php if(isset($elem['value']) && $elem['value']):?><?php if(isset($elem['value'])){ echo $elem['value'];} ?><?php endif; ?></textarea>
						<?php break;?>
						
						
						<?php case  'select':?>
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?><?php if(isset($elem['multiple']) && $elem['multiple']):?>[]<?php endif; ?>' <?php if(isset($elem['class']) && $elem['class']):?>class="<?php if(isset($elem['class'])){ echo $elem['class'];} ?>"<?php endif; ?> <?php if(isset($elem['multiple']) && $elem['multiple']):?>multiple<?php endif; ?> <?php if(isset($elem['onchange']) && $elem['onchange']):?>onchange='<?php if(isset($elem['onchange'])){ echo $elem['onchange'];} ?>'<?php endif; ?> <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options'] as $key=>$val):?>
							  <?php if( $val == ''):?><option></option>
							  <?php else: ?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( isset($elem['value']) && ((is_array($elem['value']) && in_array($key,$elem['value'])) OR $key == $elem['value'])):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endif; ?><?php endforeach; ?> 
						 </select>
						<?php break;?>
						
						<?php case  'datetime':?>
						  <div class="datetime">
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>[day]' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>[day]' class="day" <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options']['day'] as $key=>$val):?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( $key == $elem['value']['day']):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endforeach; ?> 
						 </select>
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>[month]' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>[month]' class="month" <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options']['month'] as $key=>$val):?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( $key == $elem['value']['month']):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endforeach; ?> 
						 </select>
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>[year]' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>[year]' class="year" <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options']['year'] as $key=>$val):?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( $key == $elem['value']['year']):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endforeach; ?> 
						 </select>
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>[hour]' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>[hour]' class="hour" <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options']['hour'] as $key=>$val):?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( $key == $elem['value']['hour']):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endforeach; ?> 
						 </select>
						  <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>[minute]' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>[minute]' class="minute" <?php if(isset($elem['disabled']) && $elem['disabled']):?>disabled<?php endif; ?>>
							 <?php foreach( $elem['options']['minute'] as $key=>$val):?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( $key == $elem['value']['minute']):?>SELECTED<?php endif; ?>><?php if(isset($val)){ echo $val;} ?></option><?php endforeach; ?> 
						 </select>
						 </div>
						<?php break;?>

						
						<?php case  'image_select':?>
						 <select id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?><?php if(isset($elem['multiple']) && $elem['multiple']):?>[]<?php endif; ?>' <?php if(isset($elem['class']) && $elem['class']):?>class="<?php if(isset($elem['class'])){ echo $elem['class'];} ?>"<?php endif; ?> <?php if(isset($elem['multiple']) && $elem['multiple']):?>multiple<?php endif; ?> <?php if(isset($elem['onchange']) && $elem['onchange']):?>onchange='<?php if(isset($elem['onchange'])){ echo $elem['onchange'];} ?>'<?php endif; ?>>
							 <?php foreach( $elem['options'] as $key=>$val):?>
							  <?php if( $val == ''):?><option></option>
							  <?php else: ?>
							   <option value='<?php if(isset($key)){ echo $key;} ?>' <?php if( isset($elem['value']) && ((is_array($elem['value']) && in_array($key,$elem['value'])) OR $key == $elem['value'])):?>SELECTED<?php endif; ?>><?php if( is_array($val)):?><?php if(isset($val[0])){ echo $val[0];} ?><?php else: ?><?php if(isset($val)){ echo $val;} ?><?php endif; ?></option><?php endif; ?><?php endforeach; ?> 
						 </select>
						 <?php foreach( $elem['options'][''] as $key=>$image):?>
						  <?php if( $image != ''):?> 
						  <a href="javascript:void(0);" id="<?php if(isset($elem['name'])){ echo $elem['name'];} ?>-<?php if(isset($key)){ echo $key;} ?>" class="selectIcons<?php if( isset($elem['value']) && ($key == $elem['value'] OR (is_array($elem['value']) && in_array($key,$elem['value'])))):?>_on<?php endif; ?>" onclick="selectIcon('<?php if(isset($elem['name'])){ echo $elem['name'];} ?>','<?php if(isset($elem['name'])){ echo $elem['name'];} ?>-<?php if(isset($key)){ echo $key;} ?>')"><img src="<?php if( is_array($image)):?><?php if(isset($image[1])){ echo $image[1];} ?><?php else: ?><?php if(isset($image)){ echo $image;} ?><?php endif; ?>" border="0" <?php if( is_array($image)):?>title="<?php if(isset($image[0])){ echo $image[0];} ?>"<?php endif; ?>/></a><?php endif; ?><?php endforeach; ?>
						 <div class='clear'></div>
						<?php break;?>
						
						
						<?php case  'checkbox':?>
						<input id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>' type='<?php if(isset($elem['type'])){ echo $elem['type'];} ?>' class="checkbox <?php if(isset($elem['class']) && $elem['class']):?><?php if(isset($elem['class'])){ echo $elem['class'];} ?><?php endif; ?>" <?php if( !empty($elem['checked']) OR !empty($elem['value'])):?>checked="checked"<?php endif; ?> <?php if(isset($elem['onclick']) && $elem['onclick']):?>onclick="<?php if(isset($elem['onclick'])){ echo $elem['onclick'];} ?>"<?php endif; ?> <?php if(isset($elem['disabled']) && $elem['disabled']):?> disabled<?php endif; ?><?php if(isset($elem['onchange']) && $elem['onchange']):?>onchange='<?php if(isset($elem['onchange'])){ echo $elem['onchange'];} ?>'<?php endif; ?> <?php if( isset($elem['value'])):?>value='<?php if(isset($elem['value'])){ echo $elem['value'];} ?>'<?php endif; ?>/>
						<?php break;?>
						
						
						<?php case  'radio':?>
							<?php foreach( $elem['options'] as $key=>$val):?>
								<div>
<?php if( empty($elem['label_after'])):?>
<label class="small" for='<?php if(isset($elem['id']) && $elem['id']):?><?php if(isset($elem['id'])){ echo $elem['id'];} ?><?php else: ?><?php if(isset($elem['name'])){ echo $elem['name'];} ?><?php endif; ?>-item-<?php if(isset($key)){ echo $key;} ?>'><?php if(isset($val)){ echo $val;} ?></label><?php endif; ?>
<input type="<?php if(isset($elem['type'])){ echo $elem['type'];} ?>" name="<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>" id="<?php if(isset($elem['id']) && $elem['id']):?><?php if(isset($elem['id'])){ echo $elem['id'];} ?><?php else: ?><?php if(isset($elem['name'])){ echo $elem['name'];} ?><?php endif; ?>-item-<?php if(isset($key)){ echo $key;} ?>" value="<?php if(isset($key)){ echo $key;} ?>" class="radio <?php if(isset($elem['class']) && $elem['class']):?><?php if(isset($elem['class'])){ echo $elem['class'];} ?><?php endif; ?>" <?php if( isset($elem['value']) && $key == $elem['value']):?>checked="checked"<?php endif; ?> <?php if(isset($elem['onclick']) && $elem['onclick']):?>onclick="<?php if(isset($elem['onclick'])){ echo $elem['onclick'];} ?>"<?php endif; ?> <?php if(isset($elem['onchange']) && $elem['onchange']):?>onchange='<?php if(isset($elem['onchange'])){ echo $elem['onchange'];} ?>'<?php endif; ?> /> 

<?php if( !empty($elem['label_after'])):?><label class="small" for='<?php if(isset($elem['id']) && $elem['id']):?><?php if(isset($elem['id'])){ echo $elem['id'];} ?><?php else: ?><?php if(isset($elem['name'])){ echo $elem['name'];} ?><?php endif; ?>-item-<?php if(isset($key)){ echo $key;} ?>'><?php if(isset($val)){ echo $val;} ?></label><?php endif; ?>
</div><?php endforeach; ?> 
						<?php break;?><?php endswitch; ?><?php endif; ?>	
	<?php if( !empty($elem['label']) && empty($elem['label_hidden']) && !empty($elem['label_after']) && $elem['type'] != 'radio'):?><label <?php if( $elem['type'] == 'checkbox'):?>class="small"<?php endif; ?>  for='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>'><?php if(isset($elem['label'])){ echo $elem['label'];} ?> <?php if(isset($elem['required']) && $elem['required']):?>*<?php endif; ?> </label><?php endif; ?>
	
		<?php if(isset($elem['description']) && $elem['description']):?><div class="description"><?php if(isset($elem['description'])){ echo $elem['description'];} ?></div><?php endif; ?>
		<div class="error" id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>-error'><?php if(isset($elem['error'])){ echo $elem['error'];} ?></div>
	</div>
	<?php if(isset($elem['br']) && $elem['br']):?><br/><?php endif; ?>
	<?php break;?>
		
	<?php case  'hidden':?>
	<input id='<?php echo  isset($elem['id']) ? $elem['id'] : $elem['name'];?>' name='<?php if(isset($elem['name'])){ echo $elem['name'];} ?>' type='<?php if(isset($elem['type'])){ echo $elem['type'];} ?>' class='hidden' value='<?php if(isset($elem['value'])){ echo $elem['value'];} ?>'/>
	<?php break;?>		
		</div><?php endswitch; ?><?php endif; ?><?php endforeach; ?>
</form>

<?php if(isset($js_validation) && $js_validation):?>
		<script type="text/javascript">
		formCheck = new FormCheck('<?php echo  isset($id) ? $id : $name;?>',{
		msg: <?php if(isset($errors_msg)){ echo $errors_msg;} ?>,
		display : {
			errorsLocation: (Browser.Engine.version < 5 && Browser.Engine.trident) ? 3 : <?php if(isset($errors_location)){ echo $errors_location;} ?>
		}
		});
		</script><?php endif; ?>
<?php if(isset($md5) && $md5):?>
<script type="text/javascript">
	$('<?php echo  isset($id) ? $id : $name;?>').addEvent('submit', function()  {
		if(this.isValid && this.isValid === false) {
			return false;
		}
		else {
			this.getElements(".md5").each(function(elem){
				var hidden = new Element('input').set('type','hidden').set('name',elem.id).set('id',elem.name+'_md5').set('value',$chk(elem.value) ? hex_md5(hex_md5(elem.value)) : elem.value).inject(elem,'after');
				elem.removeProperty('name');
			});
		 }
		});
	
</script><?php endif; ?>
<?php if(isset($via_cookie) && $via_cookie):?>
<script type="text/javascript">
	$('<?php echo  isset($id) ? $id : $name;?>').addEvent('submit', function()  {
		if($defined(this.isValid) && !this.isValid) {
			return false;
		}
		else {
			this.getElements(".via_cookie").each(function(elem){
			    if(elem.get('value')) var value = elem.get('value');
			    if($(elem.id+'_md5')) var value = $(elem.id+'_md5').get('value');
			    if(value) Cookie.write(elem.id,value,{domain: "."+url, path: "/"});
			    elem.removeProperty('name');
			});
		 }
		});
</script><?php endif; ?>	
