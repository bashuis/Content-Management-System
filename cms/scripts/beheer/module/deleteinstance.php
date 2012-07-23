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
                          WHERE  `m_id` = " . intval( $request[2] ) . "
                          LIMIT 1");
    if ($db->num_rows($module) == 0)
    {
        $output->addTitle("Niet gevonden");
        Messager::warning("Helaas, de door u gezochte module kan niet worden gevonden.");
    }
    else
    {
        $module = fetch_assoc( $module );
        if( $module['isOwned'] == 0 )
        {
                $output->addTitle( "Geen toegang" );
                Messager::error("U heeft geen toegang tot deze module.");
        }
        else if( $module['isActive'] == 0 )
        {
                $output->addTitle( "Niet actief" );
                Messager::error("Deze module is niet geactiveerd.");
        }
        else if( $module['multiInstance'] == 0 )
        {
                $output->addTitle( "Geen ondersteuning voor meerdere instanties" );
                Messager::warning("Helaas, de module " . stripslashes( $module['name'] ) . " heeft geen ondersteuning voor meerdere instanties.");
        }
        else
        {
            $instance = $db->query("SELECT `name`
                                    FROM   `cms_module_instances`
                                    WHERE  `instance` = " . intval($request[3]) . "
                                    AND	   `module` = " . $module['id'] . "
                                    LIMIT 1");
            if ($db->num_rows($instance) == 1)
            {
                $instance           = $db->fetch_assoc($instance);
                $instance['id']     = intval($request[3]);
                $instance['module'] = $module['id'];

                if (isset($_POST['confirm']))
                {
                    //	Delete the instance, put menu items on non-active.
                    $instanceManagementFile =  MODULES . $module['path'] . '/instance-management.php';
                    if (file_exists($instanceManagementFile))
                    {
                        require_once $instanceManagementFile;
                        instanceManagement_deleteInstance($instance['id']);
                    }

                    $db->query("DELETE FROM `cms_module_instances`
                                WHERE `instance` = " . $instance['id'] . "
                                LIMIT 1");

                    $db->query("DELETE FROM `cms_item_permission`
                                WHERE `type` = 'module'
                                AND   `item` = " . $module['id'] . "
                                AND   `mod_instance` = " . $instance['id'] . "
                                LIMIT 1");

                    $db->query("UPDATE `cms_menuitem`
                                SET `mi_active` = 0
                                WHERE `mi_mod_instance` = " . $instance['id']);

                    Messager::notify("De instantie is verwijderd.", false, true);
                    unset($request[3]);
                    redirect('/beheer/module/');
                }
                else
                {
                    ?>
                    <p>Weet u zeker dat u instantie <strong><?php echo Generic::stripAndClean($instance['name']); ?></strong> van module <?php echo stripAndClean( $module['name'] ); ?> wilt verwijderen?</p>
                    <form action="" method="post">
                        <p><input type="submit" name="confirm" value="Ja, verwijder deze instantie" /></p>
                    </form>
                    <?php
                }
            }
            else
            {
                $output->addTitle( "Niet gevonden" );
                Messager::error("De door u gezochte instantie kan niet worden gevonden.");
            }
        }
    }
}
else
{
    Messager::error('U heeft geen module gekozen.');
}