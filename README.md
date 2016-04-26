# TinyButStrong template engine

TBS is a PHP template engine for pro and beginners.
Only 1 class with few methods and properties, but it can do may things for any text templates, including HTML and XML.
The only engine that enables W3C compliant templates.
It has many plugins including OpenTBS.


## my adds:

Now need PHP >=5.3

Added sorting blocks by custom field as int, float, string or by natural algorithm

Format:

> [block...;_sortby **FieldName1**[ as **type**][ **order**][, FieldName2[ as type][ order][, ...]]_;...]

Where **order** can be ASC or DESC; **type** can be INT, FLOAT, STR, NAT; **FieldName** - custom field that should be sorted.

If two elements are equal when compared by the **FieldName1**, they are compared by the **FieldName2**, etc.


### For example:

PHP: 

	$block = [
	  [
	    'num' => 1,
	    'name' => 'killedrone'
	  ],
	  [
	    'num' => 2,
	    'name' => 'dildodrone'
	  ],
	    'num' => 3,
	    'name' => 'othername'
	];
	$tbs->MergeBlock('myblock', $block);
	
template code:

    Just sort by num
    <div>[myblock.name;block=div;sortby num] - [myblock.num]</div>
    
  	Sort by name desc
  	<div>[myblock.name;block=div;sortby name as str desc] - [myblock.num]</div>
  	
    Sort by num asc and name desc
    <div>[myblock.name;block=div;sortby num as int asc, name as nat desc] - [myblock.num]</div>