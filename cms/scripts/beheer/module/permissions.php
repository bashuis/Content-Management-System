<?php

$output->addTitle("Toegangs rechten");

if (empty($request[2]))
{
    Messager::warning('U heeft geen module/instantie gekozen om de toegangs rechten voor in te stellen.');
}
else
{
    $db->query("SELECT `m_name` AS name,
                       `m_is_owned` AS isOwned,
                       `m_supports_multiple_instances` AS multiInstance,
                       `m_id` AS id
                FROM   `cms_modules`
                WHERE  `m_id` = " . intval($request[2]) . "
                LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $module = $db->fetch_assoc();

        if($module['multiInstance'] == 1)
        {
            if (empty($request[3]))
            {
                $listInstances = true;
            }
            else
            {
                $instance = $db->query("SELECT `instance` AS id,
                                               `name` AS name
                                        FROM   `cms_module_instances`
                                        WHERE  `instance` = " . intval($request[3]) . "
                                        LIMIT 1");

                if ($db->num_rows($instance) == 1)
                {
                    $listInstances = false;
                    $instance = $db->fetch_assoc($instance);
                }
                else
                {
                    Messager::warning("Je hebt gekozen voor een niet bestaande instantie van de module " . $module['name'] . ".");
                    $listInstances = true;
                }
            }
        }
        else
        {
            $listInstances = false;
            $instance = array('id' => 0);
        }

        if ($listInstances === true)
        {
            $instances = $db->query("SELECT `instance` AS id,
                                            `name` AS name
                                    FROM    `cms_module_instances`
                                    WHERE   `module` = " . $module['id'] . "
                                    ORDER BY `name`");
            if ($db->num_rows($instances) > 0)
            {
                ?>
                <p>Kies een instantie van deze module om te beheren:</p>
                <ul>
                    <?php
                    while ($instance = $db->fetch_assoc($instances))
                    {
                        echo "\n\t<li><a href=\"/beheer/module/permissions/" . $module['id'] . "/" . $instance['id'] . "/\">" . Generic::stripAndClean($instance['name']) . "</a></li>";
                    }
                    ?>
                </ul>
                <?php
            }
            else
            {
                Messager::warning("Er zijn geen instanties van deze module.");
            }
        }
        else
        {
            ?>
            <h2>De toegangs rechten voor <?php echo $module['name']; ?><?php if(isset($instance['name'])) { echo " : ", Generic::stripAndClean($instance['name']); } ?>.</h2>
            <?php
            $permissionsUpdated = false;

            if (isset($_POST['savePermission']))
            {
                $rank1 = explode(',', $_POST['rank_1']);
                $rank3 = explode(',', $_POST['rank_3']);

                $db->query("DELETE FROM `cms_item_permission`
                            WHERE  `item` = ".$module['id']."
                            AND	`mod_instance` = ".$instance['id']."
                            AND	`type` = 'module'");

                $db->query("DELETE FROM `cms_item_edit_permission`
                            WHERE `item` = ".$module['id']."
                            AND	`mod_instance` = ".$instance['id']."
                            AND	`type` = 'module'");

                if (count($rank1) > 0)
                {
                    foreach ($rank1 as $value)
                    {
                        $db->query("INSERT INTO `cms_item_permission` (`item`,
                                                                        `type`,
                                                                        `group`,
                                                                        `mod_instance`)
                                    VALUES (" .$module['id'] . ",
                                            'module',
                                            " . intval($value) . ",
                                            " . $instance['id'] . ")");
                    }
                }

                if (count($rank3) > 0)
                {
                    foreach ($rank3 as $value)
                    {
                        $db->query("INSERT INTO `cms_item_edit_permission` (`item`,
                                                                            `type`,
                                                                            `group`,
                                                                            `mod_instance`)
                                    VALUES (" . $module['id'] . ",
                                            'module',
                                            " . intval($value) . ",
                                            " . $instance['id'] . ")");
                    }
                }

                $permissionsUpdated = true;
            }

            if ($permissionsUpdated)
            {
                Messager::notify("De wijzigingen in toegangs rechten zijn doorgevoerd.");
            }

            Form::createPermissionList($module['id'] . ':' . $instance['id'], 'module', true);
        }
    }
    else
    {
        Messager::warning("De module waarvoor je de toegangsrechten wilt aanpassen bestaat niet (meer).");
    }
}

$request[0] = 'module';