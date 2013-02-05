{extends 'page.tpl'}

{block 'content'}
<h3>Module Permissions</h3>
{foreach $modules as $module}

<div class="element" module_id="{$module->get('id')}">
	<span class="title">{$module->get('name')} ({$module->countMethods()} Permissions)</span>
</div>

<div id="module_{$module->get('id')}" class="element" style="margin: 0px 0px 20px 20px;">
	{foreach $module->getMethods('value') as $method}
	<div class="element">
		<span style="width: 50px">{$method->get('value')}</span>
		<span style="width: 200px">{$method->get('name')}</span>
		<span>{$method->getStringValue()}</span>
	</div>
	{/foreach}
</div>

{/foreach}
{/block}