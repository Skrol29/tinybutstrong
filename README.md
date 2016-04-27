# TinyButStrong template engine

TBS is a PHP template engine for pro and beginners.
Only 1 class with few methods and properties, but it can do may things for any text templates, including HTML and XML.
The only engine that enables W3C compliant templates.
It has many plugins including OpenTBS.


## my adds:

Now need PHP 5.0

Added sorting blocks (arrays only) by custom field as int, float, string or by natural algorithm. Can be used for subblocks 

Format:

> [block...;_sortby **FieldName1**[ as **type**][ **order**][, FieldName2[ as type][ order][, ...]]_;...]

Where **order** can be ASC or DESC; **type** can be INT, FLOAT, STR, NAT; **FieldName** - custom field that should be sorted.

If two elements are equal when compared by the **FieldName1**, they will compared by the **FieldName2**, etc.


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
	    'num' => -10,
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



Also you can add custom compare functions: just add custom or replace existing **type**:

	clsTbsDataSource::$SortTypes['abs'] = array(
		'conv' => true,	// will be compared using standard operators (> and ==)
		'func' => 'abs'	// if 'conv'==true then values before the comparison will be converted using this callback
	);
	clsTbsDataSource::$SortTypes['cust'] = array(
		'conv' => false,
		'func' => 'myCallable'	// if 'conv'==false then values will be compared using this callable.
	);
	function myCallable($a, $b) {
		if ($a > $b) return 1;
		if ($a < $b) return -1;
		return 0;
	}

Template:

	Sort by absolute num
	<div>[myblock.name;block=div;sortby num as abs] - [myblock.num]</div>
	Sort by num using function 'myCallable'
	<div>[myblock.name;block=div;sortby num as cust] - [myblock.num]</div>
