<h1>Template titel wijzigen</h1>
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_POST['title']) && !empty($_POST['title']))
            {
                $db->prepare("SELECT 1
                              FROM   `cms_template`
                              WHERE  `title` = :title
                              AND    `template_id` <> :template_id
                              LIMIT 1")
                   ->bindValue('title',            $_POST['title'])
                   ->bindValue('template_id',      $template['template_id'])
                   ->execute();
                if ($db->num_rows() == 0)
                {
                    $db->prepare("UPDATE `cms_template`
                                  SET    `title` = :title
                                  WHERE  `template_id` = :template_id
                                  LIMIT 1")
                       ->bindValue('title',       $_POST['title'])
                       ->bindvalue('template_id', $template['template_id'])
                       ->execute();
                    
                    Messager::ok('De titel van de template is succesvol gewijzigd.', false, true);
                    redirect('?do=list');
                }
                else
                {
                    Messager::error('De door u opgegeven titel bestaat al.');
                }
            }
            else
            {
                Messager::error('U heeft geen titel ingevuld.');
            }
        }

        ?>
        <form action="" method="post">
            <div class="normalrow">
                <label>Titel:</label>
                <input type="text" name="title" value="<?=$template['title']?>" />
            </div>
            <div class="onlyinput">
                <input type="submit" value="Opslaan" />
            </div>
        </form>
        <?php
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