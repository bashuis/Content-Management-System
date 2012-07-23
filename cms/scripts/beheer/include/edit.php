<?php

$output->addTitle("Menu");
$output->addTitle("PHP Include aanpassen");

if (isset($request[2]) && is_numeric($request[2]))
{	
    $id = intval($request[2]);

    $db->query("SELECT `inc_file` AS file
		FROM   `cms_include`
		WHERE  `inc_id` = " . $id . "
		LIMIT 1");

    if ($db->num_rows() == 1)
    {
        $include = $db->fetch_assoc();

	if (isset($_POST['submit']))
	{
            if (!isset($_POST['file']) || empty($_POST['file']))
            {
                Messager::error('U heeft geen bestandslocatie ingevuld.');
            }
            else if (!file_exists($_POST['file']))
            {
                Messager::error('De door u ingevulde bestandslocatie bestaat niet.');
            }
            else
            {
                $db->prepare("UPDATE `cms_include`
                              SET    `inc_file` = :file
                              WHERE  `inc_id` = :id
                              LIMIT 1")
                   ->bindValue('file', $_POST['submit'])
                   ->bindValue('id', $idd)
                   ->execute();

                Messager::ok('De include pagina is succesvol opgeslagen.', false, true);
                redirect('/beheer/menu/list');
            }
	}        
        ?>

        <form action="" method="post">
            <div class="form">
                <div class="normalrow">
                    <label class="required" for="file">Bestand:</label>
                    <input type="text" name="file" id="file" value="<?php echo $include['file']; ?>" />
                </div>
                <div class="onlyinput">
                    <input type="submit" name="submit" value="Bewerk deze include" />
                </div>
            </div>
        </form>
        <?php
    }
    else
    {
        Messager::error('Deze include bestaat niet (meer).', false, true);
        redirect('/beheer/menu/list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingevuld.', false, true);
    redirect('/beheer/menu/list');
}