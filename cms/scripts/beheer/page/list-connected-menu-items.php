<link rel="stylesheet" type="text/css" media="all" href="/beheer/resources/css/style.css" />
<link rel="stylesheet" type="text/css" media="all" href="/cms/style.css" />
<style type="text/css">
    html, body {
        background: #FFF;
    }
</style>

<?php
if( empty( $request[2] ) )
{
	?><p class="warning">Je moet wel een pagina kiezen he!</p><?php
}
else
{
	$pid = intval($request[2]);
	
	$menuItems = query("
		SELECT
				t.`name`,
				mi.`mi_parent` AS parent
		FROM
				`cms_menuitem` AS mi
		JOIN
				`cms_menuitem_translation` AS t
			ON	mi.`mi_id` = t.`mi_id`
		WHERE
				mi.`mi_type` = 'page'
			AND	mi.`mi_item_id` = " . $pid . "
			AND	t.`lang_id` = 1	
		;
	");
	
	if( num_rows( $menuItems ) == 0 )
	{
		?><h1>Verbonden menu items: <em>Geen</em>.</h1><?php
	}
	else
	{
		function getParentList( $menuItem, $currentList = array() )
		{
			if( $menuItem['parent'] == 0 )
				return $currentList;
				
			$parentItem = fetch_assoc( query("
				SELECT
						t.`name`,
						mi.`mi_parent` AS parent
				FROM
						`cms_menuitem` AS mi
				JOIN
						`cms_menuitem_translation` AS t
					ON	mi.`mi_id` = t.`mi_id`
				WHERE
						mi.`mi_id` = " . $menuItem['parent'] . "
					AND	t.`lang_id` = 1
				LIMIT
						1
				;
			") );
			
			$currentList[] = $parentItem['name'];
			//if( $parentItem['parent'] != 0 )
				$currentList = getParentList( $parentItem, $currentList );
			
			return $currentList;
		}
		
		?>
<h1>Verbonden menu items:</h1>
<ul>
		<?php
		while( $menuItem = fetch_assoc( $menuItems ) )
		{
			?>
	<li><?php
		$trail = array_reverse( getParentList( $menuItem ) );
		$trail = array_merge( $trail, array( $menuItem['name'] ) );
		echo implode(" &rarr; ", $trail );
	?></li>	
			<?php
		}
		?>
</ul>
	<?php
}
?>
<p>
Directe link naar de pagina: <?php
$defaultLanguage = query("SELECT `lang_tag` AS tag FROM `cms_language` WHERE `lang_id` = ".$cms_settings['default_language']." LIMIT 1;");
$defaultLanguage = fetch_assoc( $defaultLanguage );
$link = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $defaultLanguage['tag'] . '/page/' . intval( $request[2] ) . '/';
?>
<br />
<a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a>
</p>
	<?php
}
exit;
?>