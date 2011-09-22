<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	
    <title>{$title|default:'Framework Tools'}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    
    <style type="text/css">
		@import url("css/tools.css");
    </style>

	<script language="javascript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	{block 'js'}{/block}
    
</head>

<body>

{insert name="messages"}
{block 'content'}{/block}

</body>
</html>