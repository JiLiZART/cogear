<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package   CoGear
 * @author      CodeMotion, Dmitriy Belyaev
 * @copyright   Copyright (c) 2009, CodeMotion
 * @license     http://cogear.ru/license.html
 * @link        http://cogear.ru
 * @since     Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Installer controller for repository
 *
 * @package   CoGear
 * @subpackage  Install
 * @category    Gears controllers
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
class Index extends Controller{
  /**
  * Constructor
  *
  * @return void
  */
  function __construct(){
    parent::Controller();
    $this->no_sidebar = TRUE;
   }
  // ------------------------------------------------------------------------
  
  
  /**
  * Installing cogear
  *
  * @param  string  $step   Progress of install
  * @return void
  */
  function index($step = 'license'){
    d('setup');
    if($this->site->database && !$this->session->get('install')){
      if($this->user->get('id') != 1) return _403();
      return info(t('already_installed'));
    }
    title(t('title'),TRUE,TRUE);
    // You must agree with license
    if($history = $this->session->get('install') && $step != 'reset'){
      if($history != $step) redirect('/install/'.$history.'/');
    }
        $this->builder->h1(at('step_'.$step,t('title')),TRUE);
    switch($step){
       /**
       * License
       */
      case 'license':
        $data = @file_get_contents('http://cogear.ru/license/'.$this->site->url);
        if(!empty($data) && preg_match("#<div\s+class=\"text\">(.*)</div>#ismU",$data,$matches)){
          $license = preg_replace(
          array(
          '#href=([\'|"])/#',
          '#/uploads#',
          '#href#',
          ),array(
          'href=$1http://cogear.ru/',
          'http://cogear.ru/uploads',
          'target="_blank" href',
          ),$matches[1]);
        }
        else {
          $license = t('offline_license');
        }
        $this->form->set('install/license')
        ->description($license)
        ->br();
        $this->form->backlink = FALSE;
        $this->form->checkbox('agree',array('validation'=>'required','js_validation'=>'required'))
        ->buttons('next');
        $this->form->compile();
        if($result = $this->form->result()){
          if($result['agree']){
            $this->session->set('install','site');
            redirect('/install/site/');
          }
        }
      break;
      /*
      * Site info
      */
      case 'site':
        foreach(new DirectoryIterator('./templates/') as $file){
          if($file->isDir() && !$file->isDot() && strpos($file->getFilename(),'.') !== 0){
            $templates[] = $file->getFilename();
          }
        }
        $this->form->set('install/site')
        ->description(t('site_descr'))
        ->input('url',array('validation'=>'required','js_validation'=>'required'))
        ->input('name',array('validation'=>'required','js_validation'=>'required'))
        ->input('date_format',array('validation'=>'required','js_validation'=>'required'))
        ->select('template',array('options'=>array_combine($templates,$templates)))
        ->buttons('save')
        ->set_values($this->site);
        if($result = $this->form->result()){
        if(isset($result['url'])){
            $result['url'] = str_replace(array('http://','www'),'',$result['url']);
            $this->info->set(GEARS.'global/global')->change($result)->compile();
            $this->session->set('install','database');
            redirect('/install/database/');
          }
        }
        $this->form->compile();
      break;
       /*
       * Database
       */
      case 'database':
      $error = false;
      $config = array('validation'=>'required','js_validation'=>'required');
      $dbdriver['mysql'] = 'mysql';
      $this->form->set('install/database')
      ->description(t('db_descr'))
      ->input('hostname',array_merge($config,array('value'=>'localhost')))
      ->input('username',$config)
      ->input('password')
      ->input('prefix',array('description'=>t('prefix_desc')))
      ->input('database',array_merge($config,array('value'=>'cogear')))
      ->checkbox('create_db')
      ->checkbox('no_upload_dump')
      ->buttons('send');

      if($result = $this->form->result(true)){
        $this->form->set_values($result);
        $result['prefix'] = preg_replace('/[^a-z0-9]/i','',$result['prefix']);

        $driver = extension_loaded('mysqli') ? 'mysqli' : 'mysql';
        $dump = @file_get_contents('./cogear.sql');
        if(!$dump) return info(t('no_dump'));
        $dump = preg_replace(array('#/\*(.*)\*/;\n*#','#^--(.*)\n*#im'),'',$dump);
        $resource = $driver == 'mysql' ? 
        @mysql_connect($result['hostname'],$result['username'],$result['password']) :
        @mysqli_connect($result['hostname'],$result['username'],$result['password']);
        
        if(empty($resource) || empty($dump)){
          $error = t('no_connect');
        } 

        if (!$error) {
          if ($result['create_db'] == "true") {
              $query = "CREATE DATABASE {$result['database']} CHARACTER SET utf8 COLLATE utf8_general_ci";

              $cmd = "{$driver}_query";
              $cmd_error = "{$driver}_error";

              $res = ($driver == 'mysqli')? @$cmd($resource, $query) : @$cmd($query, $resource);

              if (!$res) 
              {
                 $error = t('cant_create_db', $result['database']);
              }
          } 
        }

        if (!$error) {
          $cmd = "{$driver}_select_db";
          $cmd_error = "{$driver}_error";
          $res = ($driver == 'mysqli')? @$cmd($resource, $result['database']) : @$cmd($result['database'], $resource);
          if (!$res) {
             $error = t('no_db', $result['database']);
          }
        }
          
        if (!$error) {

          $password = empty($result['password']) ? '' : ':'.$result['password'];
  
          if (!(empty($result['prefix']))) {
            $dsn = "{$driver}://{$result['username']}{$password}@{$result['hostname']}/{$result['database']}?dbprefix={$result['prefix']}_";
          } else {
            $dsn = "{$driver}://{$result['username']}{$password}@{$result['hostname']}/{$result['database']}";
          }

          $this->info->set(GEARS.'global/global')->change(array('database'=>$dsn))->compile();

          if ($result["no_upload_dump"] != "true") {
          if (!(empty($result['prefix']))) {
            $this->load->database($dsn.'&db_debug=TRUE');
            $dump = parse_db_prefix($dump,$result['prefix'].'_');
          } else {
            $this->load->database($dsn.'?db_debug=TRUE');
          }
            $dump = preg_split('#;\s*#',$dump,-1,PREG_SPLIT_NO_EMPTY);
            foreach($dump as $query){           
              $query = preg_replace("/\/\*\{PRE\}\*\/\`/", '`'.$this->db->dbprefix, $query);
              $this->db->_execute($query);
            }
          }

          $this->session->set('install','permissions');
          redirect('/install/permissions/');
        }
      }
            if(!empty($error)) info($error);

        $this->form->compile();

      break;
       /*
       * Permissions
       */
       case 'permissions':
         $dirs = array(
         "/engine/cache/" => '0777',
         "/uploads/" => '0777',
         );

         foreach($dirs as $dir=>$perm){
           @chmod('.'.$dir,octdec($perm));
         }

         $header = array(
         'dir' => array(t('dir'),'text','50%',FALSE,'left'),
         'cur_perms' => array(t('cur_perms'),'text','15%'),
         'need_perms' => array(t('need_perms'),'text','15%'),
         'result' => array('','text','20%')
         );
         $error = FALSE;
         foreach($dirs as $dir=>$perms){
           $cur_perms = substr(sprintf('%o',fileperms('.'.$dir)),-4);
           $data[] = array(
           'dir'=>$dir,
           'cur_perms'=>$cur_perms,
           'need_perms'=>$perms,
           'result'=>octdec($cur_perms) === octdec($perms) ? t('match') : t('not_match'),
           );
           if(octdec($cur_perms) !== octdec($perms)) $error = TRUE;
         }
         $info = array('noname'=>TRUE);
         $this->form->set('install/permissions');
         if($error){
             $this->form->description(t('perm_update'));
             $this->form->grid('permissions',$header,$data,$info)->compile();
             }
         else {
             $this->session->set('install','admin');
             redirect('/install/admin/');
         }
       break;
       /*
       * Admin
       */
       case 'admin':
         d('user');
      $this->form->set('install/admin')->key()
      ->input('email', array('validation'=>'required|valid_email','js_validation'=>'required|email'))
      ->input('name', array('validation'=>'required|min_length[3]|alpha_numeric','js_validation'=>'required|lengthmin[3]|alphanum'))
      ->input('passworda',array('size'=>25,'validation'=>'required|min_length[5]','js_validation'=>'required|length[5,-1]','ajax'=>array('name'=>t('!edit generate'),'url'=>'/user/passgen/','where'=>'passworda.after','update'=>'passworda')))
      ->input('password_confirm',array('size'=>25,'validation'=>'required|matches[passworda]','js_validation'=>'required|confirm[passworda]'))
      ->buttons('create');
      $this->load->database($this->site->database.'?db_debug=TRUE');
      if($user = $this->db->get_where('users',array('id'=>1))->row()){
        $user->passworda = 'password';
        $this->form->set_values($user);
      }
      if($result = $this->form->result()){
        $result['id'] = 1;
        $result['user_group'] = 1;
        $result['password'] = md5(md5(trim($result['passworda'])));
        $result['validate_code'] = $this->session->get('session_id');
        $result['is_validated'] = 'true';
        if(!empty($user)){
          $this->form->update('users',$result,array('id'=>$user->id));
        }
        else $this->form->save('users',$result);
        $this->user->login($result['email'],$result['passworda'],TRUE);
        $this->user->login($result['email'],$result['password'],TRUE);

            $this->info->set(GEARS.'global/global')->change(array('installed'=>TRUE))->compile();
            $this->session->set('install','finish');
            redirect('/install/finish/');
      }
      $this->form->compile();
       break;
       /*
       * Finish
       */
       case 'finish':
         $this->session->destroy('install');
         info(t('finish'));
       break;
       /*
       * Flush
       */
       case 'reset':
         $this->session->destroy('install');
         redirect('/install/');
       break;
    }
  }
  // ------------------------------------------------------------------------
  }

// ------------------------------------------------------------------------