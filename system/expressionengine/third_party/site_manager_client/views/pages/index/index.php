<div class="btn-bar">
	<a class="submit" href="<?= $add_url ?>">Add Site</a>
</div>
<?php foreach ($sites as $site) : ?>
	<div class="sm-site">
		<h3><?= $site->name() ?></h3>
	</div>
<?php endforeach; ?>