<?php

function makePublic ($id, $type)
{
    global $db;
    
    $db->query("SELECT 1
		FROM   `cms_item_permission`
		WHERE  `item` = " . $id . "
                AND    `type` = '" . $type . "'
                AND    `group` = 1
		LIMIT 1");

    if($db->num_rows() == 0)
    {
        $db->query("INSERT INTO `cms_item_permission` (`item`,
                                                       `group`,
                                                       `type`)
                    VALUES (" . $id . ",
                            1,
                            '" . $type . "')");
    }
}