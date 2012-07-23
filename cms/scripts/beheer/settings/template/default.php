<?php

if (isset($_GET['id']) && is_numeric($_GET['id']))
{
    $db->prepare("SELECT `template_id`,
                         `title`,
                         `identifier`
                  FROM   `cms_template`
                  WHERE  `template_id` = :template_id
                  LIMIT 1")
       ->bindValue('template_id', $_GET['id'])
       ->execute();
    if ($db->num_rows() == 1)
    {
        $template = $db->fetch_assoc();

        $db->query("UPDATE `cms_template`
                    SET    `default` = 0");

        $db->prepare("UPDATE `cms_template`
                      SET    `default` = 1
                      WHERE  `template_id` = :template_id
                      LIMIT 1")
           ->bindValue('template_id', $template['template_id'])
           ->execute();

        Messager::ok('De standaard template is gewijzigd.', false, true);
        redirect('?do=list');
    }
    else
    {
        Messager::error('Het door u ingegeven id bestaat niet.', false, true);
        redirect('?do=list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingegeven.', false, true);
    redirect('?do=list');
}