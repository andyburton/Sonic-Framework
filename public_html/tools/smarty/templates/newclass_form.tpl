<h3>Class Details:</h3>

<form action="new_class.php" method="post" name="create_class_form" id="create_class_form">
	
    <fieldset>
        
        <div id="element">
        <label for="newclass_database">Database:</label>
        <select name="newclass_database" id="newclass_database">
        {html_options values=$db->getDatabases() output=$db->getDatabases() selected=$class->get('database')}
        </select>
        </div>
		
        <div id="element">
        <label for="newclass_table">Database Table:</label>
        <select name="newclass_table" id="newclass_table">
        {html_options values=$db->getTables($class->get('database')) output=$db->getTables($class->get('database')) selected=$class->get('table')}
        </select>
        </div>
        
        <div id="element">
        <label for="newclass_namespace">Namespace:</label>
        <input type="text" name="newclass_namespace" id="newclass_namespace" size="30" value="{$class->get('namespace')}" />
        </div>
		
        <div id="element">
        <label for="newclass_name">Class Name:</label>
        <input type="text" name="newclass_name" id="newclass_name" size="30" value="{$class->get('name')}" />
        </div>
		
        <div id="element">
        <label for="newclass_extends">Extends:</label>
        <input type="text" name="newclass_extends" id="newclass_extends" size="30" value="{$class->get('extends')}" />
        </div>
        
        <div id="element">
        <label for="newclass_description">Description:</label>
        <textarea name="newclass_description" id="newclass_description" cols="59" rows="1">{$class->get('description')}</textarea>
        </div>
        
        <div id="element">
        <label for="newclass_date_created">Date Created:</label>
        <input type="text" name="newclass_date_created" id="newclass_date_created" size="20" value="{$class->get('date_created')}" />
        </div>
        
    </fieldset>
    
    <fieldset style="margin-left: 20px;">

        <div id="element">
        <label for="newclass_author">Author:</label>
        <input type="text" name="newclass_author" id="newclass_author" size="30" value="{$class->get('author')}" />
        </div>
        
        <div id="element">
        <label for="newclass_email">Email:</label>
        <input type="text" name="newclass_email" id="newclass_email" size="50" value="{$class->get('email')}" />
        </div>
        
        <div id="element">
        <label for="newclass_link">Link:</label>
        <input type="text" name="newclass_link" id="newclass_link" size="50" value="{$class->get('link')}" />
        </div>
        
        <div id="element">
        <label for="newclass_copyright">Copyright:</label>
        <input type="text" name="newclass_copyright" id="newclass_copyright" size="30" value="{$class->get('copyright')}" />
        </div>
        
        <div id="element">
		<input type="hidden" name="reload_tables" id="reload_tables" value="0" />
		<input type="hidden" name="class_overwrite" id="class_overwrite" value="0" />
		<input type="hidden" name="class_merge" id="class_merge" value="0" />
		<input type="hidden" name="create_action" id="create_action" value="{$action|default:''}" />
        <input type="submit" name="create_class" id="create_class" class="submit" value="View Class &raquo;" />
        <input type="submit" name="create_save_class" id="create_save_class" class="submit" value="Save Class &raquo;" style="margin-right: 6px;" />
        </div>
        
    </fieldset>
    
</form>