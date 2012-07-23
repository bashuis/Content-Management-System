<?php
$output->addTitle('Gebruikers & Groepen');

ob_start();
?>
    <li<?php doActive('list',1); doActive('start',1); ?>>
            <a href="/beheer/users-and-groups/list/"><img src="/icons/fugues/icons/application-blue.png" alt="Overzicht-icon" style="vertical-align: bottom;" /> Overzicht weergeven</a>
    </li>
    <li<?php doActive('new-user',1); ?>>
            <a href="/beheer/users-and-groups/new-user/"><img src="/icons/fugues/icons/plus-circle.png" alt="Nieuwe gebruiker-icon" style="vertical-align: bottom;" /> Nieuwe gebruiker</a>
    </li>
    <li<?php doActive('new-group',1); ?>>
            <a href="/beheer/users-and-groups/new-group/"><img src="/icons/fugues/icons/plus-circle.png" alt="Nieuwe gebruiker-icon" style="vertical-align: bottom;" /> Nieuwe groep</a>
    </li>
    <li<?php doActive('search-user',1); ?>>
            <a href="/beheer/users-and-groups/search-user/"<img src="/icons/fugues/icons/magnifier-left.png" alt="Zoek een gebruiker-icon" style="vertical-align: bottom;" /> Zoek een gebruiker</a>
    </li>
<?php

$quickMenuContent = ob_get_clean();
QuickMenu::add($quickMenuContent);

// Delete user
function deleteUser ($id)
{
    global $db;

    $db->query("DELETE FROM `cms_user`
                WHERE `u_id` = " . intval($id) . "
                LIMIT 1");

    $db->query("DELETE FROM `cms_user_group`
                WHERE `u_id` = " . intval($id));
}

// Create/Edit user form
function showNewUserForm ($input = array('active' => true, 'admin' => false, 'groups' => array(1)) , $errors = array(), $submitButtonText = 'Submit')
{
    global $db;
    ?>
    <form action="" method="post">
        <div class="form">
            <div class="normalrow">
                <label class="required" for="name">Gebruikersnaam:</label>
                <input type="text" name="name" id="name"<?php Form::doPrevious($input, 'name'); ?> />
            </div>
            <div class="normalrow">
                <label class="required" for="name">Volledige naam:</label>
                <input type="text" name="fullname" id="fullname"<?php Form::doPrevious($input, 'fullname'); ?> />
            </div>
            <div class="normalrow">
                <label class="required" for="email">Email adres:</label>
                <input type="text" name="email" id="email"<?php Form::doPrevious($input,'email'); ?> />
                <img src="/icons/fugues/icons/information.png" style="cursor:pointer" class="information" alt="Het wachtwoord van de gebruiker wordt naar dit adres gestuurd." onclick="alert('&quot;Het wachtwoord van de gebruiker wordt naar dit adres gestuurd.&quot;')" />
            </div>
            <div class="normalrow">
                <label for="active">Actief?</label>
                <input type="checkbox" name="active" id="active"<?php if($input['active']) echo ' checked="checked"'; ?> />
            </div>
            <div class="normalrow">
                <label for="admin">Beheerder?</label>
                <input type="checkbox" name="admin" id="admin"<?php if($input['admin']) echo ' checked="checked"'; ?> />
            </div>
            <div class="normalrow">
                <label class="required">Groep(en)</label>
                <div style="margin-left: 200px;">
                    <?php
                    $groups = $db->query("SELECT `g_id` AS id,
                                                 `g_name` AS name,
                                                 `g_admin` AS admin
                                          FROM   `cms_group`
                                          ORDER BY `g_name`");
                    while ($group = $db->fetch_assoc($groups))
                    {
                        $gid = 'g_' . $group['id'];
                        ?>
                        <div class="normalrow">
                            <label for="<?php echo $gid; ?>">
                                    <?php echo $group['name']; ?>
                                    <?php if($group['admin']): ?>
                                        <img src="/icons/fugues/icons/key.png" style="cursor: pointer;" alt="Sleutel" title="Deze groep heeft toegang tot delen van het beheerders paneel" onclick="alert('&quot;Leden van deze groep hebben beperkte toegang tot het beheer paneel.&quot;')" />
                                    <?php endif; ?>
                            </label>
                            <input type="checkbox" name="<?php echo $gid; ?>" id="<?php echo $gid; ?>"<?php if(in_array($group['id'], $input['groups'])): echo ' checked="checked"'; endif; if($group['id'] == 1): echo ' disabled="disabled"'; endif; ?> >
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="onlyinput">
                <input type="submit" name="submit" value="<?php echo $submitButtonText; ?>">
            </div>
        </div>
    </form>
    <?php
}


function showNewGroupForm( $input = array(), $errors = array(), $submitButtonText = 'Submit' )
{
    ?>
    <form action="./" method="post">
        <div class="form">
            <div class="normalrow">
                <label class="required" for="name">Naam:</label>
                <input type="text" name="name" id="name"<?php Form::doPrevious($input, 'name'); ?> />
            </div>

            <div class="normalrow">
                <label for="admin">Beheerder groep</label>
                <input type="checkbox" name="admin" id="admin"<?php if($input['admin']) echo ' checked="checked"'; ?> />
            </div>

            <div class="onlyinput">
                <input type="submit" name="submit" value="<?php echo $submitButtonText; ?>">
            </div>
        </div>
    </form>
    <?php
}