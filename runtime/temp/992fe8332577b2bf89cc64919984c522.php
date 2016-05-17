<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:67:"/webscripts/dwz_thinkphp/application/admin/view/admin_menu/add.html";i:1462660226;}*/ ?>
<div class="pageContent">
	<form method="post" action="/Admin/Admin_menu/add" class="pageForm required-validate" onsubmit="return validateCallback(this, navTabAjaxDone);">
		<div class="pageFormContent" layoutH="56">
			<p>
				<label>菜单名称：</label>
				<input name="name" class="required" type="text" size="30" value="" alt="请输入菜单名称"/>
			</p>

			<p>
				<label>模块：</label>
				<input name="module_name" class="required" type="text" size="30" value="" alt="请输入模块"/>
			</p>

			<p>
				<label>方法：</label>
				<input name="action_name" class="required" type="text" size="30" value="" alt="方法"/>
			</p>

			<p>
				<label>其它参数：</label>
				<input name="data" class="" type="text" size="30" value="" alt="其它参数"/>
			</p>

			<p>
				<label>备注：</label>
				<input name="remark" class="required" type="text" size="30" value="" alt="请输入备注"/>
			</p>
			<p>
				<label>菜单类型：</label>
				<select name="menu_type" class="required combox">
					<option value="0" selectd>菜单分类</option>
					<option value="1">菜单</option>
					<option value="2">栏目功能</option>
					<option value="4">功能</option>
				</select>

			</p>
			<p>
				<label>所在节点：</label>
				<input type="hidden" name="pid" id="pid" value="0">
				<input type="hidden" name="MenuLookup.menu_id" id="menu_id" value="${MenuLookup.menu_id}"/>
				<input type="text" name="MenuLookup.menu_name" value="不选是根节点" suggestFields="menu_name,menu_id" suggestUrl="/Admin/admin_menu/Lookup" lookupGroup="MenuLookup" />

				<a class="btnLook" href="/Admin/admin_menu/Lookup" lookupGroup="MenuLookup" callback="MenuLookup_callback" >查找带回</a>		
			</p>

		</div>
		<div class="formBar">
			<ul>
				<li>
					<div class="buttonActive">
						<div class="buttonContent">
							<button type="submit">保存</button>
						</div>
					</div>
				</li>
				<li>
					<div class="button">
						<div class="buttonContent">
							<button type="button" class="close">取消</button>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</form>
</div>
<script language="javascript">
function MenuLookup_callback()
{
	jQuery("#pid").val(jQuery("#menu_id").val());
}
</script>
