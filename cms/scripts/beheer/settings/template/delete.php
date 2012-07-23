<h1>Template verwijderen</h1>

<?php

if (isset($_GET['id']) && is_numeric($_GET['id']))
{
    $db->prepare("SELECT `template_id`,
                         `title`,
                         `identifier`,
                         `default`
                  FROM   `cms_template`
                  WHERE  `template_id` = :template_id
                  LIMIT 1")
       ->bindValue('template_id', $_GET['id'])
       ->execute();
    if ($db->num_rows() == 1)
    {
        $template = $db->fetch_assoc();

        if ($template['default'] == 0)
        {
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                if (isset($_POST['yes']))
                {
                    Dir::delete(TEMPLATES . $template['identifier']);

                    $db->prepare("DELETE FROM `cms_template`
                                  WHERE  `template_id` = :template_id
                                  LIMIT 1")
                       ->bindValue('template_id', $template['template_id'])
                       ->execute();

                    Messager::ok('De template is succesvol verwijdert.', false, true);
                }

                redirect('?do=list');
            }

            ?>
            <p>
                Weet u zeker dat de template met de titel &quot;<strong><?=$template['title']?></strong>&quot; verwijderd moet worden?<br />
                <strong>Let op:</strong> Het verwijderen van een template is <strong>definitief</strong> en kan <strong>niet</strong> ongedaan gemaakt worden.
            </p>
            <form action="" method="post">
                <input type="submit" value="Ja, de template moet verwijdert worden." name="yes" />
                <input type="submit" value="Nee, de template moet niet verwijdert worden." name="no" />
            </form>
            <?php
        }
        else
        {
            Messager::error('U kunt de standaard template niet verwijderen. U moet eerst een andere template tot standaard template maken.', false, true);
            redirect('?do=list');
        }
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