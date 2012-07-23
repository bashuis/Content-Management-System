<?php require SOURCES.'form.php'; ?>
<h2>Zoek een gebruiker</h2>
<p>Voer een naam of email adres of beiden in om op te zoeken.</p>
<form action="./" method="post">
<div class="form">
	<div class="normalrow">
		<label class="required" for="name">Naam:</label>
		<input type="text" name="name" id="name"<?php Form::doPrevious($_POST,'name'); ?> />
	</div>
	<div class="normalrow">
		<label class="required" for="email">Email:</label>
		<input type="text" name="email" id="email"<?php Form::doPrevious($_POST,'email'); ?> />
	</div>
	<div class="onlyinput">
		<input type="submit" name="submit" value="Zoeken">
	</div>
</div>
</form>
<small>Bij het zoeken word eerst gekeken of er gebruikers zijn die aan alle eisen voldoen. Mochten die er niet zijn, dan zal worden gekeken of er gebruikers zijn die aan een deel van de eisen voedoen.</small>
<?php

$checks = array();
if( ! empty( $_POST['name'] ) )
{
	$name = trim( $_POST['name'] );
	if( ! empty( $name ) )
	{
		$checks[] = "`u_name` LIKE '%".mysql_escape_string( $name )."%'";
	}
}
if( ! empty( $_POST['email'] ) )
{
	$email = trim( $_POST['email'] );
	if( ! empty( $email ) )
	{
		$checks[] = "`u_email` LIKE '%".mysql_escape_string( $email )."%'";
	}
}

if( sizeof( $checks ) > 0 )
{
	?>
<h3>Resultaten:</h3>	
	<?php
	function showResults( $matches )
	{
            global $db;
		?>
<table>
	<tr>
		<th>Naam:</th>
		<th>Email adres:</th>
		<th>Eigenschappen:</th>
		<th>Opties:</th>
	</tr>
	<?php
	$classTicker = true;
	while( $user = $db->fetch_assoc( $matches ) )
	{
		?>
	<tr class="<?php echo ($classTicker ? 'odd' : 'even'); $classTicker = !$classTicker; ?>">
		<td><?php echo stripslashes( $user['name'] ); ?></td>
		<td><?php echo stripslashes( $user['email'] ); ?></td>
		<td>
			<img src="/icons/fugues/icons/<?php echo ($user['active'] == 1 ? 'status' : 'status-busy' ); ?>.png" alt="<?php echo ($user['active'] == 1 ? 'Actief' : 'Inactief' ); ?>" />
			<?php
			if( user::isAdmin( $user['id'] ) )
			{
				?><img src="/icons/fugues/icons/star.png" alt="Beheerder" /><?php
			}
			else if( user::isInAdminGroup( $user['id'] ) )
			{
				?><img src="/icons/fugues/icons/star-half.png" alt="Semi Beheerder" /><?php
			}
			?>
		</td>
		<td>
			<a href="/beheer/users-and-groups/edit-user/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerk gebruiker" /></a>
			<a href="/beheer/users-and-groups/edit-user/<?php echo $user['id']; ?>/">Bewerken</a>
			
			<?php if( $user['id'] != user::id() )
			{
				?>
			<a href="/beheer/users-and-groups/delete-user/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijder gebruiker" /></a>
			<a href="/beheer/users-and-groups/delete-user/<?php echo $user['id']; ?>/">Verwijderen</a>
				<?php
			}
			?>
			
			<a href="/beheer/users-and-groups/reset-password/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/key.png" style="vertical-align: bottom;" alt="Wachtwoord herstellen" /></a>
			<a href="/beheer/users-and-groups/reset-password/<?php echo $user['id']; ?>/">Wachtwoord wijzigen</a>
		</td>
	</tr>	
		<?php
	}
	?>
	<tr class="nobackground">
		<td colspan="4">
			<strong>Eigenschappen legenda:</strong><br />
            <img src="/icons/fugues/icons/star.png" style="vertical-align: bottom;" alt="Beheerder" /> - Deze gebruiker heeft volledige toegang tot het beheer paneel.<br />
            <img src="/icons/fugues/icons/star-half.png" style="vertical-align: bottom;" alt="Semi Beheerder" /> - Deze gebruiker heeft beperkte toegang tot het beheer paneel.<br />
            <img src="/icons/fugues/icons/status.png" style="vertical-align: bottom;" alt="Actief" /> - Deze gebruiker is actief.<br />
            <img src="/icons/fugues/icons/status-busy.png" style="vertical-align: bottom;" alt="Inactief" /> - Deze gebruiker is inactief. Inactieve gebruikers kunnen niet inloggen.
		</td>
	</tr>
</table>	
		<?php		
	}

	$matches = $db->query("
		SELECT
				`u_id` AS id,
				`u_name` AS name,
				`u_email` AS email,
				`u_active` AS active,
				`u_admin` AS admin
		FROM
				`cms_user`
		WHERE
				".implode( ' AND ', $checks )."
		AND
				`u_verified` = 1
                AND             `u_id` <> -1
                AND             `u_id` <> 0
		ORDER BY
				`u_name`
		;
	");
	
	if( $db->num_rows( $matches ) == 0 )
	{
		if( sizeof( $checks ) == 1 )
		{
            Messager::notify('Helaas, er zijn geen gebruikers die voldoen deze zoekopdracht.');
		}
		else
		{
			$matches = $db->query("
				SELECT
						`u_id` AS id,
						`u_name` AS name,
						`u_email` AS email,
						`u_active` AS active,
						`u_admin` AS admin
				FROM
						`cms_user`
				WHERE
						".implode( ' OR ', $checks )."
                                                AND    `u_id` <> -1
                                                AND    `u_id` <> 0
				ORDER BY
						`u_name`
				;
			");
			if( $db->num_rows( $matches ) == 0 )
			{
                Messager::notify('Helaas, er zijn geen gebruikers die voldoen deze eisen set, of aan &eacute;&eacute;n van de eisen.', false);
			}
			else
			{
				if( $db->num_rows( $matches ) == 1 )
				{
                    Messager::notify('Helaas, er zijn geen gebruikers die voldoen deze eisen set. Wel voldoet &eacute;&eacute;n gebruiker aan een deel van de eisen.', false);
				}
				else
				{
                    Messager::notify('Helaas, er zijn geen gebruikers die voldoen deze eisen set. Wel zijn er ' . $db->num_rows( $matches ) . ' gebruikers die voldoen aan een deel van de eisen:', false);
				}
				showResults( $matches );
			}
		}
	}
	else
	{
		if( $db->num_rows( $matches ) == 1 )
		{
            Messager::notify('Er is &eacute;&eacute;n gebruiker die voldoet aan de eisen:', false);
		}
		else
		{
            Messager::notify('Er zijn ' . $db->num_rows( $matches ) . ' gebruikers die voldoen aan de eisen:');
		}
		showResults( $matches );
	}
}

?>