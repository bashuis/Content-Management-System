<?php

if (isset($request[2]) && is_numeric($request[2]))
{
    if (User::isAdmin())
    {
        $plugin = $db->query("SELECT `p_id` AS id,
                                     `p_name` AS name,
                                     `p_code` AS code,
                                     `p_active` AS active
                              FROM   `cms_plugins`
                              WHERE  `p_id` = ".intval($request[2])."
                              LIMIT 1");
    }
    else
    {
        $plugin = $db->query("SELECT `p_id` AS id,
                                     `p_name` AS name,
                                     `p_code` AS code,
                                     `p_active` AS active
                              FROM   `cms_plugins` AS p
                              JOIN   `cms_item_edit_permission` AS iep
                                  ON (p.`p_id` = iep.`item`)
                              WHERE  `p_id` = " . intval($request[2]) . "
                              AND    `p_owned` = 1
                              AND    `p_active` = 1
                              AND    iep.`type` = 'plugin'
                              AND    iep.`group` IN (" . implode(",", User::getGroups()) . ")
                              ORDER BY `p_name`");
    }

    if ($db->num_rows() == 1)
    {
        $plugin = $db->fetch_assoc();

        $settings = $db->query("SELECT `settings`
                                FROM   `cms_plugin_settings`
                                WHERE  `plugin` = ".$plugin['id']."
                                LIMIT  1");
        if ($db->num_rows($settings) == 1)
        {
            $settings = $db->fetch_assoc($settings);
            $settings = IniParser::parse($settings['settings'], true);
        }
        else
        {
            $settings = array();
        }
        if(!$plugin['active'])
        {
            Messager::warning('Let op, deze plugin is momenteel niet actief!');
        }

        require PLUGINS . $plugin['code'] . '/plugin.php';
        $plugin = new $plugin['code']($plugin, $settings);
        $plugin->administrate();
    }
    else
    {
        Messager::error("U heeft geen toegang tot deze plugin.", false, true);
        redirect('/beheer/plugins');
    }
}
else
{
    Messager::error('U heeft geen plugin gekozen.', false, true);
    redirect('/beheer/plugins');
}