<?php

$output->addTitle("Toegangs rechten");

if (isset($request[2]) && !empty($request[2]))
{
    $id = intval($request[2]);

    $db->query("SELECT `p_name`
                FROM   `cms_plugins`
                WHERE  `p_id` = " . $id . "
                LIMIT 1");
    if ($db->num_rows() == 1)
    {
        $permissionsUpdated = false;

        if (isset($_POST['savePermission']))
        {
            $rank3 = explode(',', $_POST['rank_3']);

            $db->query("DELETE FROM `cms_item_edit_permission`
                        WHERE `item` = " . $id . "
                        AND   `type` = 'plugin'");

            if (count($rank3) > 0)
            {
                foreach ($rank3 as $value)
                {
                    $db->query("INSERT INTO `cms_item_edit_permission` (`item`,
                                                                        `type`,
                                                                        `group`)
                                VALUES (" . $id . ",
                                        'plugin',
                                        " . intval($value) . ")");
                }
            }

            $permissionsUpdated = true;
        }

        if ($permissionsUpdated)
            Messager::ok('De wijzigingen in toegangs rechten zijn doorgevoerd.');

        Form::createPermissionList($id, 'plugin', true, false);
    }
    else
    {
        Messager::error('Deze plugin bestaat niet (meer).', false, true);
        redirect('/beheer/plugins/');
    }
}
else
{
    Messager::error('U heeft geen plugin geselecteerd om de rechten voor de bepalen.', false, true);
    redirect('/beheer/plugins/');
}