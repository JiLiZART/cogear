<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Templates manager
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Templater
* @version		$Id$
*/
class Template
{
	private $output = array();
	private $compile_dir = './engine/cache/templates/';
	private $vars = array();
	private $glob_vars = array();
	private $template = FALSE;
	public  $stop = FALSE;
	private $wrap_start = FALSE;
      private $wrapper_start = FALSE;
      private $wrapper_end = 800;
      private $wrapper = FALSE;
      private $global_template = '';
	
	/**
	* Constructor
	*/
	public function Template(){
		// If cache is completely deleted -- create compile dir
		if(!file_exists($this->compile_dir)) @mkdir($this->compile_dir,0777,TRUE);
		$CI =& get_instance();
		if(isset($CI->site)) $this->global_template = $CI->site->template;
	}
	
	/**
	* Parse template
	*
	* @param	mixed string/array template_path/data_to_add
	* @param	mixed int/array can_be_position or data
	* @param	mixed int/boolean position to place content or return content
	* @param	mixed	boolean/int replace template at position or replace via range
	* @return	mixed
	*/
	public function parse($template,$data = FALSE,$position = FALSE,$replace = FALSE) {
	        $CI =& get_instance();
	        $this->assign_by_ref('%CI', $CI);
	        $date_format = $CI->gear->date_format ? $CI->gear->date_format : $CI->site->date_format;
	        $this->assign_by_ref('%date_format',$date_format);
			switch(gettype($data)){
				case 'array':
				case 'object':
					if($this->stop) return FALSE;
					$this->assign((array)$data);		
					break;
				case 'boolean':
				case 'integer':
				case 'string':
					if($position === FALSE) $position = $data;
					break;
			}
			$name = $template;
			if(is_string($template) && file_exists($template) && $data !== $position){
				 $CI->benchmark->mark('template_parse_'.$template.'_start');
 				 $this->assign('%tpl','http://'.$CI->site->url.'/templates/'.$this->global_template.'/');
				 $template = $this->fetch($template);
				 $CI->benchmark->mark('template_parse_'.$template.'_end');
				 $this->clear_all_assign();
				 $parsed = TRUE;
			}

			switch(gettype($position)){
				case 'boolean':
					if($position === TRUE){
						$this->clear_all_assign();
						return is_array($template) ? reset($template) : $template;
					}
					else if($position === FALSE) {
						$this->output[] = isset($parsed) ? array($template) : $template;
						if(is_string($replace)){
							$this->wrapper_start = end(array_keys($this->output));
						}
					}
				break;
				case 'string':
					$position = intval($position);
				case 'integer':
					$template = isset($parsed) ? array($template) : $template;
					if(isset($this->output[$position])){
							if($replace){
								$this->output[$position] = $template;
							}
							else {
								array_insert(&$this->output,$template,$position);
							}
						}
					else {
						$this->output[$position] = $template;
					}


					switch(gettype($replace)){
						case 'integer':
						$this->wrapper_start = (int)$position;
						$this->wrapper_end = (int)$replace;
						$this->wrapper = $template;
						break;
						case 'string':
						$this->wrapper_start = $position;
						$this->wrapper_end = 'all';
						$this->wrapper = $template;
						break;
					}
				break;
			}	
		    return TRUE;		
    }
	
	/**
	* Compile all templates
	*
	* @param	array
	* @param	string
	* @return	void
	*/
	public function compile($data = FALSE,$output = ''){
		$CI =& get_instance();
		ksort($this->output);
		 if($data) $this->assign($data);
		 if(is_string($this->wrapper_end) && $this->wrapper_end == 'all'){
				$output = $this->fetch(array_shift($this->output));		 
				$output .= $this->fetch(array_pop($this->output));		 
				$output = preg_replace('#<body>.*</body>#imsu','<body>'.(is_array($this->wrapper) ? reset($this->wrapper) : $this->wrapper).'</body>',$output);
				$output = trim($output);
		 }
		 else {
			 foreach($this->output as $key=>$template){
				  if($this->wrapper_start && $key > $this->wrapper_start && $key <= $this->wrapper_end){
					  continue;
				  }
				  elseif($this->wrapper_start && $key== $this->wrapper_start) {
					  $output .= is_array($this->wrapper) ? reset($this->wrapper) : $this->wrapper;
				
				  }
				  else if(is_array($template)){
				   $output .= reset($template);
				  }
				  else {
				   $output .= $this->fetch($template);
				  }
			 }
		 }
		 if($CI->uri->subdir){
			 foreach(array('uploads','gears') as $dir){
				 $output = str_replace(array('"/'.$dir,'\'/'.$dir),array('"/'.$CI->uri->subdir.'/'.$dir,'\'/'.$CI->uri->subdir.'/'.$dir),$output);
			 }
		 }
		if($result = $CI->_hook('*','output',FALSE,array($output))){
			$output = $result[0];
		} 
		$CI->output->append_output($output);
	} 
  
	/**
	* Clear data
	*
	* @return	void
	*/
	private function clear(){
		$this->output = array();
		$this->clear_all_assign();
	}
	/**
	* Clear assigned data
	*/
	private function clear_all_assign(){
	    $this->vars = array();
	}
	
	/**
	* Assign variable
	*
	* @param	string	Key.
	* @param	mixed	Value.	
	*/
	private function assign($key,$value = FALSE){
	    if(is_array($key)){
		    foreach($key as $k=>$value){
			    $this->assign($k,$value);
		    }
	    }
	    else{
	     if($key[0] == '%'){
		     $this->glob_vars[substr($key,1)] = $value;
	     }
	     else $this->vars[$key] = $value;
	    }
	}
	
	/**
	* Assign variable by reference
	*
	* @param	string	Key.
	* @param	mixed	Value.	
	*/
	private function assign_by_ref($key,$value){
	     if($key[0] == '%'){
		     $this->glob_vars[substr($key,1)] =& $value;
	     }
	     else $this->vars[$key] =& $value;
	}    
	
	/**
	* Fetch template
	*
	* @param	string	File path.
	* @param	array	Variables.
	* @param	boolean  Clean.
	* @return	string	Compiled template.
	*/
	private function fetch($path,$vars = FALSE,$clean = FALSE){
	    if(!file_exists($path)) return;
	    if(strpos($path,'gears') !== FALSE){
	    	    $alt_path = str_replace('gears','templates/'.$this->global_template.'/gears',$path);
		    if(file_exists($alt_path)){
			    $path = $alt_path;
		    }
	    }
    	    $ext = pathinfo($path,PATHINFO_EXTENSION);
	    if($ext == 'php'){
			return $this->process($path,$vars,$clean);
	    }
	    else {
		    $file = $this->make_cpath($path);
		    $this->template = $path;
	    }
	    if(!$file->expired){
		    return $this->process($file->path,$vars,$clean);
	    }
	    else {
		    $this->prepare($path,$file->path);
		    return $this->process($file->path,$vars,$clean);
	    }
	}
	
	/**
	* Create compiled filename
	*
	* @param	string	Original path.
	* @return	object
	*/
	private function make_cpath($path){
	    $mod = filemtime($path);
	    $filename = pathinfo($path,PATHINFO_FILENAME);
	    $ext = pathinfo($path,PATHINFO_EXTENSION);
	    $file = new stdClass;
	    $file->path = $this->compile_dir.basename(dirname(dirname($path))).'_'.$filename.'.'.$ext;
	    if(file_exists($file->path)){
		    $cmod = filemtime($file->path);
		    $file->expired = $mod > $cmod ? TRUE : FALSE;
	    }
	    else {
		    $file->expired = TRUE;
	    }
	    $file->expired  = TRUE;
	    return $file;
	}
	
	/**
	* Process template
	*
	* @param	string	Path to compiled template
	* @param	array	Variables.
	* @param	boolean	Clean assign action.
	* @return	string
	*/
	private function process($include_file_path,$vars = FALSE,$clear = FALSE){
		    ob_start();
		    extract($vars ? $vars : $this->vars);
		    extract($this->glob_vars);
		    include($include_file_path);
		    $output = ob_get_clean();
		    if($clear) $this->clear_all_assign();
		    return $output;
	}
	
	/**
	* Prepare template
	*
	* @param	string	Path to file.
	*/
	private function prepare($path,$cpath){
		$code = file_get_contents($path);
		
		/**
		* Parse code
		*/
		$code = preg_replace_callback('#(\$[\w->\[\]\'"]+\.[\w.]+)#i',array($this,'fetch_dot_array'),$code);
		$code = preg_replace_callback('#(\$[\w.\[\]\'"->]+)\|(.[^}]+)#i',array($this,'fetch_modificators'),$code);
		// Short if isset statment like "[width='{$width}']"
		$code = preg_replace_callback('#\[(.[^\[\]]*){(\$.[^}]+)}(.[^\[\]]*)\]#iU',array($this,'fetch_optional'),$code);
		$code = preg_replace_callback('#{\s*include\s*file=["|\']?(.*)["|\']?}#iU',array($this,'fetch_include'),$code);
		//if(strpos($path,'form')) debug($code);
		$search = array(
		// Strip comments
		'#{\*(.*)\*}#imsU',
		// Parse variables
		/* '#(?<=[}|\(]){\$(\S[^()+-=/%]+)}#iU', */
		'#{\$(\S[^()+=/%]+)}#iU',
		// Fix switch
		'#({switch.*})\s+({case)#msU',
		'#{if\s*\(?\s*(\$\S*)\s*\)?\s*}#isU',
		'#{if\s*\(?\s*!(\$\S*)\s*\)?\s*}#isU',
		'#{else\s?if\s*\(?\s*(\$\S*)\s*\)?\s*}#isU',
		'#{else\s?if\s*\(?\s*!(\$\S*)\s*\)?\s*}#isU',
		// Simulate 10-level nesting
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*\((.*)\)\s*}\s*(.*)\s*{/\\1}#imsU',
		// Simulate 10-level nesting
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{(if|foreach|for|switch)\s*(.*)\s*}\s*(.+)\s*{/\\1}#imsU',
		'#{else\s*if\s*\((.*)\)}#iU',
		'#{else\s*if\s*(.*)}#iU',
		'#{else}#iU',
		'#{case\s*(.*)}#iU',
		'#{break}#iU',
		'#{default}#iU',
		'#{\?(.*)}#U',
		'#{!(.*)}#U',
		'#{_\s*!?(.*)}#U',
		);
		$replace = array(
		'',
		/* '<?php echo $\\1;?>', */
		'<?php if(isset($\\1)){ echo $\\1;} ?>',
		// Fix switch
		'$1$2',
		'{if(isset(\\1) && \\1)}',
		'{if(isset(\\1) && !\\1)}',
		'{elseif(isset(\\1))}',
		'{elseif(isset(\\1) && !\\1)}',
		// 5-level nesting x 2
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php $1($2):?>$3<?php end$1; ?>', 
		'<?php elseif(\\1):?>',
		'<?php elseif(\\1):?>',
		'<?php else: ?>',
		'<?php case $1:?>',
		'<?php break;?>',
		'<?php default:?>',
		'<?php echo \\1;?>',
		'<?php \\1;?>',
		'<?php echo t(\'\\1\');?>',
		);
		$code = preg_replace($search,$replace,$code);
		if(!file_exists(dirname($cpath))) mkdir(dirname($cpath),0777,TRUE);
		file_put_contents($cpath,$code);
		
	}
	
	/**
	* Transcode $var.var1.var2 to $var[var1][var2] via callback regex replace
	*
	* @param	array	Matches.
	* @return	string
	*/
	private function fetch_dot_array($matches){
		$pieces = explode('.',$matches[1]);
		$result = array_shift($pieces);
		$pieces = array_map(create_function('$a','return is_numeric($a) ? $a : "\'$a\'";'),$pieces);
		$result .= '['.join('][',$pieces).']';
		return $result;
	}
	
	/**
	* Fetch modificators via callback regex replace
	*
	* @param	array	Matches
	* @return	string
	*/
	private function fetch_modificators($matches){
		$mods = explode('|',$matches[2]);
		$var = $matches[1];
		foreach($mods as $mod){
			$params = explode(':',$mod);
			if(count($params) > 1){
				$mod = array_shift($params);
			}
			else $params = FALSE;
			switch($mod){
				case 'default':
				 return '? isset('.$var.') ? '.$var.' : '.$params[0];
				break;
				default:
				 if(!is_array($params)) $params[] = $var;
				 else array_unshift($params,$var);
				break;
			}
			$var = $mod.'(';
			if($params){ 
				$var .= implode(',',$params);
			}
			$var .= ')';			
		}
		return '? '.$var;
	}
	
	/**
	* Fetch templates includes via callback regex replace 
	*
	* @param	array
	* @return	string
	*/
	private function fetch_include($matches){
		$tpl = $matches[1];
		// Remove all quotes from tpl name
		$tpl = trim($tpl,'\'"');
		// If it's not a varible -- wrap it with quotes
		if($tpl[0] != '$') $tpl = '"'.$tpl.'"';
		return '<?php
		if(strpos('.$tpl.'," ")){
			$pieces = preg_split("/[^\w.-]/",'.$tpl.',-1,PREG_SPLIT_NO_EMPTY);	
			if(isset($pieces[1])){
				$tpl = GEARS.$pieces[0]."/templates/".$pieces[1];
			}	
			else {
				$tpl = dirname("'.$this->template.'")."/".$pieces[1];
			}
		}
		else {
			$tpl = str_replace(GEARS,"",'.$tpl.');
			if(strpos($tpl,"/") !== FALSE){
				$tpl = GEARS.trim($tpl,"/");
			}
			else {
				$tpl = dirname("'.$this->template.'")."/".$tpl;
			}
		}
		if(!in_array(pathinfo($tpl,PATHINFO_EXTENSION),array("tpl","php"))) $tpl .= \'.tpl\';
		$alt_path = str_replace("gears","templates/'.$this->global_template.'/gears",$tpl);
		if(file_exists($alt_path)){
			$tpl = $alt_path;
		}
		if(!file_exists($tpl)) return;
		$file = $this->make_cpath($tpl);
		if($file->expired){
			$this->prepare($tpl,$file->path);
		}
		include($file->path);
		?>';
	}
	
	/**
	* Fetch optional parameters matched with brackets via callback regex replace
	*
	* Example
	* [width="{$with}"]
	*
	* @param	array	Matches.
	* @return	string
	*/
	private function fetch_optional($matches){
		$start = addslashes(str_replace('\'','"',$matches[1]));
		$end = addslashes(str_replace('\'','"',$matches[3]));
		$var =  $matches[2];
		return '<?php if(!empty('.$var.')){ echo " '.$start.'".'.$var.'."'.$end.'";}?>';
	}
}