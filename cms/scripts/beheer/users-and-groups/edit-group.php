<?php

// Empty array for errors.
$errors = array();
$input = array();

$groupId = intval($request[2]);

// Deal with updates
if (isset($_POST['submit']))
{
    //	Check the name
    if (!empty($_POST['name']))
    {
        $db->prepare("SELECT 1
                     FROM   `cms_group`
                     WHERE  `g_name` = :name
                     AND    `g_id` <> :gip
                     LIMIT 1")
           ->bindValue('name', $_POST['name'])
           ->bindValue('gip', $groupId)
           ->execute();
        if ($db->num_rows() == 1)
        {
            Messager::error('Helaas, er is al een groep met de naam &quot;' . htmlentities($_POST['name']) . '&quot;, je zult een andere naam moeten kiezen.', false);
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
        Messager::error('Je moet de naam van de groep wel invullen!');
        $errors['name'] = true;
    }
	
    $input['admin'] = isset($_POST['admin']);
	
	
    //	Everything is A-OK.
    //	Lets update the group.
    if (sizeof($errors) == 0)
    {
        $db->query("UPDATE `cms_group`
                    SET    `g_name` = '" . mysql_escape_string($input['name']) . "',
                           `g_admin` = " . ($input['admin'] ? 1 : 0) . "
                    WHERE `g_id` = " . $groupId . "
					AND   `g_id` <> 1
					AND   `g_id` <> 200
                    LIMIT 1");
        Messager::ok('De wijzigingen zijn opgeslagen.', false, true);
        redirect('/beheer/users-and-groups/list/');
    }
    else
    {
        Messager::warning('<strong>Let op:</strong> Er is een fout opgetreden bij het wijzigen van de groep, er zijn <strong>geen wijzigingen opgeslagen</strong>!', false);
    }
}

$group = $db->query("SELECT `g_name` AS name,
			    `g_admin` AS admin
                     FROM   `cms_group`
                     WHERE  `g_id` = " . $groupId . "
                     LIMIT 1");

if ($db->num_rows($group) == 1)
{
    $group = $db->fetch_assoc($group);

    //	Override DB stuff with input stuff.
    //	Odd, you think, as we just wrote the input to the DB?
    //	Well, if there was an error, we didn't do the write.
    $group = array_merge($group, $input);
    showNewGroupForm($group, $errors, 'Sla de bewerkingen op');
}
else
{
    Messager::error('De groep die je wilt bewerken bestaat niet (meer).');
}
?>