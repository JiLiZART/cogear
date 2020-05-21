<?php
/**
* Sitemap model
*
* @author   Dmitriy Belyaev <admin@cogear.ru>
* @copyright    Copyright (c) 2009, Dmitriy Belyeav
* @license    http://cogear.ru/license.html
* @link     http://cogear.ru
* @package    Sitemap
* @version    $Id$
*/
class Sitemap extends Model{
  
  /**
  * Sitemap path
  * @string
  */
  public $path; 
  /**
  * Sitemaps
  * @array
  */
  private $sitemaps = array();
  /**
  * Current sitemap
  * @string
  */
  private $sitemap = '';
  /**
  * Counter
  * @int
  */
  private $counter = 0;
  
  /**
  * Constructor
  */
  public function __construct(){
    parent::Model();
    $this->path = ROOTPATH.'/sitemap.xml';
  }
  
  /**
  * Generate sitemap
  */
  public function generate(){
        @ini_set("memory_limit","256M");
      if($this->gears->community) $this->communities();
      if($this->gears->blogs) $this->blogs();
      if($this->gears->pages) $this->pages();
      $this->nodes();
      $this->users();
      foreach($this->sitemaps as $sitemap){
        $map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $map .= $sitemap;
        $map .= "</urlset>";
        $output[] = $map;
      }
      if(empty($output)){
        $map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $map .= $this->sitemap;
        $map .= "</urlset>";
        file_put_contents($this->path,$map);
        @chmod($this->path, 0644);
      }
      else {
        $sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
        $i = 1;
        foreach($output as $map){
          $file = str_replace('.xml','_'.$i.'.xml',$this->path);
          file_put_contents($file,$map);
          @chmod($file,0644);
               $sitemap .= "
               \t<sitemap>
               \t\t<loc>".str_replace(ROOTPATH,$this->site->url.'/',$file)."</loc>
               \t\t<lastmod>".date("Y-m-d\TH:i:sP")."</lastmod>
               \t</sitemap>";
               $i++;
        }
        $sitemap .= "\n</sitemapindex>";
        file_put_contents($this->path,$sitemap);
        @chmod($this->path, 0644);
      }
      // ping Google
      $sitemap_url = 'http://'.$this->site->url.'/sitemap.xml';
      // Now let's change robots.txt
      $robots_path = ROOTPATH.'/robots.txt';
      if(file_exists($robots_path)){
        $robots = file_get_contents($robots_path);
        if(strpos($robots,'Sitemap:')){
          $robots = preg_replace("#Sitemap:\s+.*$#",'Sitemap: '.$sitemap_url,$robots);
        }
        else {
          $robots .= "\nSitemap: ".$sitemap_url."\n";
        }
        file_put_contents($robots_path,$robots);
      }
      else {
        file_put_contents(ROOT,"User-agent: *\nAllow: /\nSitemap: ".$sitemap_url);
      }
  }
  
  /**
  * Generate sitemap for communities
  */
  private function communities(){
    $communities = $this->db->order_by('id','asc')->get('community')->result();
    foreach($communities as $community){
      $node = $this->db->order_by('id','desc')->get_where('nodes',array('cid'=>$community->id))->row();
      $last_update = date('Y-m-d',strtotime($node ? ($node->last_update == '0000-00-00 00:00:00' ? $node->created_date : $node->last_update) : $community->created_date));
      $this->get_xml('/community/'.$community->url_name.'/',$last_update,0.8);
    }
  }
  
  /**
  * Generate sitemap for blogs
  */
  private function blogs(){                         
    $blogs = $this->db->select("users.url_name, MAX({$this->db->dbprefix}nodes.last_update) as last_update")->join('nodes','nodes.aid = users.id','inner')->
    where('last_update != "0000-00-00 00:00:00"')->group_by('nodes.aid')->get('users')->result();
    foreach($blogs as $blog){
      $this->get_xml('/blogs/'.$blog->url_name.'/',date('Y-m-d',strtotime($blog->last_update)),0.7);
    }
  }
  
  /**
  * Generate sitemap for pages
  */
  private function pages(){
    $pages = $this->db->order_by('id','asc')->get('pages')->result();
    foreach($pages as $page){
      $this->get_xml('/pages/'.$page->url_name.'/',date('Y-m-d',strtotime($page->last_update)),0.9);
    }
  }
  
  /**
  * Generate sitemap for nodes
  */
  private function nodes(){
    $this->node->query();
    $this->db->where('published IS NOT NULL');
    $nodes = $this->db->order_by('id','desc')->get('nodes')->result();
    foreach($nodes as $node){
      $link = $this->node->create_link($node);
      $this->get_xml($link,date('Y-m-d',strtotime($node->last_update)),0.8);
    }
  }

  /*
  * Generate sitemap for users
  */
  private function users(){
    $users = $this->db->order_by('id','asc')->get('users')->result();
    foreach($users as $user){
      $this->get_xml('/user/'.$user->url_name.'/',date('Y-m-d',strtotime($user->last_visit)),0.6);
    }
  }
  
  /**
  * Generate xml piece
  * 
  * @param  string  Location.
  * @param  string  Last update.
  * @param  float Priority.
  * @param  string  Change frequency.
  * @return string  Xml-piece.
  */
  private function get_xml($loc,$date,$priority = 1,$freq = "daily"){
    $loc = htmlspecialchars($loc);
    if(!strpos($loc,$this->site->url)){
      $loc = 'http://'.$this->site->url.'/'.ltrim($loc,' /');
    }
    $xml = "\t<url>\n";
    $xml .= "\t\t<loc>$loc</loc>\n";
    $xml .= "\t\t<lastmod>$date</lastmod>\n";
    $xml .= "\t\t<priority>" . $priority . "</priority>\n";
    $xml .= "\t\t<changefreq>daily</changefreq>\n";
    $xml .= "\t</url>\n";
    
    $this->sitemap .= $xml;
    $this->counter++;
    
    if($this->counter%50000 == 0){
      array_push($this->sitemaps,$this->sitemap);
      $this->sitemap = '';
    }   
    return $xml;
  }
}