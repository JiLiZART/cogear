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
 * Update function
 *
 * @package   CoGear
 * @subpackage  Global
 * @category    Gears javascripts
 * @author      CodeMotion, Dmitriy Belyaev
 * @link        http://cogear.ru/user_guide/
 */
function update(url,el,where,target){
  if(!where) where = 'after';
  var el_where = $(el);
  target = $type(target) === 'string' ? $(target) : target;
  if(where.split('.').length > 1){
    where = where.split('.');
    el_where = where[0] == 'this' ? $(target) : $(where[0]);
    where = where[1];
  }
  var data = target ? target.get('value') : '';
  new Request.JSON({  
  url: '/ajax'+url,
  data : "data=" + data,
  onRequest : function(){
    loader.inline(el_where);  
  },
  onComplete : function(re,text){
    loader.inline(el_where,null,true);
    if(re){
      if(re.success){
        $(el).removeClass('error').addClass('success');
      }
      if(re.msg){
        if($(el).get('tag') != 'input') $(el).set('html',''+re.msg);
        else $(el).set('value',''+re.msg);
      }
    }
    else {
      if($(el).get('tag') != 'input') $(el).removeClass('success').addClass('error').set('html',''+text);
      else {
       if(text) $(el).set('value',text);
      }
    }
  }
  }).post();

}
// ------------------------------------------------------------------------