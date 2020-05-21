	
	<?php if(isset($sidebar)){ echo $sidebar;} ?>
	</div>
</div>
<div id="footer">
	<p><a href="http://cogear.ru"><img src="http://cogear.ru/uploads/cogear-powered.gif" alt="Работает на cogear" width="70" height="19" align="absmiddle"></a> &copy; <?php echo date('Y');?><br/><small>Использование памяти: <?php if(isset($mem_usage)){ echo $mem_usage;} ?><br/><?php if( !empty($CI->db)):?>Запросов к базе данных: <?php echo  count($CI->db->queries);?><br/><?php endif; ?>Запросов в кеш: <?php echo  $CI->cache->counter;?><br/>Время работы: <?php echo  $CI->benchmark->elapsed_time();?></small></p>
</div>


</body>
</html>

