<?php

$db->query("SELECT l.`l_target`,
                   t.`url`
            FROM   `cms_link` AS l
            JOIN   `cms_link_translation` AS t
                ON (l.`l_id` = t.`l_id`)
            WHERE  l.`l_id` = " . $requestedPage['id'] . "
            AND	   t.`lang_id` = " . LANG . "
            LIMIT 1");

if ($db->num_rows() == 1)
{
    $link = $db->fetch_assoc();

    redirect($link['url']);
}