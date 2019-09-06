<?php
# MetInfo Enterprise Content Management System
# Copyright (C) MetInfo Co.,Ltd (http://www.metinfo.cn). All rights reserved.
defined('IN_MET') or exit('No permission');
if($data['handle']){
	$data=array_merge($data,$data['handle']);
	unset($data['handle']);
}
$foot_save_no=1;
$table_ajaxurl=$url['own_name'].'c='.$data['module'].'_manage&a=dojson_list';
$table_order=$data['module'].'-list-'.$data['class1'].'-'.$data['class2'].'-'.$data['class3'];
?>
<include file="pub/content_list/form_head"/>
				<include file="pub/content_list/checkall_all"/>
				<th data-table-columnclass="text-center">
					{$word.cvPosition}<!-- <br>
					<select name="position" data-table-search class="form-control">
						<option value="">{$word.please_choose}</option>
					</select> -->
				</th>
				<th data-table-columnclass="text-center" width="80">
					<select name="search_type" data-table-search class="form-control">
						<option value="0">{$word.smstips64}</option>
						<option value="1">{$word.unread}</option>
						<option value="2">{$word.read}</option>
					</select>
				</th>
				<list data="$data['showcol']" name="$v">
				<th>
				{$v.name}
				<if value="in_array($v['type'], array(2,6))">
				<select name="para_{$v.id}" data-table-search class="form-control d-inline-block w-a">
					<list data="$v['options']['list']" name="$s">
					<option value="{$s.val}">{$s.name}</option>
					</list>
				</select>
				</if>
				</th>
				</list>
				<th data-table-columnclass="text-center" width="100">{$word.cvAddtime}</th>
				<th width="120">{$word.operate}</th>
			</tr>
		</thead>
		<tbody>
			<?php $colspan=5+count($data['showcol']); ?>
			<include file="pub/content_list/table_loader"/>
		</tbody>
		<input type="hidden" name="class1" value="{$data.class1}" data-table-search="#{$table_order}">
		<?php $colspan--; ?>
		<include file="pub/content_list/tfoot_common"/>
				</th>
			</tr>
		</tfoot>
	</table>
	<input type="hidden" name="class1" value="{$data.class1}" data-table-search="#{$table_order}">
	<input type="hidden" name="class2" value="{$data.class2}" data-table-search="#{$table_order}">
	<input type="hidden" name="class3" value="{$data.class3}" data-table-search="#{$table_order}">
	<input type="hidden" name="jobid" value="{$data.jobid}" data-table-search="#{$table_order}">
</form>