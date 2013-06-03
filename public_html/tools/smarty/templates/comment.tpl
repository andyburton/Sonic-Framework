{extends 'page.tpl'}

{block 'content'}
<form action="comment.php" method="post">
	
    <fieldset>
        
        <div id="element" style="width: 100%;">
        <label for="comment_type">Comment Type:</label>
        <select name="comment_type" id="comment_type">
        {html_options values=['line','star','phpdoc'] output=['line','star','phpdoc'] selected=$smarty.post.comment_type|default:'phpdoc'}
        </select>
        </div> 
        
        <div id="element" style="width: 100%;">
        <label for="comment">Comment:</label>
        <textarea name="comment" id="comment" class="fixedwidth" cols="145" rows="10">{$clean_comment}</textarea>
        </div>
        
        <div id="element" style="width: 100%;">
        <label for="comment_formatted">Formatted Comment:</label>
        <textarea name="comment_formatted" id="comment_formatted" class="fixedwidth" cols="145" rows="32" onclick="this.select();">{$comment}</textarea>
        </div>
        
        <div id="element" style="width: 100%;">
        <input type="submit" name="create_comment" class="submit" value="Create Comment &raquo;" />
        </div>
        
    </fieldset>
    
</form>
{/block}