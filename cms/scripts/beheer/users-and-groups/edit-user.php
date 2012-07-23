<?php

// Empty array for errors.
$errors = array();
$input = array();

$userId = intval($request[2]);

// Deal with updates
if (isset($_POST['submit']))
{
    //	Check the name
    if (!empty($_POST['name']))
    {
        $db->prepare("SELECT 1
                     FROM   `cms_user`
                     WHERE  `u_name` = :name
                     AND    `u_id` <> " . $userId . "
                     LIMIT 1")
           ->bindValue('name', $_POST['name'])
           ->execute();

        if ($db->num_rows() == 1 || Security::checkSetting("check-username&usr=" . $_POST['name']) == '0')
        {
            Messager::error('Helaas, er is al een gebruiker met de naam &quot;' . htmlentities($_POST['name']) . '&quot;, je zult een andere naam moeten kiezen.', false);
            $errors['name'] = true;
            $input['name'] = $_POST['name'];
        }
        else
        {
            $input['name'] = $_POST['name'];
        }
    }
    else
    {
        Messager::error('U heeft geen gebruikersnaam ingevuld.', false);
        $errors['name'] = true;
    }

    //	Check the fullname
    if (!empty($_POST['fullname']))
    {
        $db->prepare("SELECT 1
                     FROM   `cms_user`
                     WHERE  `u_fullname` = :fullname
                     AND    `u_id` <> " . $userId . "
                     LIMIT 1")
           ->bindValue('fullname', $_POST['fullname'])
           ->execute();

        if($db->num_rows() == 1 )
        {
            Messager::error('Helaas, er is al een gebruiker met de volledige naam &quot;' . htmlentities($_POST['fullname']) . '&quot;, je zult een andere volledige naam moeten kiezen.', false);
            $errors['fullname'] = true;
            $input['fullname'] = $_POST['fullname'];
        }
        else
        {
            $input['fullname'] = $_POST['fullname'];
        }
    }
    else
    {
        Messager::error('U heeft geen volledige naam ingevuld.', false);
        $errors['fullname'] = true;
    }

    //	Check the email address
    if (!empty($_POST['email']))
    {
        if (!Security::validateEmailAddress($_POST['email']))
        {
            Messager::error('U heeft geen geldig e-mailadres ingevuld.', false);
            $errors['email'] = true;
            $input['email'] = $_POST['email'];
        }
        else
        {
            $input['email'] = $_POST['email'];
        }
    }
    else
    {
        Messager::error('U heeft geen e-mailadres ingevuld.', false);
        $errors['email'] = true;
    }
	
    //	Everybody is in group 1 "Iedereen"
    $_POST['g_1'] = true;

    //	Check if there is a group to put the user in
    $groupWasSelected = false;
    $input['groups'] = array();

    $groups = $db->query("SELECT `g_id` AS id
                          FROM `cms_group`");

    while ($group = $db->fetch_assoc($groups))
    {
        if (isset($_POST['g_'.$group['id']]))
        {
            $groupWasSelected = true;
            $input['groups'][] = $group['id'];
        }
    }
	
    if (!$groupWasSelected)
    {
        Messager::error('Iedere gebruiker moet lid zijn van minimaal 1 groep.', false);
        $errors['group'] = true;
    }
	
    $input['active'] = isset($_POST['active']);
    $input['admin'] = isset($_POST['admin']);
	
    if( sizeof( $errors ) == 0 )
    {
        // Update the user.
        $db->prepare("UPDATE `cms_user`
                      SET `u_name` = :name,
                          `u_fullname` = :fullname,
                          `u_email` = :email,
                          `u_active` = :active,
                          `u_admin` = :admin
                      WHERE `u_id` = :id
                      LIMIT 1")
           ->bindValue('name',     $input['name'])
           ->bindValue('fullname', $input['fullname'])
           ->bindValue('email',    $input['email'])
           ->bindValue('active',   ($input['active'] ? 1 : 0))
           ->bindValue('admin',    ($input['admin'] ? 1 : 0))
           ->bindValue('id',       $userId)
           ->execute();
		

        $db->query("DELETE FROM `cms_user_group`
                    WHERE `u_id` = " . intval($userId));

		
        foreach ($input['groups'] as $group)
        {
            $db->query("INSERT INTO `cms_user_group` (`u_id`,
                                                      `g_id`)
                        VALUES (" . intval($userId) . ",
                                " . intval($group) . ")");
        }

        Messager::ok('De wijzigingen zijn opgeslagen.', false, true);
        redirect('/beheer/users-and-groups/list/');
    }
    else
    {
        Messager::warning('<strong>Let op:</strong> Er is een fout opgetreden bij het wijzigen van de gebruiker, er zijn <strong>geen wijzigingen opgeslagen</strong>!', false);
    }
}

// Grab the info and show it.
$user = $db->query("SELECT `u_id` AS id,
                           `u_name` AS name,
                           `u_fullname` AS fullname,
                           `u_email` AS email,
                           `u_active` AS active,
                           `u_admin` AS admin
                    FROM   `cms_user`
                    WHERE  `u_id` = ".$userId."
                    LIMIT 1");
if ($db->num_rows($user) == 1)
{
    $user = $db->fetch_assoc($user);
    $groups = $db->query("SELECT `g_id` AS id
                          FROM   `cms_user_group`
                          WHERE  `u_id` = " . intval($user['id']));

    //	Override DB stuff with input stuff.
    //	Odd, you think, as we just wrote the input to the DB?
    //	Well, if there was an error, we didn't do the write.
    $user = array_merge($user, $input);
    $user['groups'] = array();

    while ($group = $db->fetch_assoc($groups))
    {
        $user['groups'][] = $group['id'];
    }
    
    showNewUserForm($user, $errors, 'Sla de bewerkingen op');
}
else
{
    Messager::error('De gebruiker die je wilt bewerken bestaat niet (meer).');
}