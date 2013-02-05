{extends 'newclass.tpl'}

{block 'content'}

<div class="message_container">
	<div class="message_error">
		<p>The class file already exists!</p>
		<p>Would you like to <a href="#" id="class_overwrite_link">overwrite</a> the existing class or <a href="#" id="class_merge_link">merge</a> it?</p>
	</div>
</div>


{include file="newclass_form.tpl"}
{/block}