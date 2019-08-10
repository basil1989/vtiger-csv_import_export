{strip}
<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
			</div>&nbsp
<div style="padding-left: 15px;">
    <form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
        <input type="hidden" name="module" value="{$FOR_MODULE}" />
        <input type="hidden" name="view" value="List" />
        <input type="hidden" name="mode" value="uploadAndParse" />
        <table style=" width:90%;margin-left: 5% " class="searchUIBasic" cellspacing="12">
            <tr>
                <td class="font-x-large" align="left" colspan="2">
                    <strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}</strong>
                </td>
            </tr>
            {if $ERROR_MESSAGE neq ''}
                <tr>
                    <td class="style1" align="left" colspan="2">
                        <span class="alert-error">{$ERROR_MESSAGE}</span>
                    </td>
                </tr>
            {/if}
            <tr>
                <td class="leftFormBorder1 importContents" width="80%" valign="top">
                    {include file='Import_Step1.tpl'|@vtemplate_path:'CsvImport'}
                </td>
            </tr>
            <tr>
                <td align="right" colspan="2">
                    {include file='Import_Basic_Buttons.tpl'|@vtemplate_path:'CsvImport'}
                </td>
            </tr>
        </table>
    </form>
</div>
{/strip}