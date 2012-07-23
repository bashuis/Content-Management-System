<?php

function getParentList ($menuItem, $currentList = array())
{
    global $db;
    
    if ($menuItem['parent'] == 0)
            return $currentList;

    $query = $db->query("SELECT t.`name`,
                                mi.`mi_parent` AS parent
                        FROM    `cms_menuitem` AS mi
                        JOIN    `cms_menuitem_translation` AS t
                            ON  mi.`mi_id` = t.`mi_id`
                        WHERE   mi.`mi_id` = " . $menuItem['parent'] . "
                        AND     t.`lang_id` = 1
                        LIMIT 1");
    $parentItem = $db->fetch_assoc();

    $currentList[] = $parentItem['name'];
    $currentList = getParentList($parentItem, $currentList);

    return $currentList;
}

$output->addTitle("Bewerken");

if (isset($request[2]) && is_numeric($request[2]))
{
    $db->prepare("SELECT `p_id`,
                         `p_intrash`,
                         `locked`,
                         `locked_by`
                  FROM   `cms_page`
                  WHERE  `p_id` = :pid
                  LIMIT 1")
       ->bindValue('pid', $request[2])
       ->execute();
    if ($db->num_rows() == 1)
    {
        $page = $db->fetch_assoc();

        if (User::isAdmin() || (is_null($page['locked']) && is_null($page['locked_by']) || $page['locked_by'] == User::id() || (time() - $page['locked']) > 900))
        {
            $db->prepare("UPDATE `cms_page`
                          SET    `locked` = :locked,
                                 `locked_by` = :by
                          WHERE  `p_id` = :id
                          LIMIT 1")
               ->bindValue('locked', time())
               ->bindValue('by',     User::id())
               ->bindValue('id',     $page['p_id'])
               ->execute();
            
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

                        $db->prepare("UPDATE `cms_page_translation`
                                      SET    `name` = :name,
                                             `text` = :text
                                      WHERE  `p_id` = :pid
                                      AND    `lang_id` = :lid")
                           ->bindValue('name', $name)
                           ->bindValue('text', $text)
                           ->bindValue('pid',  $page['p_id'])
                           ->bindValue('lid',  $lang['id'])
                           ->execute();
                    }

                    Messager::ok('De pagina is succesvol opgeslagen.', false, true);
                    redirect('/beheer/page/list');
                }
                else
                {
                    Messager::error('U moet minimaal de <strong>naam</strong> invullen van de standaard taal.', false);
                }
            }

            $menuItems = $db->query("SELECT t.`name`,
                                            mi.`mi_parent` AS parent
                                     FROM   `cms_menuitem` AS mi
                                     JOIN   `cms_menuitem_translation` AS t
                                         ON mi.`mi_id` = t.`mi_id`
                                     WHERE mi.`mi_type` = 'page'
                                     AND   mi.`mi_item_id` = " . $page['p_id'] . "
                                     AND   t.`lang_id` = 1");

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

                <div class="normalrow">
                    <label class="required">Verbonden menu items:</label>
                    <?php
                    if ($db->num_rows() > 0)
                    {
                        echo '<ul style="margin-top: 3px;">';
                        while ($menuItem = $db->fetch_assoc($menuItems))
                        {
                            $trail = array_reverse( getParentList( $menuItem ) );
                            $trail = array_merge( $trail, array( $menuItem['name'] ) );
                            echo '<li>' . implode(" &rarr; ", $trail) . '</li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
                
                <div id="accordion">
                    <?php
                    foreach (Lang::getAll() as $lang)
                    {
                        $db->prepare("SELECT `name`,
                                             `text`
                                      FROM   `cms_page_translation`
                                      WHERE  `p_id` = :pid
                                      AND    `lang_id` = :lid
                                      LIMIT 1")
                           ->bindValue('pid', $page['p_id'])
                           ->bindValue('lid', $lang['id'])
                           ->execute();
                        $pagecontent = $db->fetch_assoc();
                        ?>
                        <div class="onlyinput" style="cursor: pointer; background-color: <?php echo (isset($errors['lang_' . $lang['id']])) ? '#FBC2C4' : '#EEEEEE'; ?>; padding: 10px; font-weight: bold;">
                            <img src="<?=$lang['flag']?>" alt="<?=$lang['flag']?>" /> <?=$lang['name']?>
                        </div>
                        <div>
                            <div class="normalrow">
                                <label class="required">Naam:</label>
                                <input type="text" name="name_<?php echo $lang['id']; ?>" value="<?=isset($_POST['name_' . $lang['id']]) ? $_POST['name_' . $lang['id']] : $pagecontent['name']?>" />
                            </div>
                            <div class="normalrow">
                                
                                <div>
                                <?php
                                    $fck = new FCKEditor('text_' . $lang['id']);
                                    $fck->Height = 400;
                                    $fck->Width = 600;
                                    $fck->ToolbarSet = 'Default_preview';

                                    if (isset($_POST['text_' . $lang['id']]))
                                        $fck->Value = $_POST['text_' . $lang['id']];
                                    else
                                        $fck->Value = $pagecontent['text'];

                                    //$fck->Create();
                                    
                                    
                                    
                                    require_once $_SERVER['DOCUMENT_ROOT'].'/cms/pub/ckfinder/ckfinder.php' ;
                                     // Create a class instance.
                                    $CKEditor = new CKEditor();

                                    // Path to the CKEditor directory.
                                    $CKEditor->basePath = '/cms/pub/ckeditor/';
                                    
                                    //$CKEditor->config['width'] = "90%";
                                    $CKEditor->config['height'] = 400;
                                    $CKEditor->config['skin'] = 'office2003';
                                    
                                    $CKEditor->config['filebrowserBrowseUrl'] = '/cms/pub/ckfinder/ckfinder.html';
                                    $CKEditor->config['filebrowserImageBrowseUrl'] = '/cms/pub/ckfinder/ckfinder.html?Type=Images';
                                    $CKEditor->config['filebrowserFlashBrowseUrl'] = '/cms/pub/ckfinder/ckfinder.html?Type=Flash';
                                    $CKEditor->config['filebrowserUploadUrl'] = '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
                                    $CKEditor->config['filebrowserImageUploadUrl'] = '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
                                    $CKEditor->config['filebrowserFlashUploadUrl'] = '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
                                    
                                    
                
                                    /*
                                     * 
                                      
        filebrowserBrowseUrl : '/ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl : '/cms/pub/ckfinder/ckfinder.html?Type=Images',
        filebrowserFlashBrowseUrl : '/cms/pub/ckfinder/ckfinder.html?Type=Flash',
        filebrowserUploadUrl : '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl : '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl : '/cms/pub/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
                                     
                                     * 
                                     * 
                                     * 
                                     */
                                   

                                    // Change default textarea attributes.
                                    $CKEditor->textareaAttributes = array("cols" => 80, "rows" => 40);

                                    if (isset($_POST['text_' . $lang['id']]))
                                        $CKEditor->editor('text_' . $lang['id'], $_POST['text_' . $lang['id']]);
                                    else
                                        $CKEditor->editor('text_' . $lang['id'], $pagecontent['text']);

                                ?>
                                 
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="onlyinput">
                    <input type="submit" name="submit" value="Opslaan" />
                </div>
            </form>
            <?php
        }
        else
        {
            Messager::error('Deze pagina wordt momenteel bewerkt door: ' . User::fullName($page['locked_by']), false, true);
            redirect('/beheer/page/list');
        }
    }
    else
    {
        Messager::error('Deze pagina bestaat niet (meer).', false, true);
        redirect('/beheer/page/list');
    }
}
else
{
    Messager::error('U heeft geen geldig id ingevuld.', false, true);
    redirect('/beheer/page/list');
}