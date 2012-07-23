<h1>Bestand verwijderen</h1>

<?php

if (isset($_GET['type']) && isset($_GET['id']) && isset($_GET['file']))
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
        $templateDir = TEMPLATES . $template['identifier'] . DS;

        if ($_GET['type'] == 'css')
        {
            $templateDir = $templateDir . 'css' . DS;
            if (file_exists($templateDir . $_GET['file']))
            {
                if ($_SERVER['REQUEST_METHOD'] == 'POST')
                {
                    if (isset($_POST['yes']))
                    {
                        unlink($templateDir . $_GET['file']);
                        Template::minify($template['template_id']);
                        Messager::ok('Het bestand is succesvol verwijdert.', false, true);
                    }
                    redirect('?do=update&id=' . $template['template_id']);
                }
                ?>
                <p>
                    Weet u zeker dat het CSS bestand met de naam &quot;<strong><?=$_GET['file']?></strong>&quot; verwijderd moet worden?<br />
                    <strong>Let op:</strong> Het verwijderen van een bestand is <strong>definitief</strong> en kan <strong>niet</strong> ongedaan gemaakt worden.
                </p>
                <form action="" method="post">
                    <input type="submit" value="Ja, het bestand moet verwijdert worden." name="yes" />
                    <input type="submit" value="Nee, het bestand moet niet verwijdert worden." name="no" />
                </form>
                <?php
            }
            else
            {
                Messager::error('Dit bestand bestaat niet.', false, true);
                redirect('?do=update&id=' . intval($_GET['id']));
            }
        }
        else if ($_GET['type'] == 'js')
        {
            $templateDir = $templateDir . 'js' . DS;
            if (file_exists($templateDir . $_GET['file']))
            {
                if ($_SERVER['REQUEST_METHOD'] == 'POST')
                {
                    if (isset($_POST['yes']))
                    {
                        unlink($templateDir . $_GET['file']);
                        Template::minify($template['template_id']);
                        Messager::ok('Het bestand is succesvol verwijdert.', false, true);
                    }
                    redirect('?do=update&id=' . $template['template_id'] . '&type=js');
                }
                ?>
                <p>
                    Weet u zeker dat het javascript bestand met de naam &quot;<strong><?=$_GET['file']?></strong>&quot; verwijderd moet worden?<br />
                    <strong>Let op:</strong> Het verwijderen van een bestand is <strong>definitief</strong> en kan <strong>niet</strong> ongedaan gemaakt worden.
                </p>
                <form action="" method="post">
                    <input type="submit" value="Ja, het bestand moet verwijdert worden." name="yes" />
                    <input type="submit" value="Nee, het bestand moet niet verwijdert worden." name="no" />
                </form>
                <?php
            }
            else
            {
                Messager::error('Dit bestand bestaat niet.', false, true);
                redirect('?do=update&id=' . intval($_GET['id']) . '&type=js');
            }
        }
        else if ($_GET['type'] == 'image')
        {
            $templateDir = $templateDir . 'images' . DS;
            if (file_exists($templateDir . $_GET['file']))
            {
                if ($_SERVER['REQUEST_METHOD'] == 'POST')
                {
                    if (isset($_POST['yes']))
                    {
                        unlink($templateDir . $_GET['file']);
                        Messager::ok('Het bestand is succesvol verwijdert.', false, true);
                    }
                    redirect('?do=update&id=' . $template['template_id'] . '&type=images');
                }
                ?>
                <p>
                    Weet u zeker dat de afbeelding met de naam &quot;<strong><?=$_GET['file']?></strong>&quot; verwijderd moet worden?<br />
                    <strong>Let op:</strong> Het verwijderen van een bestand is <strong>definitief</strong> en kan <strong>niet</strong> ongedaan gemaakt worden.
                </p>
                <form action="" method="post">
                    <input type="submit" value="Ja, het bestand moet verwijdert worden." name="yes" />
                    <input type="submit" value="Nee, het bestand moet niet verwijdert worden." name="no" />
                </form>
                <?php
            }
            else
            {
                Messager::error('Dit bestand bestaat niet.', false, true);
                redirect('?do=update&id=' . intval($_GET['id']) . '&type=images');
            }
        }
        else
        {
            Messager::error('Dit bestandstype bestaat niet.', false, true);
            redirect('?do=update&id=' . intval($_GET['id']));
        }
    }
    else
    {
        Messager::error('Deze template bestaat niet.', false, true);
        redirect('?do=list');
    }
}
else
{
    Messager::error('U heeft niet alle gegevens volledig ingevuld.', false, true);
    redirect('?do=update&id=' . intval($_GET['id']));
}