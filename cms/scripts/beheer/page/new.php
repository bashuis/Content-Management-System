<?php

$output->addTitle("Toevoegen");

$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $errors   = array();
    $continue = false;

    foreach (Lang::getAll() as $lang)
    {
        if ($lang['id'] == LANG)
        {
            if (isset($_POST['name_'. $lang['id']]) && !empty($_POST['name_'. $lang['id']]))
                $continue = true;
        }

        if (empty($_POST['name_' . $lang['id']]))
        {
            $errors['lang_' . $lang['id']] = true;
        }
    }

    if ($continue)
    {
        $db->query("INSERT INTO `cms_page` (`p_intrash`)
                    VALUES (0)");
        $pid = $db->insert_id();
        makePublic($pid, 'page');

        foreach (Lang::getAll() as $lang)
        {
            if (empty($_POST['name_' . $lang['id']]))
            {
                $name = $_POST['name_' . LANG];
                $text = $_POST['text_' . LANG];

                Messager::notify('De taal <strong>' . $lang['name'] . '</strong> heeft de inhoud gekregen van standaard taal omdat deze niet ingevuld was.', false, true);
            }
            else
            {
                $name = $_POST['name_' . $lang['id']];
                $text = $_POST['text_' . $lang['id']];
            }

            $db->prepare("INSERT INTO `cms_page_translation` (`p_id`,
                                                              `lang_id`,
                                                              `name`,
                                                              `text`)
                          VALUES (:pid,
                                  :lid,
                                  :name,
                                  :text)")
               ->bindValue('pid',  $pid)
               ->bindValue('lid',  $lang['id'])
               ->bindValue('name', $name)
               ->bindValue('text', $text)
               ->execute();
        }

        if (isset($_GET['mid']) && is_numeric($_GET['mid']))
        {
            $db->query("UPDATE `cms_menuitem`
                        SET `mi_item_id` = " . $pid . ",
                            `mi_active` = 1
                        WHERE `mi_id` = " . intval($_GET['mid']) . "
                        LIMIT 1");

            Messager::ok('De pagina is aangemaakt en de menuknop toegevoegd. Je word nu terug gebracht naar het overzicht van menu knoppen.', false, true);
            redirect('/beheer/menu/list');
        }
        else
        {
            Messager::ok('De pagina is succesvol toegevoegd.', false, true);
            redirect('/beheer/page/edit/' . $pid);
        }                
    }
    else
    {
        Messager::error('U moet minimaal de <strong>naam</strong> invullen van de standaard taal.', false);
    }
}

?>
<form action="" method="post">
    <script type="text/javascript">
        $(function() {
            $( "#accordion" ).accordion({
                header: 'div.onlyinput',
                icons: false,
                event: "click hoverintent"
            });
        });
    </script>

    <div id="accordion">
        <?php
        foreach (Lang::getAll() as $lang)
        {
            ?>
            <div class="onlyinput" style="cursor: pointer; background-color: <?php echo (isset($errors['lang_' . $lang['id']])) ? '#FBC2C4' : '#EEEEEE'; ?>; padding: 10px; font-weight: bold;">
                <img src="<?=$lang['flag']?>" alt="<?=$lang['flag']?>" /> <?=$lang['name']?>
            </div>
            <div>
                <div class="normalrow">
                    <label class="required">Naam:</label>
                    <input type="text" name="name_<?php echo $lang['id']; ?>" value="<?=isset($_POST['name_' . $lang['id']]) ? $_POST['name_' . $lang['id']] : ''?>" />
                </div>
                <div class="normalrow">
                    <label>Tekst:</label>
                    <div>
                    <?php
                        $fck = new FCKEditor('text_' . $lang['id']);
                        $fck->Height = 400;
                        $fck->Width = 600;
                        $fck->ToolbarSet = 'Default_preview';

                        if (isset($_POST['text_' . $lang['id']]))
                            $fck->Value = $_POST['text_' . $lang['id']];
                        
                        $fck->Create();
                    ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="onlyinput">
        <input type="submit" name="submit" value="Voeg deze pagina toe" />
    </div>
</form>