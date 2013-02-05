
$(function ()
{
	
	$('textarea,input').focus (function ()
	{
		this.select ();
	});
	
	$('#class_overwrite_link').click (function ()
	{
		$('#class_overwrite').val ('1');
		$('#create_class_form').submit ();
	});
	
	$('#class_merge_link').click (function ()
	{
		$('#class_merge').val ('1');
		$('#create_class_form').submit ();
	});
	
	$('#newclass_database').change (function ()
	{
		$('#reload_tables').val ('1');
		$('#create_class_form').submit ();
	});
	
	$('#newclass_table').change (function ()
	{
		
		var classname	= convertToNamespaceAndClass ($(this).val ());
		
		$('#newclass_namespace').val (classname[0]);
		$('#newclass_name').val (classname[1]);
		
	});
	
	$('#create_save_class').click (function ()
	{
//		return confirm ('This will overwrite any existing class. Are you sure?');
	});
	
	$('#newclass_table').trigger ('change');
	
});


/**
 * Convert a table name to a namespace and class name
 * @param string table Table name
 * @return array
 */

function convertToNamespaceAndClass (table)
{

	var arr	= table.split ('_');
	
	$.each (arr, function (key, val)
	{
		val			= val.toLowerCase ();
		arr[key]	= val.charAt (0).toUpperCase () + val.slice (1);
	});
	
	arr.unshift ('Sonic', 'Model');
	
	var classname	= arr.pop ();
	var namespace	= arr.join ('\\');
	
	return [namespace, classname];

}
