<?php

class FrmTestCase extends TBSUnitTestCase {

	function FrmTestCase() {
		$this->UnitTestCase('Frm Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testNumericFormats() {

		// decimal and thousand separators
		$this->assertEqualMergeFieldStrings("{[a;frm=0.]}", array('a'=>3128.50),  "{3129}", "test round limit #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.]}", array('a'=>3128.49),  "{3128}", "test round limit #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>3128.495),  "{3128.50}", "test decimals alone");
		$this->assertEqualMergeFieldStrings("{[a;frm=0 000.00]}", array('a'=>3128.495),  "{3 128.50}", "test thousand and decimal #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=0 000,00]}", array('a'=>3128.495),  "{3 128,50}", "test thousand and decimal #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0,000.00]}", array('a'=>3128.495),  "{3,128.50}", "test thousand and decimal #3");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.000 00]}", array('a'=>3128.495),  "{3.128 50}", "test thousand and decimal #4 wired separators");

		// leading zeros
		$this->assertEqualMergeFieldStrings("{[a;frm=0000000.00]}", array('a'=>3128.495),  "{0003128.50}", "test leading zeros");

		// pourcents
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00%]}", array('a'=>0.495),  "{49.50%}", "test pourcent standard");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00 %]}", array('a'=>0.495),  "{49.50 %}", "test pourcent #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=% 0.00]}", array('a'=>0.495),  "{% 49.50}", "test pourcent #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0,000.00%]}", array('a'=>3128.495),  "{312,849.50%}", "test pourcent with thousand separator");

		// number format with text parts
		$this->assertEqualMergeFieldStrings("{[a;frm='0 000,00 �']}", array('a'=>3128.495),  "{3 128,50 �}", "test number with text parts (not using symbols)");

		// unexpected values
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>false),  "{}", "test unexpected values: false");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>true),  "{1}", "test unexpected values: true"); // TBS performs an implicite conversion from true to string
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>''),  "{}", "test unexpected values: empty string");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>'zzz'),  "{zzz}", "test unexpected values: string");
		
	}

	function testDateFormats() {

		$d = mktime(21, 46, 33, 11, 30, 2001);
		$ds = mktime(9, 8, 7, 2, 3, 2001); // sort, i.e. with (day<10) and (month<10) and (hour<10) and (minutes<10)
		$d1 = mktime(21, 46, 33, 11, 1, 2001); // first  day of month
		$d2 = mktime(21, 46, 33, 11, 2, 2001); // second day of month
		$d3 = mktime(21, 46, 33, 11, 3, 2001); // third  day of month
		$d4 = mktime(21, 46, 33, 11, 4, 2001); // other  day of month
		
		// common
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>$d),  "{2001-11-30 21:46:33}", "test ISO date");

		// date part
		$this->assertEqualMergeFieldStrings("{[a;frm=yy]}", array('a'=>$d),  "{01}", "test year on two digits");
		$this->assertEqualMergeFieldStrings("{[a;frm=d dd ddd dddd]}", array('a'=>$d) ,  "{30 30 Fri Friday}", "test day formats (day>=10)");
		$this->assertEqualMergeFieldStrings("{[a;frm=d dd ddd dddd]}", array('a'=>$ds),  "{3 03 Sat Saturday}", "test day formats (day<10)");
		$this->assertEqualMergeFieldStrings("{[a;frm=m mm mmm mmmm]}", array('a'=>$d),   "{11 11 Nov November}", "test month formats (month>=10)");
		$this->assertEqualMergeFieldStrings("{[a;frm=m mm mmm mmmm]}", array('a'=>$ds),  "{2 02 Feb February}", "test month formats (month<10)");
		$this->assertEqualMergeFieldStrings("{[a;frm=w]}", array('a'=>$d),  "{5}", "test number of the day in the week");
		$this->assertEqualMergeFieldStrings("{[a;frm=dxx]}", array('a'=>$d),  "{30th}", "test number of the day in the month");
		$this->assertEqualMergeFieldStrings("{[a;frm=dxx]}", array('a'=>$d1),  "{1st}", "test number of the day in the month (st)");
		$this->assertEqualMergeFieldStrings("{[a;frm=dxx]}", array('a'=>$d2),  "{2nd}", "test number of the day in the month (nd)");
		$this->assertEqualMergeFieldStrings("{[a;frm=dxx]}", array('a'=>$d3),  "{3rd}", "test number of the day in the month (rd)");
		$this->assertEqualMergeFieldStrings("{[a;frm=dxx]}", array('a'=>$d4),  "{4th}", "test number of the day in the month (th)");

		// hour part
		$this->assertEqualMergeFieldStrings("{[a;frm=rr ampm]}", array('a'=>$d),  "{09 pm}", "test hour (pm)");
		$this->assertEqualMergeFieldStrings("{[a;frm=rr AMPM]}", array('a'=>$d),  "{09 PM}", "test hour (PM)");
		$this->assertEqualMergeFieldStrings("{[a;frm=rr ampm]}", array('a'=>$ds),  "{09 am}", "test hour (am)");
		$this->assertEqualMergeFieldStrings("{[a;frm=rr AMPM]}", array('a'=>$ds),  "{09 AM}", "test hour (AM)");
		$this->assertEqualMergeFieldStrings("{[a;frm=h]}", array('a'=>$ds),  "{9}", "test hour (one digit)");
		$this->assertEqualMergeFieldStrings("{[a;frm=r]}", array('a'=>$d),  "{9}", "test hour (one digit)");
		$this->assertEqualMergeFieldStrings("{[a;frm=hm]}", array('a'=>$d),  "{09}", "test hour (deprecated)"); // hm is like rr, it's deprecated since TBS 3.2.0

		// locale
		/* no configuration file on my computer :(
		$currLocale = setlocale(LC_TIME, null); // save current Locale
		setlocale(LC_TIME, 'fr_FR'); // change current Locale
		$this->assertEqualMergeFieldStrings("{[a;frm=m mm mmm mmmm(locale)]}", array('a'=>$d),   "{11 11 Nov Novembre}", "test month formats (month>=10)");
		setlocale(LC_TIME, $currLocale); // restore current Locale
		*/

		// string values. TBS try to convert strings into timestamps using the PHP function strtotime()
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>'2001-12-05'),  "{2001-12-05 00:00:00}", "test string values (ISO without hour)");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>'2001-12-05 15:12:03'),  "{2001-12-05 15:12:03}", "test string values (ISO with hour)");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>'20011205'),  "{2001-12-05 00:00:00}", "test string values (compact without hour)");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>'20011205 15:12:03'),  "{2001-12-05 15:12:03}", "test string values (compact with hour)");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>'Tue, 1 Feb 2011 01:58:44 -0800 (PST)'),  "{2011-02-01 09:58:44}", "test string values (RFC 2822)");

		// date format with text parts
		$this->assertEqualMergeFieldStrings("{[a;frm='\"date(yyyy-mm-dd)=\"yyyy-mm-dd \"time=\"hh:nn:ss']}", array('a'=>$d),  "{date(yyyy-mm-dd)=2001-11-30 time=21:46:33}", "test date with text parts");

		// unexpected values
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>false),  "{}", "test unexpected values: false");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>true),  "{1}", "test unexpected values: true"); // TBS performs an implicite conversion from true to string
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>''),  "{}", "test unexpected values: empty string");
		$this->assertEqualMergeFieldStrings("{[a;frm=yyyy-mm-dd hh:nn:ss]}", array('a'=>0),  "{1970-01-01 00:00:00}", "test unexpected values: 0"); // 0 is a timsetamp for the sart of unix dates
		
	}

	function testConditionalFormats() {

		$p = 3128.495;
		$n = -4239.384;
		$z = 0;
		$e = '';

		// 2 conditions
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00]}", array('a'=>$p),  "{+3 128,50}", "2 conditions merged with a positive value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00]}", array('a'=>$n),  "{-4 239,38}", "2 conditions merged with a negative value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00]}", array('a'=>$z),  "{+0,00}", "2 conditions merged with a zero value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00]}", array('a'=>$e),  "{}", "2 conditions merged with a null-empty value");
		
		// 3 conditions
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0]}", array('a'=>$p),  "{+3 128,50}", "3 conditions merged with a positive value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0]}", array('a'=>$n),  "{-4 239,38}", "3 conditions merged with a negative value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0]}", array('a'=>$z),  "{0}", "3 conditions merged with a zero value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0]}", array('a'=>$e),  "{}", "3 conditions merged with a null-empty value");

		// 4 conditions
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0|(nothing)]}", array('a'=>$p),  "{+3 128,50}", "4 conditions merged with a positive value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0|(nothing)]}", array('a'=>$n),  "{-4 239,38}", "4 conditions merged with a negative value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0|(nothing)]}", array('a'=>$z),  "{0}", "4 conditions merged with a zero value");
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|0|(nothing)]}", array('a'=>$e),  "{(nothing)}", "4 conditions merged with a null-empty value");

		// strange or bug ?
		$this->assertEqualMergeFieldStrings("{[a;frm=+0 000,00|-0 000,00|zero|(nothing)]}", array('a'=>$z),  "{ze12o}", "zero part merged as standard"); // this is not a bug yet, only a poor functionality: the zero part format is used a standard format (i.e. date or numeric). In this example, it is merged a date format: r is replaced by the hour
		
		//$this->dumpLastSource();

	}

	function testTemplateFormats() {

		// we need methods that can simulate a LoadTemplate()
		//$this->assertEqualMergeFieldStrings("[onload;tplfrms;money='0,000.00']{[a;frm=money]}", array('onload'=>false, 'a'=>3128.495),  "{3,128.50}", "test template formats");
	
	}

}

?>