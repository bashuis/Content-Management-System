<?php

$moduleId = intval($request[2]);
$instanceId = isset($request[3]) ? intval($request[3]) : 0;

if(User::isAdmin())
{
    $hasEditPermissions = true;
}
else
{
    if (isset($request[3]))
    {
        $db->query("SELECT 1
                    FROM   `cms_item_edit_permission`
                    WHERE  `item` = " . $moduleId . "
                    AND	   `type` = 'module'
                    AND	   `mod_instance` = " . $instanceId . "
                    AND	   `group` IN(" . implode(",",user::getGroups() ) . ")
                    LIMIT  1");
    }
    else
    {
        $db->query("SELECT 1
                    FROM   `cms_item_edit_permission`
                    WHERE  `item` = " . $moduleId . "
                    AND	   `type` = 'module'
                    AND	   `group` IN(" . implode(",",user::getGroups() ) . ")
                    LIMIT 1");
    }

    $hasEditPermissions = ($db->num_rows() == 1);
}

if ($hasEditPermissions)
{
    $module = $db->query("SELECT `m_id` AS id,
                                 `m_name` AS name,
                                 `m_path` AS path,
                                 `m_supports_multiple_instances` AS multiInstance,
                                 `m_has_admin` AS hasAdmin,
                                 `m_is_active` AS isActive,
                                 `m_is_owned` AS isOwned
                          FROM   `cms_modules`
                          WHERE  `m_id` = " . intval($request[2]) . "
                          LIMIT 1");

    if ($db->num_rows($module) == 0)
    {
        $output->addTitle("Module niet gevonden");
        Messager::warning("Helaas, de door u gezochte module kan niet worden gevonden.");
    }
    else
    {
        $module = $db->fetch_assoc($module);
        $output->addTitle(Generic::stripAndClean($module['name']));

        if ($module['isOwned'] == 0)
        {
            Messager::error("U heeft geen toegang tot deze module.");
        }
        else if ($module['isActive'] == 0)
        {
            Messager::error("Deze module is niet geactiveerd.");
        }
        else if ($module['hasAdmin'] == 0)
        {
            Messager::warning("Deze module heeft geen beheersmogelijkheden.");
        }
        else
        {
            $modulePath = SYSTEMPATH . 'modules/' . $module['path'];
            if (is_dir($modulePath))
            {
                $moduleAdminFile = $modulePath . '/backend.php';
                if (file_exists($moduleAdminFile))
                {
                    if (is_readable($moduleAdminFile))
                    {
                        try
                        {
                            Lang::loadTranslationsForModule($module['path'], LANGKEY);
                        }
                        catch(Exception $exception)
                        {
                            Lang::loadTranslationsForModule($module['path'], 'nl');
                        }

                        if ($module['multiInstance'])
                        {
                            if (isset($request[3]))
                            {
                                $instance = $db->query("SELECT `module`,
                                                               `menuitem`,
                                                               `instance` AS id,
                                                               `name`
                                                        FROM   `cms_module_instances`
                                                        WHERE  `instance` = " . intval( $request[3] ) . "
                                                        LIMIT 1");

                                if ($db->num_rows($instance) == 1)
                                {
                                    $instance = $db->fetch_assoc($instance);
                                    if ($module['id'] == $instance['module'])
                                    {
                                        require_once $moduleAdminFile;
                                    }
                                    else
                                    {
                                        Messager::error("De instantie die u aan probeert te passen, is een instantie van een andere module.");
                                    }
                                }
                                else
                                {
                                    Messager::error("U probeert een niet bestaande instantie van een module te wijzigen.");
                                }
                            }
                            else
                            {
                                require_once BEHEERSCRIPTS . 'module/kies-instantie.php';
                            }
                        }
                        else
                        {
                            require_once $moduleAdminFile;
                        }
                    }
                    else
                    {
                        Messager::error("Alle benodigde bestanden zijn wel gevonden, maar de user heeft geen read rights voor het bestand.");
                    }
                }
                else
                {
                    Messager::error("De module is wel gevonden in de database en de map is wel aangemaakt, maar het beheerders bestand bestaat niet.");
                }
            }
            else
            {
                Messager::error("De module is wel gevonden in de database, maar de bestanden kunnen niet worden gevonden.");
            }
        }
    }
}
else
{
    $output->addTitle("Geen toegang");
    Messager::error("Helaas, u heeft niet voldoende rechten om deze module/instantie te kunnen beheren.");
}