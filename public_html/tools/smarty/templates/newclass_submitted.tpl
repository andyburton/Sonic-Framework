{extends 'newclass.tpl'}

{block 'content'}
{include file="newclass_form.tpl"}

<div id="class_results">
<h3>Class Results:</h3>
<form>
<textarea name="class_data" id="class_data" class="fixedwidth" cols="120" rows="20">{$class_generated}</textarea>
</form>
</div>
{/block}