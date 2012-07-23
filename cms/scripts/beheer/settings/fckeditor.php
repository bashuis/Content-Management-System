<h2>FCKeditor aanpassen</h2>

<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if (isset($_POST['customize']))
        {
            if (file_exists(TEMPLATES . '/fckeditor.js'))
                unlink(TEMPLATES . '/fckeditor.js');

            if ($_POST['customize'] == '1')
            {
                file_put_contents(TEMPLATES . '/fckeditor.js', $_POST['content']);
            }

            Messager::ok('De wijzigingen zijn opgeslagen.');
        }
        $db->query("UPDATE cms_settings SET `fck_max_image_size` = ".$_POST['fck_max_image_size'].";");
        $cms_settings = $db->fetch_assoc($db->query("SELECT * FROM cms_settings"));
    }

    if (file_exists(TEMPLATES . '/fckeditor.js'))
    {
        $exists     = true;
        $fckContent = file_get_contents(TEMPLATES . '/fckeditor.js');
    }
    else
    {
        $exists     = false;
        $fckContent = '';
    }
?>

<script type="text/javascript">
    $(document).ready(function() {
        if ($('input[name=customize]:checked').val() == 0) {
            $('#fckcontent').hide();
        }

        $('input[name=customize]').change(function() {
            if ($(this).val() == 1) {
                $('#fckcontent').slideDown();
            } else {
                $('#fckcontent').slideUp();
            }
        });
    });
</script>

<form action="" method="post">

    <div class="normalrow">
        <label class="required">Maximale afmeting foto's:</label>
        <input type="text" name="fck_max_image_size" value="<?php print $cms_settings['fck_max_image_size']; ?>" style="width:100px" />px <small>(0 = geen maximale afmeting)</small>
    </div>

    <div class="normalrow">
        <label class="required">Alternatieve configuratie:</label>
        <input type="radio" name="customize" value="1" <?php if ($exists) echo 'checked '; ?>/> Ja
        <input type="radio" name="customize" value="0" <?php if (!$exists) echo 'checked '; ?>/> Nee
    </div>
    <div class="normalrow" id="fckcontent">
        <label class="required">Inhoud configuratie:</label>
        <a href="/cms/sources/classes/fckeditor/fckconfig.js?iframe=true&width=800&height=600" rel="prettyPhoto">Bekijk hier het orginele configuratie bestand</a>
        <textarea rows="10" cols="50" name="content" style="width: 80%; min-height: 400px;"><?=$fckContent?></textarea>        
    </div>
    <div class="onlyinput">
        <input type="submit" value="Opslaan" />
    </div>
</form>