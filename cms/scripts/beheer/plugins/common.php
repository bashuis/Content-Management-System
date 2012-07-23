<?php

$output->addTitle("Plugins");

if(isset($request[2]) && is_numeric($request[2]))
{	
    ob_start();
    ?>
    <li<?php doActive('manage', 1); doActive('start',1); ?>>
        <a href="/beheer/plugins/manage/<?php echo $request[2]; ?>/">
            <img src="/icons/fugues/icons/user-black.png" alt="Beheer icon" style="vertical-align: bottom;" />
            Beheren
        </a>
    </li>
    <li<?php doActive('settings', 1); ?>>
        <a href="/beheer/plugins/settings/<?php echo $request[2]; ?>/">
            <img src="/icons/fugues/icons/wrench-screwdriver.png" alt="Instellingen icon" style="vertical-align: bottom;" />
            Instellingen
        </a>
    </li>
    <li>
        <a href="/beheer/plugins/explain/<?php echo $request[2]; ?>/?iframe=true&amp;width=800&amp;height=300" rel="prettyPhoto" title="Uitleg in het gebruik van deze plugin">
            <img src="/icons/fugues/icons/information.png" alt="Info icon" style="vertical-align: bottom" />
            Uitleg van gebruik
        </a>
    </li>
    <?php
    if (User::isAdmin())
    {
        ?>
        <li<?php doActive('permissions', 1); ?>>
            <a href="/beheer/plugins/permissions/<?php echo $request[2]; ?>/">
                <img src="/icons/fugues/icons/key.png" alt="Toegang icon" style="vertical-align: bottom;" />
                Toegang
            </a>
        </li>
        <?php

        $plugins = $db->query("SELECT `p_id` AS id,
                                      `p_active` AS active
                               FROM   `cms_plugins` AS p
                               WHERE  `p_owned` = 1
                               AND    `p_id` = " . intval($request[2]) . "
                               LIMIT 1");
        if ($db->num_rows() == 1)
        {
            $plugin = $db->fetch_assoc();

            if ($plugin['active'] == 1)
            {
                ?>
                <li>
                    <a href="/beheer/plugins/?do=uninstall&amp;id=<?php echo $plugin['id']; ?>">
                        <img src="/icons/fugues/icons/status-busy.png" alt="Disconnect icon" style="vertical-align: middle" />
                        Deactiveren
                    </a>
                </li>
                <?php
            }
            else
            {
                ?>
                <li>
                    <a href="/beheer/plugins/?do=install&amp;id=<?php echo $plugin['id']; ?>">
                        <img src="/icons/fugues/icons/status.png" alt="Disconnect icon" style="vertical-align: middle" />
                    </a>
                    <a href="/beheer/plugins/?do=install&amp;id=<?php echo $plugin['id']; ?>">Deactiveren</a>
                </li>
                <?php
            }
        }
    }

    $quickMenuContent = ob_get_clean();
    QuickMenu::add($quickMenuContent);
}