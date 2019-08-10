<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css'>


<table width="100%" cellspacing="0" cellpadding="10" class="importContents">
	<tr>
		<td>
			<strong>{'CSV_LBL_IMPORT_STEP_5'|@vtranslate:$MODULE}:</strong>
		</td>
		<td>
			<span class="big">{'CSV_LBL_IMPORT_STEP_5_DESCRIPTION'|@vtranslate:$MODULE}</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="hidden" id="select2input" name="select2input" value="" />
			<input type="hidden" id="select2input2" name="select2input2" value="" />
			<select id="select2" style="width:100%;" multiple>
				{foreach item=VALUE key=NAME from=$ACCOUNT_FIELDS}
				<option value="{$NAME}">{$VALUE}</option>
				{/foreach}
			</select>		
		</td>
	</tr>
</table>
<script src='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js'></script>
<script>
	var $input = $("#select2");
	$input.select2().on("select2:select", function (e) {
	    var selected_element = $(e.currentTarget);
	    var select_val = selected_element.val();
	    $('#select2input').val(select_val);
	    console.log(select_val);

	    var select_txt = $('#select2').select2('data').map(function(elem){ 
                return elem.text 
           });
	    $('#select2input2').val(select_txt);
	    console.log(select_txt);
	});
	$("ul.select2-selection__rendered").sortable({
	  containment: 'parent'
	});
</script>

{include file="Import_Default_Values_Widget.tpl"|@vtemplate_path:'CsvImport'}