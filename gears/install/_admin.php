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



/**
 * Installer CP page
 *
 * @package		CoGear
 * @subpackage	Install
 * @category		Gears controllers
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class _Admin extends Controller{
	private $allgears = array();
	/**
	* Constructor
	*
	* @return	void
	*/
	function __construct(){
		parent::Controller();
		$this->allgears =& $this->gears_info->get();
		d('install');
	}
	

	/**
	* Create tabs panel to navigate
	*
	* @return	void
	*/
	private function tabs(){
/*
		 $this->install_tabs = new Panel('install_tabs',FALSE,FALSE,'tabs');
		 $this->install_tabs->set_title = TRUE;
		 $this->install_tabs->links_base = '/admin/install/';
		 $this->install_tabs->add(array('name'=>'index','text'=>fc_t('install setup'),'index'=>TRUE));
		 $this->install_tabs->add(array('name'=>'import','text'=>fc_t('global import')));
		 $this->install_tabs->add(array('name'=>'export','text'=>fc_t('global export')));
 		 $this->install_tabs->set_active($this->method);
		 $this->install_tabs->compile(12);	
*/
	}
	

	/**
	* Show list of all gears and give user ability to install/deinstall them 
	*
	* @return	void
	*/
	function index(){
	 $this->tabs();

	 // Try to get update info from repository
	 if($this->gear->repository){
		 $this->form->set('install_check_for_updates')->
		 buttons(array('check_updates'=>array('value'=>t('install check_for_updates'))))->compile();
		 if($result = $this->form->result()){
			 if(isset($result['check_updates'])){
				 $data[] = 'login='.$this->gear->repository->login;
				 $data[] = 'password='.md5(md5($this->gear->repository->password));
				 foreach($this->allgears as $key=>$value){
				  $data[] = "gears[]=".$key;
				 }
				 $context = stream_context_create(array(
					'http' => array(
						'method' => 'POST',
						'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
						'content' => implode('&',$data),
					),
				  ));
				  $response = json_decode(@file_get_contents(
					$file = $this->gear->repository->url,
					$use_include_path = false,
					$context
				  ));
				  if(isset($response->success) && isset($response->msg)){
					  msg($response->msg,$response->success);
				  }
				  store('install/updates',$response);
			 }
		 }
		 else {
			 $response = retrieve('install/updates');
		 }
		  if(isset($response->gears)){
			  foreach($response->gears as $gear=>$version){
				  if(isset($this->allgears[$gear]) && isset($this->allgears[$gear]['version']) && $version > $this->allgears[$gear]['version']) $this->allgears[$gear]['new_version'] = $version;
			  }
		  }
		  if(isset($response->updates) && $response->updates){
			 $this->form->set('install_updates')->action('/admin/install/update/');
			 $info = array(
			  'primary' => 'key',
			  'ajax' => TRUE,
			  'ajax_delete' => TRUE,
			 );
			 $header = array(
			 'key' => array('','hidden','1%'),
			 'title' => array(t('edit name'),'text','20%',FALSE,'left'),
			 'version' => array(t('install version'),'text','10%'),
			 'description' => array(t('edit description'),'text','50%',FALSE,'left'),
			 'updates' => array('','checkbox','5%'),
			 );
			 $updates = object2array($response->updates);
			 $updates_path = ROOTPATH.'/uploads/install/updates/';
			 if(!file_exists($updates_path)) mkdir($updates_path,0777,TRUE);
			 $updates_dir = glob($updates_path);
			 $updated = array();
			 if($updates_dir){ 
				 foreach($updates_dir as $dir){
					 $config = $this->info->read($updates_path.$dir.'/update');
					 if($config && isset($config['installed']) && $config['installed']){
						 unset($updated[$dir]);
					 }
				 }
			 }
			 if($updated) $updates = array_diff_key($updates,$updated);
			 if($updates){
				 $this->form->grid(fc_t('install updates'),$header,$updates,$info);  
				 $this->form->compile();
			 }
		  }
	 }
	 $this->form->set('install')->action('/admin/install/process/');

	 foreach($this->allgears as $gear=>$config){
		 if(isset($config['required'])){
			 if(!is_array($config['required'])) $config['required'] = _explode(',',$config['required']);
			 $required = array();
			 foreach($config['required'] as $req_gear){
				if(strpos($req_gear,' ')){
					$tmp = explode(' ',$req_gear);
					$version = array_pop($tmp);
					$req_gear = implode(' ',$tmp);
				}
				$class = ($this->allgears[$req_gear]['group'] == 'core' OR $this->allgears[$req_gear]['enabled']) ? 'success' : 'error';
				$required[] = $this->builder->span($this->allgears[$req_gear]['title'],$class);
			 }
			 $config['description'] .= $this->builder->div($this->builder->b(t('install required').': ').implode(', ',$required));
		 }
		 if(isset($config['core'])){
			 $config['description'] .= $this->builder->div($this->builder->b(t('install core').': ').$this->builder->span($config['core'],$this->check_core($config['core']) ? 'success':'error'));
		 }
 		  if(!empty($config['incomp'])){
				  $incomp = is_array($config['incomp']) ? $config['incomp'] : explode(',',str_replace(' ','',$config['incomp']));
				  $config['description'] .= '<div><b>'.t('install incomp').'</b> ';
				  $descr = array();
				  foreach($incomp as $incomp_gear){
					  foreach($groups as $group_name => $group){
						  foreach($group as $group_gear_name => $group_gear){
							  if($group_gear_name == $incomp_gear){
								 $groups[$group_name][$group_gear_name]['gears'] = FALSE;
								 if(!strpos($groups[$group_name][$group_gear_name]['description'],$config['title'])){
								 $groups[$group_name][$group_gear_name]['description'] .= '<div><b>'.t('install incomp').'</b> <span class="error">'.$config['title'].'</span>';
								 }
							  }
						  }
					  }
					  $this->allgears[$incomp_gear]['enabled'] = FALSE;
					  $descr[] = '<span class="error">'.(isset($this->allgears[$incomp_gear]['title']) ? $this->allgears[$incomp_gear]['title'] : $incomp_gear).'</span>';
				  }
				  $config['description'] .= implode(', ',$descr).'. </div>';
		  }
		 if(isset($config['group'])) $groups[$config['group']][$gear] = $config;
		 else $groups['no_group'][$gear] = $config;
	 }
	 $info = array(
      'primary' => 'gear',
      'ajax' => TRUE,
      'link' => array('/admin/install/update'),
      'link_add' => array('gear'),
      'undel' => array_keys($groups['core']),
     );
	 $header = array(
	 'gear' => array('','hidden','1%'),
	 'title' => array(t('edit name'),'text','20%',FALSE,'left'),
	 'version' => array(t('install version'),'text','10%'),
	 'new_version' => array(fc_t('install update'),'link','10%','left'),
	 'description' => array(t('edit description'),'text','50%',FALSE,'left'),
	 'gears' => array('','checkbox','5%'),
	 );
	 foreach($groups as $name=>$group){
		 $cheader = $header;
		 if($name == 'core'){
			 $suffix = ' ('.COGEAR.')';
			 unset($cheader['gears']);
		 }
		 else $suffix = FALSE;
		 $name = fc_t('global '.$name).$suffix;
		 $this->form->grid($name,$cheader,$group,$info);
	 }
	 $this->form->compile();
	}
	

	
	/**
	* Install/deinstall gears
	*
	* @return	json
	*/
	function process(){
		$gears = $this->input->post('gears');
		$gear = reset($gears);
		$path = GEARS.$gear.'/'.$gear;

		//$this->load->model(''.$gear.' _install','install',TRUE);
		if($this->allgears[$gear]['group'] == 'core') {
			ajax(FALSE);
		}
		if(isset($this->allgears[$gear]['enabled']) && $this->allgears[$gear]['enabled']){
			foreach($this->allgears as $list_gear){
				$msg = array();
				if(!isset($list_gear['required'])) continue;
				if(!isset($list_gear['enabled']) OR !$list_gear['enabled']) continue;
				foreach($list_gear['required'] as $req_gear){
					if(strpos(trim($req_gear),' ') !== FALSE){
						$tmp = explode(' ',$req_gear);
						$version = array_pop($tmp);
						$req_gear = implode(' ',$tmp);
					}
					if($req_gear == $gear) {
						$msg[] = t('install require_deinstall',$list_gear['title']);
					} 
				}
				if($msg) ajax(FALSE,implode("\n",$msg));
			}
			$msg = '';
			if(class_exists(ucfirst($gear).'_Install')){
				$install = $gear.'_install';
				if(method_exists($this->$install,'deinstall')){
					$msg = $this->$install->deinstall();
				}
			}
			$this->sql(GEARS.$gear.'/deinstall.sql');
			$this->info->set($path)->change('enabled',FALSE)->compile();
			$this->cache->clear('gears');
			ajax(TRUE,$msg);
		}
		else{
			$msg = array();
			if(!empty($this->allgears[$gear]['incomp'])){
					$incomp = is_array($this->allgears[$gear]['incomp']) ? $this->allgears[$gear]['incomp'] : explode(',',str_replace(' ','',$this->allgears[$gear]['incomp']));
					foreach($incomp as $incomp_gear){
						if(!empty($this->allgears[$incomp_gear]['enabled'])){
							$msg[] = t('install incomp_with',$this->allgears[$incomp_gear]['title']);
						}
					}
			}
			foreach($this->allgears as $gname=>$config){
					if(empty($config['incomp'])) continue;
					$incomp = is_array($config['incomp']) ? $config['incomp'] : explode(',',str_replace(' ','',$config['incomp']));
					if(in_array($gear,$incomp) && !empty($config['enabled'])){
						$msg[] = t('install incomp_with',$config['title']);
					}
				}
				if(!empty($gears2)) debug($gears2);
				if(isset($this->allgears[$gear]['core'])){
				if(!$this->check_core($this->allgears[$gear]['core'])){
					ajax(FALSE,t('install core_uncombatible'));
				}
			}
			if(isset($this->allgears[$gear]['required'])){
				foreach($this->allgears[$gear]['required'] as $req_gear){
					if(strpos($req_gear,' ')){
						$tmp = explode(' ',$req_gear);
						$version = array_pop($tmp);
						$req_gear = implode(' ',$tmp);
					}
					if($this->allgears[$req_gear]['group'] != 'core' && empty($this->allgears[$req_gear]['enabled'])) {
						$msg[] = t('install require',$this->allgears[$req_gear]['title']);
					} 
					elseif(isset($version) && (isset($this->allgears[$req_gear]['version']) && $this->allgears[$req_gear]['version'] < $version OR !isset($this->allgears[$req_gear]['version']))) {
						$msg[] = t('install require_version',$version,$this->allgears[$req_gear]['title']);	
					}
				}
			}
			if($msg) ajax(FALSE,implode("\n",$msg));
			$msg = '';
			if(class_exists(ucfirst($gear).'_Install')){
				$install = $gear.'_install';
				if(method_exists($this->$install,'install')){
					$msg = $this->$install->install();
				}
			}
			$this->sql(GEARS.$gear.'/install.sql');
			$this->info->set($path)->change('enabled',TRUE);
			$this->info->compile();
			$this->cache->flush();
			ajax(TRUE,$msg);
		}
		ajax(FALSE);
	}
	

	/**
	* Import gears
	*
	* @return	void
	*/
	function import(){
		$this->tabs();
		$options = glob(ROOTPATH.'/uploads/install/gears/*.zip') ? array_map(create_function('$a','return strtolower(basename($a));'),glob(ROOTPATH.'/uploads/install/gears/*.zip')) : FALSE;
		$this->form->set('install_import')->fieldset('install_import');
		if($options){
		 $this->form->select('package_uploaded',array('options'=>array_combine($options,$options),'label'=>t('package_uploaded'),'description'=>FALSE));
		}
		$this->form->file('package',array('allowed_types'=>'zip','js_validation'=>'file[zip]','upload_path'=>_mkdir(ROOTPATH.'/uploads/install/gears/'),'overwrite'=>TRUE))
		->file_url('package_url',array('allowed_types'=>'zip','js_validation'=>'file[zip]','upload_path'=>_mkdir(ROOTPATH.'/uploads/install/gears/'),'overwrite'=>TRUE,'label'=>t('remote'),'description'=>t('remote_description')));
		//->checkbox('rewrite')
		$this->form->buttons('upload');
		if($result = $this->form->result()){
			if(!isset($result['package_url']) && !isset($result['package']) && isset($result['package_uploaded'])){
				$result['package'] = $result['package_uploaded'];
			}
			if(isset($result['package_url']) && !isset($result['package'])){
				$result['package'] = $result['package_url'];
			}
			if(isset($result['package']) && strpos($result['package'],'/uploads') === FALSE) $result['package'] = '/uploads/install/gears/'.$result['package'];
			if(strpos(basename($result['package']),'patch') === 0 
			OR strpos(basename($result['package']),'update') === 0 
			OR strpos(basename($result['package']),'core') === 0){
				$action = 'update';
				$path = '/uploads/install/updates/';
				if(!file_exists('.'.$path)) mkdir('.'.$path,0777,TRUE);
				$update = $path.basename($result['package']);
				copy('.'.$result['package'],'.'.$update);
				@unlink($result['package']);
				$result['package'] = $update;
			}
			else {
				$action = 'gear';
			}
			if(isset($result['package']) && $this->installer->import($result['package'],$action)){
				msg('upload_success',TRUE);
			}
			else msg('!form saved_failure',FALSE);
			// Clean lang cache
			$this->i18n->clear();
		}
		$this->form->compile();
	}
	

	/**
	* Update gear
	*
	* @param	string
	* @return	void
	*/
	function update($gear = FALSE){
		if(!$gear OR !$this->gear->repository OR !$this->input->post('updates')) return _404();
		 $data = array(
		 'login='.$this->gear->repository->login,
		 'password='.md5(md5($this->gear->repository->password))
		 );

		if($updates = $this->input->post('updates')){
		 $update = reset($updates);
		 $data[] = 'update='.$update;
		}
		else {
		  $data[] = 'gear='.$gear;
		}

		 $context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
				'content' => implode('&',$data),
			),
		  ));$response = file_get_contents(
			$file = $this->gear->repository->url,
			$use_include_path = false,
			$context
		  );
		  if(isset($response)){
			  $name = $gear ? 'gears/'.$gear : 'updates/'.$update;
			  $path = ROOTPATH.'/uploads/install/'.$name.'.zip';
			  _mkdir(dirname($path),0777);
			  file_put_contents($path,$response);
			  if($gear && $this->installer->import($path,'gear')){msg('upload_success',TRUE);redirect('/admin/install/');
			  }
			  else if($update && $this->installer->import($path,'update')){ajax(TRUE,'!install update_installed_success');}
		  }

	}
	

	/**
	* Export gears
	*
	* @return	void
	*/
	function export(){
		$this->tabs();
		$gears = $this->gears_info->get();
		$options = array4key($gears,FALSE,'title');
		natsort($options);
		$this->form->set('install_export')->fieldset('install_export')
		->select('gear',array('options'=>$options))
		->buttons('download')
		->compile();if($result = $this->form->result()){
			if(!$result['gear']) return;
			$this->installer->export($result['gear']);
		}
	}
	
	/**
	*  Compare gears core and current core
	*
	* @param	string
	* @return	boolean
	*/
	private function check_core($version){
		return version_compare($version,COGEAR,'<=');
	}
	
	/**
	* Operate sql file
	*
	* @param	string	Path to file.
	*/
	private function sql($path){
		if(!file_exists($path)) return;
		$sql = file_get_contents($path);
		$sql = parse_db_prefix($sql);
		$queries = explode(';',$sql);
		$this->db->db_debug = FALSE;
		foreach($queries as $query){
			if(!empty($query)) $this->db->query($query.';');
		}	
	}
	
	/**
	 * Patcher
	 *
	 * @param	string	$gear
	 * @return
	 */
	 public function patch(){
		if($patches = array_filter(glob(GEARS.'*/patch.sql'),'check_patch_filter')){
			$output = array();
			foreach($patches as $patch_file){
				$patch_gear = basename(dirname($patch_file));
				$this->sql($patch_file);
				@chmod($patch_file,0777);
				file_put_contents($patch_file,"-- Patched
".file_get_contents($patch_file));
				msg(t('install patch_successed',t('gears '.$patch_gear)));
			}
		}
		redirect('/admin/');
	 }
}
