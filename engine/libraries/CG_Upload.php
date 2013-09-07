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
 * Upload class
 *
 * Extends CI_Upload class for uploading from remote server
 *
 * @package		CoGear
 * @subpackage	Upload
 * @category		Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class CG_Upload extends CI_Upload {

    var $url = FALSE;

    /**
	 * Constructor
	 *
	 * @return	void
	 */
    function CG_Upload($props = array())
    {
		parent::CI_Upload($props);
    }

/*
|===============================================================
| CoGear - based on CodeIgniter (by CodeMotion, http://codemotion.ru)
|---------------------------------------------------------------
| http://cogear.ru
|---------------------------------------------------------------
| Copyright (c) 2009 CodeMotion, Dmitriy Belyaev
|---------------------------------------------------------------
| Following changes are made by Dmitriy Belyaev
|---------------------------------------------------------------
| Purpose:
| This method is almost a copy of original CI_Upload->do_upload() function.
| Just make some changes to be able to use URL adress for copy to server.
|===============================================================
*/

	/*
	* Do (upload from) url
	*
	* Get link and upload it to server
	*
	* @param	mixed $url Can be link, or Form gear element as array or as object
	* @param	booleab $is_path If $url is path (not var name)
	* @return	void
	*/
	function do_url($url,$is_path = FALSE){
			$CI =& get_instance();
			// It can be a Form gear element
			if(!$is_path){
				if(is_array($url)){
					$url = $CI->input->post($url['name']);
				}
				// It can be a Form gear element in object form
				else if(is_object($url)){
					$url = $CI->input->post($url->name);
				}
				else {
					$url = $CI->input->post($url);
				}
			}
			if($url && !pathinfo($url,PATHINFO_EXTENSION)){
			    $this->set_error('upload_no_file_selected');
                return FALSE;
            }
            // Is the upload path valid?
            if ( ! $this->validate_upload_path())
            {
                $this->set_error('upload_no_filepath');
                return FALSE;
            }
            if($this->file_name == ""){
                $this->file_name =  url_name(basename($url));
            }
            $this->file_ext	 = '.'.pathinfo($url,PATHINFO_EXTENSION);
            if ( ! in_array(substr($this->file_ext,1),$this->allowed_types))
            {
                $this->set_error('upload_invalid_filetype');
                return FALSE;
            }
            // Sanitize the file name for security
            $this->file_name = $this->clean_file_name($this->file_name);

            // Remove white spaces in the name
            if ($this->remove_spaces == TRUE)
            {
                $this->file_name = preg_replace("/\s+/", "_", $this->file_name);
            }

            /**
            * Validate the file name
            * This function appends an number onto the end of
            * the file if one with the same name already exists.
            * If it returns false there was a problem.
            */
            $this->orig_name = $this->file_name;
            if ($this->overwrite == FALSE)
            {
                $this->file_name = $this->set_filename($this->upload_path, $this->file_name);

                if ($this->file_name === FALSE)
                {
                    return FALSE;
                }
            }

            /**
            * Move the file to the final destination
            * To deal with different server configurations
            * we'll attempt to use copy() first.  If that fails
            * we'll use move_uploaded_file().  One of the two should
            * reliably work in most environments
            */
            if ( ! @copy($url, $this->upload_path.$this->file_name))
            {
                $this->set_error('upload_destination_error');
                return FALSE;
            }

            /**
            * Run the file through the XSS hacking filter
            * This helps prevent malicious code from being
            * embedded within a file.  Scripts can easily
            * be disguised as images or other file types.
            */
            if ($this->xss_clean == TRUE)
            {
                $this->do_xss_clean();
            }

            /**
            * Set the finalized image dimensions
            * This sets the image width/height (assuming the
            * file was an image).  We use this information
            * in the "data" function.
            */
            $this->set_image_properties($this->upload_path.$this->file_name);

            return TRUE;
	}
	// ------------------------------------------------------------------------

	/**
	 * Perform the file upload
	 *
	 * Some trouble with mime type
	 * ["type"]=>string(28) "\"application/octet-stream\""
	 * Fix it on line 227
	 *
	 * @access	public
	 * @return	bool
	 */	
	function do_upload($field = 'userfile')
	{
		// Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field]))
		{
			$this->set_error('upload_no_file_selected');
			return FALSE;
		}
		
		// Is the upload path valid?
		if ( ! $this->validate_upload_path())
		{
			// errors will already be set by validate_upload_path() so just return FALSE
			return FALSE;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error)
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
				   $this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
				   $this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :   $this->set_error('upload_no_file_selected');
					break;
			}

			return FALSE;
		}

		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];		
		$this->file_name = $this->_prep_filename($_FILES[$field]['name']);
		$this->file_size = $_FILES[$field]['size'];		
		$this->file_type = trim(preg_replace("/^(.+?);.*$/", "\\1", $_FILES[$field]['type']),"\ \"");
		$this->file_type = strtolower($this->file_type);
		$this->file_ext	 = $this->get_extension($_FILES[$field]['name']);
		
		// Convert the file size to kilobytes
		if ($this->file_size > 0)
		{
			$this->file_size = round($this->file_size/1024, 2);
		}

		// Is the file type allowed to be uploaded?
		if ( ! $this->is_allowed_filetype())
		{
			$this->set_error('upload_invalid_filetype');
			return FALSE;
		}

		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize())
		{
			$this->set_error('upload_invalid_filesize');
			return FALSE;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions())
		{
			$this->set_error('upload_invalid_dimensions');
			return FALSE;
		}

		// Sanitize the file name for security
		$this->file_name = $this->clean_file_name($this->file_name);
		
		// Truncate the file name if it's too long
		if ($this->max_filename > 0)
		{
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}

		// Remove white spaces in the name
		if ($this->remove_spaces == TRUE)
		{
			$this->file_name = preg_replace("/\s+/", "_", $this->file_name);
		}

		/**
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

		if ($this->overwrite == FALSE)
		{
			$this->file_name = $this->set_filename($this->upload_path, $this->file_name);
			
			if ($this->file_name === FALSE)
			{
				return FALSE;
			}
		}

		/**
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
		if ( ! @copy($this->file_temp, $this->upload_path.$this->file_name))
		{
			if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name))
			{
				 $this->set_error('upload_destination_error');
				 return FALSE;
			}
		}
		
		/**
		 * Run the file through the XSS hacking filter
		 * This helps prevent malicious code from being
		 * embedded within a file.  Scripts can easily
		 * be disguised as images or other file types.
		 */
		if ($this->xss_clean == TRUE)
		{
			$this->do_xss_clean();
		}

		/**
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the "data" function.
		 */
		$this->set_image_properties($this->upload_path.$this->file_name);

		return TRUE;
	}
	
	// --------------------------------------------------------------------

	
	/**
	 * Set an error message - to be compatible with i18n
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_error($msg)
	{
		$CI =& get_instance();	
		
		if (is_array($msg))
		{
			foreach ($msg as $val)
			{
				$msg = t('!upload '.$val);				
				$this->error_msg[] = $msg;
				log_message('error', $msg);
			}		
		}
		else
		{
			$msg = t('!upload '.$msg);				
			$this->error_msg[] = $msg;
			log_message('error', $msg);
		}
	}
	// ------------------------------------------------------------------------

	/**
	 * Prep Filename
	 *
	 * Prevents possible script execution from Apache's handling of files multiple extensions
	 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _prep_filename($filename)
	{
		if (strpos($filename, '.') === FALSE)
		{
			return $filename;
		}
		
		$parts		= explode('.', $filename);
		$ext		= array_pop($parts);
		$filename	= array_shift($parts);
				
		foreach ($parts as $part)
		{
			// There is no need to add _ after filename - commented by Dmitry Belyaev
/*
			if ($this->mimes_types(strtolower($part)) === FALSE)
			{
				$filename .= '.'.$part.'_';
			}
			else
			{
*/
				$filename .= '.'.$part;
/* 			} */
		}
		
		$filename .= '.'.$ext;
		
		return url_name($filename);
	}
	// --------------------------------------------------------------------
	
/*
|===============================================================
| CoGear - changes end
|===============================================================
*/
}
// ------------------------------------------------------------------------
