<?php

$output->addTitle("Toegangs rechten");

if (isset($request[2]) && !empty($request[2]))
{
    $id = intval($request[2]);

    $db->query("SELECT `p_name`
                FROM   `cms_file`
                WHERE  `f_id` = " . $id . "
                LIMIT 1");
    if ($db->num_rows() == 1)
    {
        $perm = $db->fetch_assoc();

        echo '<h1>Toegangs rechten [file] <i>' . $perm['p_name'] . '</i></h1>';

        $permissionsUpdated = false;

        if (isset($_POST['savePermission']))
        {
            $rank1 = explode(',', $_POST['rank_1']);
            $rank3 = explode(',', $_POST['rank_3']);

            $db->query("DELETE FROM `cms_item_permission`
                        WHERE `item` = " . $id . "
                        AND   `type` = 'file'");

            $db->query("DELETE FROM `cms_item_edit_permission`
                        WHERE `item` = " . $id . "
                        AND   `type` = 'file'");

            if (count($rank1) > 0)
            {
                foreach ($rank1 as $value)
                {
                    $db->query("INSERT INTO `cms_item_permission` (`item`,
                                                                   `type`,
                                                                   `group`)
                                VALUES (" . $id . ",
                                        'file',
                                        " . intval($value) . ")");
                }
            }

            if (count($rank3) > 0)
            {
                foreach ($rank3 as $value)
                {
                    $db->query("INSERT INTO `cms_item_edit_permission` (`item`,
                                                                        `type`,
                                                                        `group`)
                                VALUES (" . $id . ",
                                        'file',
                                        " . intval($value) . ")");
                }
            }

            $permissionsUpdated = true;
        }

        if ($permissionsUpdated)
            Messager::ok('De wijzigingen in toegangs rechten zijn doorgevoerd.');

        Form::createPermissionList($id, 'file', true);
    }
    else
    {
        Messager::error('Deze file bestaat niet (meer).', false, true);
        redirect('/beheer/menu/list');
    }
}
else
{
    Messager::error('U heeft geen file geselecteerd om de rechten voor de bepalen.', false, true);
    redirect('/beheer/menu/list');
}