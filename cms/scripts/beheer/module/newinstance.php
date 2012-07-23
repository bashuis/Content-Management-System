<?php
if (isset($request[2]) && is_numeric($request[2]))
{
    $module = $db->query("SELECT `m_id` AS id,
                                 `m_name` AS name,
                                 `m_path` AS path,
                                 `m_supports_multiple_instances` AS multiInstance,
                                 `m_has_admin` AS hasAdmin,
                                 `m_is_active` AS isActive,
                                 `m_is_owned` AS isOwned
                          FROM   `cms_modules`
                          WHERE   `m_id` = " . intval($request[2]) . "
                          LIMIT 1");

    if ($db->num_rows() == 0)
    {
        $output->addTitle("Niet gevonden");
        Messager::warning("Helaas, de door u gezochte module kan niet worden gevonden.");
    }
    else
    {
        $module = $db->fetch_assoc($module);

        if ($module['isOwned'] == 0)
        {
            $output->addTitle( "Geen toegang" );
            Messager::error("U heeft geen toegang tot deze module.");
        }
        else if ($module['isActive'] == 0)
        {
            $output->addTitle("Niet actief");
            Messager::error("Deze module is niet geactiveerd.");
        }
        else if ($module['multiInstance'] == 0)
        {
            $output->addTitle("Geen ondersteuning voor meerdere instanties");
            Messager::warning("Helaas, de module " . stripslashes($module['name']) . " heeft geen ondersteuning voor meerdere instanties.");
        }
        else
        {
            $output->addTitle(stripslashes( $module['name']));
            $output->addTitle("Instantie aanmaken");

            if (empty($_POST['instance-name']))
            {
                ?>
                <h2>Maak een nieuwe instantie aan</h2>
                <form action="" method="post">
                <fieldset class="form">
                    <div class="normalrow">
                        <label for="instance-name">Naam:</label>
                        <input type="text" name="instance-name" />
                    </div>
                    <div class="onlyinput">
                        <input type="submit" name="submit" value="Maak de instantie aan" />
                    </div>
                </fieldset>
                </form>
                <?php
            }
            else
            {
                $db->query("INSERT INTO `cms_module_instances`
                            SET `module` = " . $module['id'] . ",
                                `name` = '" . mysql_real_escape_string($_POST['instance-name']) . "'");

                $newInstanceId = $db->insert_id();

                $db->query("INSERT INTO `cms_item_permission`
                            SET `item` = " . $module['id'] . ",
                                `type` = 'module',
                                `group` = 1,
                                `mod_instance` = " . $newInstanceId);

                //	Get module-specific stuff setup
                $instanceManagementFile =  MODULES . $module['path'] . '/instance-management.php';
                if (file_exists($instanceManagementFile))
                {
                    require_once $instanceManagementFile;
                    instanceManagement_createInstance($newInstanceId);
                }

                if ($module['hasAdmin'] == 0)
                {
                    Messager::notify("De instantie is toegevoegd. Je kunt deze nu gebruiken.");
                    redirect('/beheer/module/');
                }
                else
                {
                    redirect('/beheer/module/view/' . $module['id'] . '/' . $newInstanceId . '/');
                }
            }
        }
    }
}
else
{
    Messager::error('U heeft geen module gekozen.');
}