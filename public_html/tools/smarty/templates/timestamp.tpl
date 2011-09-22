{extends 'page.tpl'}

{block 'content'}
<form action="timestamp.php" method="post">
	
    <fieldset>
        
        <div id="element">
        <label for="date">Date:</label>
        <input type="text" name="date" id="date" size="20" value="{$date|default:$smarty.now|date_format:'%d/%m/%Y %H:%M:%S'}" onclick="this.select()" />
        </div>
        
        <div id="element">
        <input type="submit" name="format_date" id="format_date" class="submit" value="Convert to timestamp &raquo;" />
        </div>
        
        <div id="element">
        <label for="timestamp">Timestamp:</label>
        <input type="text" name="timestamp" id="timestamp" size="20" value="{$timestamp|default:$smarty.now}" onclick="this.select()" />
        </div>
        
        <div id="element">
        <label for="date_format">Date Format:</label>
        <input type="text" name="date_format" id="date_format" size="20" value="{$date_format}" onclick="this.select()" />
        </div> 
        
        <div id="element">
        <input type="submit" name="format_timestamp" id="format_timestamp" class="submit" value="Convert to date &raquo;" />
        </div>
        
    </fieldset>
    
</form>
{/block}