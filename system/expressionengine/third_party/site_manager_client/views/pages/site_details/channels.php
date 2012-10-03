
<div class="columns nav">
	<div class="left">
		<ul>
		<?php foreach ($navigation as $nav) : ?>
			<li class="<?php if($nav['active']) echo "active"; ?>"><a href="<?php echo $nav['url'] ?>"><?php echo $nav['label'] ?></a></li>
		<?php endforeach; ?>
		</ul>
		<ul class="bottom">
			<li><a href="<?php echo $delete_url ?>" onclick="return confirm('Are you sure you want to delete this site?');">Delete Site</a></li>
		</ul>
	</div>
	<div class="right">
		<div class="site-details">
			<h1 class="mainHeading"><?php echo $site->name() ?></h1>
			<div class="statusBlock">
				<span class="status live"></span>
				<span class="status connecting active"></span>
				<span class="status offline"></span>
			</div>
	
			<p class="meta"><a href="<?php echo $site->base_url() ?>" target="_blank"><?php echo $site->base_url() ?></a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?php echo $site->cp_url() ?>" target="_blank">Control Panel</a> &nbsp; &nbsp; &nbsp; &nbsp; EE: <span class="app_version">-</span></p>

				<div class="dynamicDataWrapper">
				<dl id="channelTarget" class="dynamicData"></dl>
				</div>

				
				<div class="channelTemplate channel">
					<dt class="channel_title"></dt>
					
					<dd>
						<table class="mainTable padTable statTable" border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<th width="25%">Channel ID</th>
									<td width="25%" class="channel_id">a</td>
									<th width="25%">Total Entries</th>
									<td width="25%" class="total_entries">a</td>
								</tr>
								
								<tr>
									<th>Channel Name</th>
									<td class="channel_name">a</td>
									<th>Total Comments</th>
									<td class="total_comments">a</td>
								</tr>
							</tbody>
						</table>


						<table class="mainTable padTable fieldTable" border="0" cellpadding="0" cellspacing="0">
							<thead>
								<tr>
									<th width="50%">Field Label</th>
									<th width="25%">Short Name</th>
									<th width="25%">Type</th>
								</tr>
							</thead>
							<tbody>
							
							</tbody>
						</table>
					</dd>
				</div>





		</div>
	</div>
</div>


<script type="text/javascript">
	window.SM = {};
	window.SM.js_api = "<?php echo $js_api ?>";
	window.SM.site_id = <?php echo $site->id() ?>;

	define('site_config', [], function() {
		return <?php echo $site->js_config() ?>; 
	});
</script>