<?php
ob_clean();

$db->query("SELECT `p_id` AS id,
                   `p_name` AS name,
                   `p_code` AS code,
                   `p_active` AS active
            FROM   `cms_plugins`
            WHERE  `p_id` = ".intval($request[2])."
            LIMIT 1");

if ($db->num_rows() == 1 )
{
    $plugin = $db->fetch_assoc();

    require_once PLUGINS . $plugin['code'] . '/plugin.php';

    $pluginClass = new $plugin['code']($plugin, null);
    $showHeader = $pluginClass->showHeader;

    if (method_exists($pluginClass, 'add_explain'))
    {
        ob_start();

        print "<p>";
        print @$pluginClass->add_explain();
        print "</p>";

        $showHeader = $pluginClass->showHeader;

        $explainText = ob_get_clean();
    }

    if ($showHeader)
    {
        print "<h1>Uitleg voor het gebruik van " . $plugin['name'] . "</h1>";
        print "<p>Om deze plugin in te laden in de template via PHP, voegt u de volgende code toe:<br />";
        highlight_string( "<?php Plugin_load::loadPlugin('" . $plugin['code'] . "'); ?>" );
        print "</p>";
        print "<p>Wilt u deze plugin tonen als deel van uw content? Plaats dan de volgende code in een pagina:<br />";
        print "<code>##plugin:".$plugin['code']."##</code>";
        print "</p>";
    }

    if (isset($explainText))
        print $explainText;
}

echo ob_get_clean();

exit();