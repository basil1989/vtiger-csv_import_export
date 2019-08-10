{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}
<style>
#csvtable tr{
	cursor: pointer;
}

#csvtable .tbl-selected{
	background-color: cadetblue !important;
}
</style>
<table width="100%" cellspacing="0" cellpadding="10" class="importContents">
	<tr>
		<td>
			<strong>{'CSV_LBL_IMPORT_STEP_4'|@vtranslate:$MODULE}:</strong>
		</td>
		<td>
			<span class="big">{'CSV_LBL_IMPORT_STEP_4_DESCRIPTION'|@vtranslate:$MODULE}</span>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
        <td colspan="2">
			<input type="hidden" name="field_mapping" id="field_mapping" value="" />
			<input type="hidden" name="default_values" id="default_values" value="" />
			<input type="hidden" name="cur_selected" id="cur_selected" value="1" />
			<table id="csvtable" width="100%" cellspacing="0" cellpadding="2" class="listRow table table-bordered table-condensed listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						{if $HAS_HEADER eq true}
						<th width="25%"><a>{'CSV_LBL_FILE_COLUMN_HEADER'|@vtranslate:$MODULE}</a></th>
						{/if}
						<th width="25%"><a>{'CSV_LBL_ROW_1'|@vtranslate:$MODULE}</a></th>
					</tr>
				</thead>
				<tbody>
					{foreach key=_HEADER_NAME item=_FIELD_VALUE from=$ROW_1_DATA name="headerIterator"}
					{assign var="_COUNTER" value=$smarty.foreach.headerIterator.iteration}
					<tr class="fieldIdentifier {if $_COUNTER eq 1}tbl-selected{/if}" id="fieldIdentifier{$_COUNTER}">
						{if $HAS_HEADER eq true}
						<td class="cellLabel">
							<span name="header_name">{$_HEADER_NAME}</span>
						</td>
						{/if}
						<td class="cellLabel">
							<span>{$_FIELD_VALUE|@textlength_check}</span>
						</td>
					</tr>
					{/foreach}
			</tbody>
			</table>
		</td>
	</tr>
</table>
<script>
    $(document).ready(function() {

	    $('#csvtable tr').on('click', function() {
	    	$('#csvtable tr.tbl-selected').removeClass('tbl-selected');
	        $(this).addClass('tbl-selected');
	        $('#cur_selected').val(this.rowIndex);
	    });

	});
 </script>
{include file="Import_Default_Values_Widget.tpl"|@vtemplate_path:'CsvImport'}
