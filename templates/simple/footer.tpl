	
	{$sidebar}
	</div>
</div>
<div id="footer">
	<p><a href="http://cogear.ru"><img src="http://cogear.ru/uploads/cogear-powered.gif" alt="Работает на cogear" width="70" height="19" align="absmiddle"></a> &copy; {?date('Y')}<br/><small>Использование памяти: {$mem_usage}<br/>{if !empty($CI->db)}Запросов к базе данных: {? count($CI->db->queries)}<br/>{/if}Запросов в кеш: {? $CI->cache->counter}<br/>Время работы: {? $CI->benchmark->elapsed_time()}</small></p>
</div>
{*
 You can use
 {include file="global footer"}
 to include default site header
*}
{* footer *}
</body>
</html>
{* /footer *}
