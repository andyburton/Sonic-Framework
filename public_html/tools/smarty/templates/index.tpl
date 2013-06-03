{extends 'page.tpl'}

{block 'content'}
<ul class="index">
{foreach from=$links item=link}
<li><a href="{$link}">{$link}</a></li>
{/foreach}
</ul>
{/block}