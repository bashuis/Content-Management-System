<?php

if( empty( $request[2] ) )
{
	?><p class="warning">Je moet wel een groep kiezen om te bekijken!</p><?php
}
else
{
	$group = $db->query("
		SELECT
				`g_id` AS id,
				`g_name` AS name
		FROM
				`cms_group`
		WHERE
				`g_id` = ".intval($request[2])."
		LIMIT
				1
		;
	");
	
	if( $db->num_rows( $group ) == 1 )
	{
		$group = $db->fetch_assoc( $group );
		$users = $db->query("
			SELECT
					u.`u_id` AS id,
					u.`u_name` AS name,
					u.`u_active` AS active,
					u.`u_admin` AS admin
			FROM
					`cms_user` AS u
			JOIN
					`cms_user_group` AS ug
			ON
					u.`u_id` = ug.`u_id`
			WHERE
					ug.`g_id` = ".$group['id']."
			AND
					u.`u_verified` = 1
			ORDER BY
					`u_name`
		");
		
		if( $db->num_rows( $users ) == 0 )
		{
			//	Hello Huizinga employee with sneak-access to the management panel. :)
			?><p class="error">Er zijn geen gebruikers.</p><?php
		}
		else
		{
			?>
<h2>Leden van de groep <?php echo stripslashes( $group['name'] ); ?></h2>			
<table>
	<tr>
		<th>Naam:</th>
		<th>Eigenschappen:</th>
		<th>Opties:</th>
	</tr>
	<?php
	$classTicker = true;
	while( $user = $db->fetch_assoc( $users ) )
	{
		?>
	<tr class="<?php echo ($classTicker ? 'odd' : 'even'); $classTicker = !$classTicker; ?>">
		<td><?php echo stripslashes( $user['name'] ); ?></td>
		<td>
			<img src="/icons/silk/<?php echo ($user['active'] == 1 ? 'status_online' : 'status_busy' ); ?>.png" alt="<?php echo ($user['active'] == 1 ? 'Actief' : 'Inactief' ); ?>" />
			<?php
			if( user::isAdmin( $user['id'] ) )
			{
				?><img src="/icons/silk/star.png" alt="Beheerder" /><?php
			}
			else if( user::isInAdminGroup( $user['id'] ) )
			{
				?><img src="/icons/silk/star_gray.png" alt="Semi Beheerder" /><?php
			}
			?>
		<td>
			<a href="/beheer/users-and-groups/edit-user/<?php echo $user['id']; ?>/"><img src="/icons/silk/pencil.png" /></a>
			<a href="/beheer/users-and-groups/edit-user/<?php echo $user['id']; ?>/">Bewerken</a>
			
			<?php if( $user['id'] != user::id() )
			{
				?>
			<a href="/beheer/users-and-groups/delete-user/<?php echo $user['id']; ?>/?g=<?php echo $group['id']; ?>"><img src="/icons/silk/delete.png" /></a>
			<a href="/beheer/users-and-groups/delete-user/<?php echo $user['id']; ?>/?g=<?php echo $group['id']; ?>">Verwijderen</a>
				<?php
			}
			?>
			
			<a href="/beheer/users-and-groups/reset-password/<?php echo $user['id']; ?>/"><img src="/icons/silk/key.png" /></a>
			<a href="/beheer/users-and-groups/reset-password/<?php echo $user['id']; ?>/">Wachtwoord wijzigen</a>
		</td>
	</tr>	
		<?php
	}
	?>
	<tr class="nobackground">
		<td colspan="3">
			<strong>Eigenschappen legenda:</strong><br />
			<img src="/icons/silk/star.png" alt="Beheerder" /> - Deze gebruiker heeft volledige toegang tot het beheer paneel.<br />
			<img src="/icons/silk/star_gray.png" alt="Semi Beheerder" /> - Deze gebruiker heeft beperkte toegang tot het beheer paneel.<br />
			<img src="/icons/silk/status_online.png" alt="Actief" /> - Deze gebruiker is actief.<br />
			<img src="/icons/silk/status_busy.png" alt="Inactief" /> - Deze gebruiker is inactief. Inactieve gebruikers kunnen niet inloggen.
		</td>
	</tr>
</table>	
			<?php
		}
	}
	else
	{
		?><p class="warning">De groep waarvan je de leden wilt bekijken bestaat niet (meer).</p><?php
	}
}
?>