<div class="columns">
	<?php echo $verify_site_form_declaration ?>
		<div class="left">
			<h1 class="mainHeading">Verify Settings</h1>
			<p>On your right you will see the settings for the site(s) you are importing.</p>
			<p>You can modify the site name to give this site a custom label. The site name is only local; the name will not be sent to the remote site.</p>
			<div class="btn-bar">
				<a href="<?php echo $back_url ?>" class="submit back">Back</a>
				<input type="submit" class="submit" value="Continue" />
			</div>
		</div>


		<div class="right">

			<?php foreach($sites as $i => $site): ?>
			<table class="mainTable padTable " border="0" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th width="50%"><?php echo $site['site_name'] ?></th>
						<th>Setting</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Site Name</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][site_name]" value="<?php echo $site['site_name'] ?>"/></td>
					</tr>
					<tr>
						<td>Site ID</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][site_id]" value="<?php echo $site['site_id'] ?>"/></td>
					</tr>
					<tr>
						<td>Base URL</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][base_url]" value="<?php echo $site['base_url'] ?>"/></td>
					</tr>
					<tr>
						<td>Index Page</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][index_page]" value="<?php echo $site['index_page'] ?>"/></td>
					</tr>
					<tr>
						<td>CP URL</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][cp_url]" value="<?php echo $site['cp_url'] ?>"/></td>
					</tr>
					<tr>
						<td>API Action ID</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][action_id]" value="<?php echo $site['action_id'] ?>"/></td>
					</tr>
					<tr>
						<td>User ID</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][user_id]" value="<?php echo $site['user_id'] ?>"/></td>
					</tr>
					<tr>
						<td>Channel Nomenclature</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][channel_nomenclature]" value="<?php echo $site['channel_nomenclature'] ?>"/></td>
					</tr>
					<tr>
						<td>Public Key</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][public_key]" value="<?php echo $site['public_key'] ?>"/></td>
					</tr>
					<tr>
						<td>Private Key</td>
						<td><input type="text" class="input fullfield" name="sites[<?php echo $i ?>][private_key]" value="<?php echo $site['private_key'] ?>"/></td>
					</tr>
				</tbody>
			</table>
			<?php endforeach; ?>
		</div>
	</form>
</div>