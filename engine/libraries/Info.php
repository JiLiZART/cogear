<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author			CodeMotion, Dmitriy Belyaev
 * @copyright		Copyright (c) 2009, CodeMotion
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Info class
 *
 * Read and write .ini-format files. Use to store gears configuration.
 *
 * @package		CoGear
 * @subpackage	Info
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class CI_Info {
    private $data;
    private $filename = FALSE;
    private $content = FALSE;
    
	/**
	* Check file
	*
	* @param	string $filename File source
	* @return	void
	*/
    function check($filename){
        if(!$filename && $this->filename){
         $filename = $this->filename;
        }
        elseif(trim($filename) == '' OR !$filename && !$this->filename){
         return FALSE;
        }
        if(!pathinfo($filename,PATHINFO_EXTENSION)){
            $this->filename = $filename.'.info';
        }
        else {
            $this->filename = $filename;
        }
        if(!file_exists($this->filename)){
            return FALSE;
        }
        @chmod($this->filename,0777);
        return TRUE;
    }
	// ------------------------------------------------------------------------
    
   	/**
	* Check
	*
	* Synonim for check with chaining return 
	* 
	* @return	object	$this
	*/
	function set($path){
     $this->check($path);
     return $this;
    }
	// ------------------------------------------------------------------------

	/**
	*  Read 
	*
	* Parse and process 'ini'-format file
	*
	* @param	string $filename
	* @return	mixed
	*/
    function read($filename = FALSE){
        if($this->check($filename)){
            $this->data = parse_ini_file($this->filename,true);
            $this->data = $this->parse($this->data);
            return $this->data;
        }
        else {
            return FALSE;
        }
    }
	// ------------------------------------------------------------------------

	/**
	*  Change
	*
	* Change any variable value in current file 
	*
	* @param	string $name
	* @param	mixed $value
	* @return	void
	*/
    function change($name, $value = TRUE){
		if(is_array($name)){
			$result = array();
			foreach($name as $key=>$value){
				$result[] = $this->change($key,$value);
			}
			return $this;
		}
		
        if(!$this->content) $this->content = file_get_contents($this->filename);
        if(is_array($value)){
			foreach($value as $k=>$val){
				$this->change($name.' '.$k,$val);
			}
			return $this;
        }
        $this->process($value);
		if(strpos($name,' ')){
			$tmp = explode(' ',$name);
			$departure = array_shift($tmp);
			$name = implode(' ',$tmp);
			$this->content = preg_replace("/\s?(\[{$departure}\].*)\s+{$name}\s?=\s?(.[^\n]*)$/ism","\n$1\n{$name} = {$value}",$this->content);
        }
        else {
			$this->content = preg_replace("/([^\]]\s+){$name}[\s]?=[\s]?\"?.*\"?/mi","$1".$name.' = '.$value,$this->content);
		}
		return $this;
    }	
    // ------------------------------------------------------------------------
    
  	/**
	*  Add
	*
	* Add new variable to file
	*
	* @param	string	$name	
	* @param	mixed	$value
	* @return	void
	*/
    function add($name, $value = FALSE){
		if(is_array($name)){
			$result = array();
			foreach($name as $key=>$value){
				$result[] = $this->add($key,$value);
			}
			return $this;
		}

        if(!$this->content) $this->content = file_get_contents($this->filename);
        $this->process($value);

		if(strpos($name,' ')){
			$tmp = explode(' ',$tmp);
			$departure = array_shift($tmp);
			$name = implode(' ',$name);
			if(!preg_match("/\s?(\[{$departure}\].*){$name}\s?=\s?(.[^\n]*)%/ism","\n$1\n{$name}={$value}\n",$this->content)){
				$this->content .= "\n[{$departure}]\n{$name} = $value";		
			}
        }
		else if(!preg_match("/[^\]]\s+{$name}[\s]?=/i",$this->content)){
		  $bracket_place = strpos($this->content,'\n[');
		  if($bracket_place) $this->content = substr($this->content,0,$bracket_place)."\n{$name} = $value".substr($this->content,$bracket_place);
		  else $this->content .= "\n{$name} = $value";
		 }
		return $this;
    }
	// ------------------------------------------------------------------------
	
	/**
	* Process
	*
	* Prepare for writing ini-file
	*
	* @param	string $value
	* @return	string $value
	*/
	function process($value){
		if(is_bool($value)){
			if($value) $value = 'TRUE';
			else $value = 'FALSE';
        }
        else if(trim($value,"' ") == 'NULL') {
			$value = 'FALSE';
        } 
		else if(trim($value,"' ") == 'true') {
			$value = 'TRUE';
        } 
        else if(is_string($value)){
         $value = trim($value,'"');
         $value = '"'.str_replace('"',"''",$value).'"';
        }
        $value = html_entity_decode($value);
        return $value;
    }
	// ------------------------------------------------------------------------

	/**
	* Parse 
	*
	* Prepare data after reading file
	*
	* @param	string	$data
	* @return	void
	*/
	function parse($data){
		// Replace ' to ", because by default parse_ini_file crops any double quotes
		$data = str_replace("''",'"',$data);
		if(is_array($data)){
			foreach($data as $key=>$value){
					$data[$key] = $this->parse($value);
			}
			return $data;
		}
		else {
			// If param is set to TRUE with uppercase letters, parse_ini_file function mention it as a string - fix this issue
			if(gettype($data) == 'string'){
				if($data == '') return FALSE;
				else if($data === '1') return TRUE;
			}
			return $data;
		}
    }
	// ------------------------------------------------------------------------
	
	/**
	* Write
	*
	* Write a ini-file from data array. Data may be 2 dimensional array.
	*
	* @param	array		$data
	* @param	mixed	$return_or_file - Whether to return (boolean) or filename (string)
	* @return	void
	*/
    function write($data, $return_or_file = FALSE){
		 $output = '';
		 if(!is_array($data)) return FALSE;
		 foreach($data as $section=>$info){
				$output .= "[{$section}]\n";
				foreach($info as $name=>$value){
					$output .= "{$name} = ".$this->process($value)."\n";
				}
				$output .= "\n";
		 }
		 if($return_or_file === TRUE){
			 return $output;
		 }
		 else if(is_string($return_or_file)) {
			 if(function_exists('mkdir_if_not_exists')) {
				 mkdir_if_not_exists(dirname($return_or_file));
			 }
			 @chmod($return_or_file,0777);
			 file_put_contents($return_or_file,$output);
			 @chmod($return_or_file,0644);
			 return TRUE;
		 }
		 else return FALSE;
    }
	// ------------------------------------------------------------------------

    
	/**
	* Compile 
	*
	* @return	void
	*/
    function compile(){
	      @chmod($this->filename,0777);
			file_put_contents($this->filename,$this->content);
	      @chmod($this->filename,0644);
    }
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------
