<h1>Plugin beheer</h1>
<?php

if (isset($_GET['do']) && isset($_GET['id']) && User::isAdmin())
{
    $db->query("SELECT `p_name` AS name,
                       `p_code` AS code,
                       `p_active` AS active
                 FROM  `cms_plugins`
                 WHERE `p_id` = ".intval( $_GET['id'] )."
                 AND   `p_owned` = 1
                 LIMIT 1");
	
    if ($db->num_rows() == 1)
    {
        $plugin = $db->fetch_assoc();
        if ($_GET['do'] == 'install')
        {
            if($plugin['active'] == 1)
            {
                Messager::error($plugin['name'] . " is al geactiveerd.");
            }
            else
            {
                $db->query("UPDATE `cms_plugins`
                            SET    `p_active` = 1
                            WHERE  `p_id` = ".intval( $_GET['id'] )."
                            LIMIT 1");
                Messager::notify($plugin['name'] . " is geactiveerd.");
            }
        }
        else
        {
            if ($plugin['active'] == 1)
            {
                $db->query("UPDATE `cms_plugins`
                            SET    `p_active` = 0
                            WHERE  `p_id` = ".intval( $_GET['id'] )."
                            LIMIT 1");
                Messager::notify( $plugin['name'] . " is gedeactiveerd.");
            }
            else
            {
                Messager::error( $plugin['name'] . " is niet geactiveerd.");
            }
        }
    }
    else
    {
        Messager::error("U heeft geen plugin gekozen.");
    }
}

if (User::isAdmin())
{
    $plugins = $db->query("SELECT `p_id` AS id,
                                  `p_name` AS name,
                                  `p_code` AS code,
                                  `p_active` AS active
                            FROM  `cms_plugins`
                            WHERE `p_owned` = 1
                            ORDER BY `p_name`");
}
else
{
    $plugins = $db->query("SELECT `p_id` AS id,
                                  `p_name` AS name,
                                  `p_code` AS code,
                                  `p_active` AS active
                           FROM   `cms_plugins` AS p
                           JOIN   `cms_item_edit_permission` AS iep
                               ON (p.`p_id` = iep.`item`)
                           WHERE  `p_owned` = 1
                           AND    `p_active` = 1
                           AND    iep.`type` = 'plugin'
                           AND    iep.`group` IN (" . implode(",", User::getGroups()) . ")
                           ORDER BY `p_name`");
}

if ($db->num_rows($plugins) > 0)
{
    ?>
    <table>
        <tr>
            <th>Plugin:</th>
            <th colspan="5">Opties:</th>
        </tr>
        <?php
        while ($plugin = $db->fetch_assoc($plugins))
        {
            if ($plugin['active'] == 1)
            {
                ?>
                <tr>
                    <td><?php echo stripslashes(htmlentities($plugin['name'])); ?></td>

                    <td class="last" width="70">
                        <div style="width: 70px">
                            <a href="/beheer/plugins/manage/<?php echo $plugin['id']; ?>/">
                                <img src="/icons/fugues/icons/wrench-screwdriver.png" alt="Wrench icon" style="vertical-align: middle" />
                            </a>
                            <a href="/beheer/plugins/manage/<?php echo $plugin['id']; ?>/">Beheren</a>
                        </div>
                    </td>

                    <td class="last" width="87">
                        <div style="width: 87px">
                            <a href="/beheer/plugins/settings/<?php echo $plugin['id']; ?>/">
                                <img src="/icons/fugues/icons/wrench-screwdriver.png" alt="Wrench icon" style="vertical-align: middle" />
                            </a>
                            <a href="/beheer/plugins/settings/<?php echo $plugin['id']; ?>/">Instellingen</a>
                        </div>
                    </td>

                    <td class="last" width="130">
                        <div style="width: 130px">
                            <a href="/beheer/plugins/explain/<?php echo $plugin['id']; ?>/?iframe=true&amp;width=800&amp;height=300" rel="prettyPhoto" title="Uitleg in het gebruik van de plugin <?php print $plugin['name']; ?>">
                                <img src="/icons/fugues/icons/information.png" alt="Info icon" style="vertical-align: middle" />
                            </a>
                            <a href="/beheer/plugins/explain/<?php echo $plugin['id']; ?>/?iframe=true&amp;width=800&amp;height=300" rel="prettyPhoto" title="Uitleg in het gebruik van de plugin <?php print $plugin['name']; ?>">
                                Uitleg van gebruik
                            </a>
                        </div>
                    </td>

                    <td class="last" width="75">
                        <div style="width: 75px">
                            <?php
                            if (User::isAdmin())
                            {
                                ?>
                                <a href="/beheer/plugins/permissions/<?php echo $plugin['id']; ?>/">
                                    <img src="/icons/fugues/icons/key.png" alt="Sleutel icon" style="vertical-align: middle" />
                                </a>
                                <a href="/beheer/plugins/permissions/<?php echo $plugin['id']; ?>/">Toegang</a>
                                <?php
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <div style="min-width: 90px">
                            <?php
                            if (User::isAdmin())
                            {
                                ?>
                                <a href="/beheer/plugins/?do=uninstall&amp;id=<?php echo $plugin['id']; ?>">
                                    <img src="/icons/fugues/icons/status-busy.png" alt="Disconnect icon" style="vertical-align: middle" />
                                </a>
                                <a href="/beheer/plugins/?do=uninstall&amp;id=<?php echo $plugin['id']; ?>">Deactiveren</a>
                                <?php
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
            else
            {
                ?>
                <tr>
                    <td><?php echo stripslashes(htmlentities($plugin['name'])); ?></td>
                    <td colspan="5">
                        <?php
                        if (User::isAdmin())
                        {
                            ?>
                            <a href="/beheer/plugins/?do=install&amp;id=<?php echo $plugin['id']; ?>">
                                <img src="/icons/fugues/icons/status.png" alt="Connect icon" style="vertical-align: middle" />
                            </a>
                            <a href="/beheer/plugins/?do=install&amp;id=<?php echo $plugin['id']; ?>">Activeren</a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <?php
}
else
{
    Messager::notify('Er zijn geen plugins aanwezig.<br />Kijk op <a href="' . stripslashes(BRANDED_WEBSITE_PLUGINS) . '">' . stripslashes(BRANDED_WEBSITE_PLUGINS) . '</a> voor meer informatie over modules &amp; plugins.', false);
}