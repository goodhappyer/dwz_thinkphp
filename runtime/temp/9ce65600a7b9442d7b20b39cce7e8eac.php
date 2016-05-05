<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"/webscripts/dwz_thinkphp/application/admin/view/admin_menu/index.html";i:1462488232;}*/ ?>
<div class="pageContent">
	<div class="panelBar">
		<ul class="toolBar">
			<?php foreach($menu as $v)
			{
			?>
			<li>
				<a class="<?php echo $v['action_name']; ?>" 
					href="/admin/<?php echo $v['module_name']; ?>/<?php echo $v['action_name']; ?><?php echo $v['data']; ?>" target="navTab">
					<span><?php echo $v['name']; ?></span></a>
				</li>
			<?php 
			}
			?>
			</ul>
	</div>
</div>
