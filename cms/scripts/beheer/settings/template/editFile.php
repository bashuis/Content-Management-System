<h1>Bestand bewerken</h1>

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
                    if (!empty($_POST['content']))
                    {
                        file_put_contents($templateDir . $_GET['file'], $_POST['content']);
                        Template::minify($template['template_id']);
                        Messager::ok('Het bestand is succesvol aangepast.');
                    }
                }
                $fileContent = file_get_contents($templateDir . $_GET['file']);

                ?>
                <form action="" method="post">
                    <div class="normalrow">
                        <label>Bestand:</label>
                        <?=$_GET['file']?>
                    </div>
                    <div class="normalrow">
                        <label>Inhoud:</label>
                        <textarea rows="10" cols="50" name="content" style="width: 80%; min-height: 400px;"><?=$fileContent?></textarea>
                    </div>
                    <div class="onlyinput">
                        <input type="submit" value="Opslaan" />
                        <a href="?do=update&amp;id=<?=$template['template_id']?>">Terug naar bestanden beheer</a>
                    </div>
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
                    if (!empty($_POST['content']))
                    {
                        file_put_contents($templateDir . $_GET['file'], $_POST['content']);
                        Template::minify($template['template_id']);
                        Messager::ok('Het bestand is succesvol aangepast.');
                    }
                }
                $fileContent = file_get_contents($templateDir . $_GET['file']);

                ?>
                <form action="" method="post">
                    <div class="normalrow">
                        <label>Bestand:</label>
                        <?=$_GET['file']?>
                    </div>
                    <div class="normalrow">
                        <label>Inhoud:</label>
                        <textarea rows="10" cols="50" name="content" style="width: 80%; min-height: 400px;"><?=$fileContent?></textarea>
                    </div>
                    <div class="onlyinput">
                        <input type="submit" value="Opslaan" />
                        <a href="?do=update&amp;id=<?=$template['template_id']?>&amp;type=js">Terug naar bestanden beheer</a>
                    </div>
                </form>
                <?php
            }
            else
            {
                Messager::error('Dit bestand bestaat niet.', false, true);
                redirect('?do=update&id=' . intval($_GET['id']));
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