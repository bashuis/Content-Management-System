<h1>Module beheer</h1>

<?php
if( User::isAdmin())
{
    $modules = $db->query("SELECT `m_id` AS id,
                                  `m_name` AS name,
                                  `m_path` AS path,
                                  `m_supports_multiple_instances` AS multiInstance
                           FROM   `cms_modules`
                           WHERE  `m_is_owned` = 1
                           AND	  `m_is_active` = 1
                           AND	  `m_has_admin` = 1");

    $instancesRS = $db->query("SELECT i.`module`,
                                      i.`instance`,
                                      i.`name`
                               FROM   `cms_module_instances` AS i");

    $instances = array();
    while($instancesR = $db->fetch_assoc())
    {
        $instances[] = $instancesR;
    }
}
else
{
    $modules = $db->query("SELECT DISTINCT m.`m_id` AS id,
                                           m.`m_name` AS name,
                                           m.`m_path` AS path,
                                           m.`m_supports_multiple_instances` AS multiInstance
                           FROM   `cms_modules` AS m
                           JOIN   `cms_item_edit_permission` AS iep
                               ON (m.`m_id` = iep.`item`)
                           WHERE  `m_is_owned` = 1
                           AND    `m_is_active` = 1
                           AND    `m_has_admin` = 1
                           AND    iep.`type` = 'module'
                           AND    iep.`group` IN (" . implode(",", User::getGroups()).")");

    $instancesRS = $db->query("SELECT i.`module`,
                                      i.`instance`,
                                      i.`name`
                               FROM   `cms_module_instances` AS i
                               JOIN   `cms_item_edit_permission` AS iep
                                   ON (i.`module` = iep.`item` AND i.`instance` = iep.`mod_instance`)
                               WHERE  iep.`group` IN (" . implode(",", User::getGroups()).")");

    $instances = array();
    while($instancesR = $db->fetch_assoc())
    {
        $instances[] = $instancesR;
    }
}

if ($db->num_rows($modules) == 0)
{
	Messager::notify('Er zijn geen plugins aanwezig.<br />Kijk op <a href="' . stripslashes(BRANDED_WEBSITE_PLUGINS) . '">' . stripslashes(BRANDED_WEBSITE_PLUGINS) . '</a> voor meer informatie over modules &amp; plugins.', false);
}
else
{
    $classTicker = true;
    ?>
    <table>
        <tr>
            <th>Module / Instantie</th>
            <th colspan="3">Opties</th>
        </tr>

        <?php 
        while ($module = $db->fetch_assoc($modules))
        {
            $classTicker = !$classTicker;
            ?>
            <tr class="module">
                <td><strong><?php echo $module['name']; ?></strong></td>
                <?php 
                if ($module['multiInstance'])
                {
                    if (User::isAdmin())
                    {
                        ?>
                        <td class="last">
                            <a href="/beheer/module/newinstance/<?php echo $module['id']; ?>"><img src="/icons/fugues/icons/plus-circle.png" style="vertical-align: bottom;" alt="" /></a>
                            <a href="/beheer/module/newinstance/<?php echo $module['id']; ?>">Nieuwe instantie</a>
                        </td>
                        <td class="last"></td>
                        <?php 
                        if (is_dir(MODULES . $module['path'] . '/lang/'))
                        {
                            ?>
                            <td>
                                <a href="/beheer/module/translate/<?php echo $module['id']; ?>"><img src="/icons/fugues/icons/locale.png" style="vertical-align: bottom;" alt="" /></a>
                                <a href="/beheer/module/translate/<?php echo $module['id']; ?>">Vertalen</a>
                            </td>
                        <?php
                        }
                        else
                        {
                            ?>
                            <td></td>
                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <td class="last"></td>
                        <td></td>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <td class="last">
                        <a href="/beheer/module/view/<?php echo $module['id']; ?>"><img src="/icons/fugues/icons/hammer-screwdriver.png" style="vertical-align: bottom;" alt="" /></a>
                        <a href="/beheer/module/view/<?php echo $module['id']; ?>">Beheren</a>
                    </td>

                    <?php 
                    if (User::isAdmin())
                    {
                        ?>
                        <td class="last">
                        <a href="/beheer/module/permissions/<?php echo $module['id']; ?>"><img src="/icons/fugues/icons/key.png" style="vertical-align: bottom;" alt="" /></a>
                        <a href="/beheer/module/permissions/<?php echo $module['id']; ?>">Toegang</a>
                        </td>
                        <?php 
                        if (is_dir(MODULES . $module['path'] . '/lang/'))
                        {
                            ?>
                            <td>
                                <a href="/beheer/module/translate/<?php echo $module['id']; ?>"><img src="/icons/fugues/icons/locale.png" style="vertical-align: bottom;" alt="" /></a>
                                <a href="/beheer/module/translate/<?php echo $module['id']; ?>">Vertalen</a>
                            </td>
                            <?php
                        }
                        else
                        {
                            ?>
                            <td></td>
                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <td class="last"></td>
                        <td></td>
                        <?php
                    }
                }
                ?>
            </tr>

            <?php 
            if ($module['multiInstance'])
            {
                foreach ($instances as $instance)
                {
                    if ($instance['module'] == $module['id'])
                    {
                        ?>
                        <tr>
                            <td>&nbsp;&nbsp;&bull;&nbsp;<?php echo $instance['name']; ?></td>
                            <td class="last">
                                <a href="/beheer/module/view/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>"><img src="/icons/fugues/icons/hammer-screwdriver.png" style="vertical-align: bottom;" /></a>
                                <a href="/beheer/module/view/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>">Beheren</a>
                            </td>
                            <?php 
                            if (User::isAdmin())
                            {
                                ?>
                                <td class="last">
                                    <a href="/beheer/module/permissions/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>"><img src="/icons/fugues/icons/key.png" style="vertical-align: bottom;" /></a>
                                    <a href="/beheer/module/permissions/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>">Toegang</a>
                                </td>
                                <td>
                                    <a href="/beheer/module/deleteinstance/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" /></a>
                                    <a href="/beheer/module/deleteinstance/<?php echo $module['id']; ?>/<?php echo $instance['instance']; ?>">Verwijderen</a>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                    }
                }
            }
        }
        ?>
    </table>
    <?php
}