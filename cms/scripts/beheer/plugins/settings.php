<?php

if (isset($request[2]) && is_numeric($request[2]))
{
    $plugin = $db->query("SELECT `p_id` AS id,
                                 `p_name` AS name,
                                 `p_code` AS code,
                                 `p_active` AS active
                          FROM   `cms_plugins`
                          WHERE  `p_id` = " . intval($request[2]) . "
                          LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $plugin = $db->fetch_assoc( $plugin );

        if (User::isAdmin())
        {
            $adminIsAllowed = true;
        }
        else
        {
            $adminIsAllowedCheck = $db->query("SELECT 1
                                               FROM   `cms_plugin_admins`
                                               WHERE  `plugin` = " . $plugin['id'] . "
                                               AND    `group` IN (" . implode(",", user::getGroups()) . ")
                                               LIMIT 1");

            $adminIsAllowed = ($db->num_rows($adminIsAllowedCheck) == 1);
        }

        if($adminIsAllowed)
        {
            $settings = $db->query("SELECT `settings`
                                    FROM   `cms_plugin_settings`
                                    WHERE  `plugin` = " . $plugin['id'] . "
                                    LIMIT 1");
            if($db->num_rows($settings) == 1)
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

            require_once PLUGINS . $plugin['code'] . '/plugin.php';
            $plugin = new $plugin['code']($plugin, $settings);
            $plugin->settings();
        }
        else
        {
            Messager::error("U heeft geen toegang tot de instellingen van deze plugin.");
        }
    }
    else
    {
        Messager::error("Plugin niet gevonden.");
    }
}
else
{
    Messager::error('U heeft geen plugin gekozen.');
}