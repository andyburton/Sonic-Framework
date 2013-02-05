{extends 'page.tpl'}

{block 'content'}
<form action="crypt.php" method="post">
	
    <fieldset>
        
        <div id="element">
        <label for="original">Input:</label>
		<textarea name="original" id="original" onclick="this.select()" cols="50" rows="4">{$original}</textarea>
        </div>
        
        <div id="element">
        <label for="type">Type:</label>
        <select name="type" id="type">
        {html_options values=$options output=$options selected=$type}
        </select>
        </div>
		
        <div id="element">
        <input type="submit" name="crypt" id="crypt" class="submit" value="Encrypt &raquo;" />
        </div>
        
        <div id="element">
        <label for="crypt">Output:</label>
        <textarea name="crypt" id="crypt" onclick="this.select()" cols="50" rows="4">{$crypt}</textarea>
        </div>
        
    </fieldset>
    
</form>
{/block}