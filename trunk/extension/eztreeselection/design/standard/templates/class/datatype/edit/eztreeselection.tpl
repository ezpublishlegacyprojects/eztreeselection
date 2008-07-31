{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{* <link type="text/css" rel="stylesheet" href={"stylesheets/treeview/assets/skins/sam/treeview.css"|ezdesign} /> *}
<script src={"javascript/yahoo/yahoo-min.js"|ezdesign}></script>
<script src={"javascript/event/event-min.js"|ezdesign}></script>
<script src={"javascript/treeview/treeview.js"|ezdesign}></script>
<script src={"javascript/eztreeselection.js"|ezdesign}></script>

<div class="block">
<label>{'Style'|i18n( 'design/standard/class/datatype' )}:</label>
<select name="ContentClass_eztreeselection_ismultiple_value_{$class_attribute.id}">
<option value="0" {section show=not( $class_attribute.content.is_multiselect )}selected="selected"{/section}>{'Single choice'|i18n( 'design/standard/class/datatype' )}</option>
<option value="1" {section show=$class_attribute.content.is_multiselect}selected="selected"{/section}>{'Multiple choice'|i18n( 'design/standard/class/datatype' )}</option>
</select>
</div>


<fieldset>
{* <legend>{'Options'|i18n( 'design/standard/class/datatype' )}</legend> *}

<div id="ContentClass_eztreeselection_main_{$class_attribute.id}">
<script type="text/javascript">
	{$class_attribute.content.classEditYuiTreeViewJavascript}
</script>
</div>

{*
<hr />
<label style="display:inline;" for="ContentClass_eztreeselection_newoptionlabel_{$class_attribute.id}" >{'New label'|i18n( 'design/standard/class/datatype' )}</label>
<input type="text" id="ContentClass_eztreeselection_newoptionlabel_{$class_attribute.id}" name="ContentClass_eztreeselection_newoptionlabel_{$class_attribute.id}" value="" title="{'New label for the selected option.'|i18n( 'design/standard/class/datatype' )}" />
<hr />
*}

<div style="display: none;">
{* Buttons. *}
{if $class_attribute.content.has_options}
<input class="button" type="submit" id="ContentClass_eztreeselection_removeoption_button_{$class_attribute.id}" name="ContentClass_eztreeselection_removeoption_button_{$class_attribute.id}" value="{'Remove selected'|i18n( 'design/standard/class/datatype' )}" title="{'Remove selected options.'|i18n( 'design/standard/class/datatype' )}" />
{else}
<input class="button-disabled" type="submit" id="ContentClass_eztreeselection_removeoption_button_{$class_attribute.id}" name="ContentClass_eztreeselection_removeoption_button_{$class_attribute.id}" value="{'Remove selected'|i18n( 'design/standard/class/datatype' )}" disabled="disabled" />
{/if}

<input class="button" type="submit" id="ContentClass_eztreeselection_renameoption_button_{$class_attribute.id}" name="ContentClass_eztreeselection_renameoption_button_{$class_attribute.id}" value="{'Rename selected'|i18n( 'design/standard/class/datatype' )}" title="{'Rename the selected options.'|i18n( 'design/standard/class/datatype' )}" />
<input class="button" type="submit" id="ContentClass_eztreeselection_newoption_button_{$class_attribute.id}" name="ContentClass_eztreeselection_newoption_button_{$class_attribute.id}" value="{'New option'|i18n( 'design/standard/class/datatype' )}" title="{'Add a new option.'|i18n( 'design/standard/class/datatype' )}" />
</div>
</fieldset>