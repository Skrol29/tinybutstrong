# Changes in TinyButStrong Template Engine

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)

## [3.13.2] - 2022-02-23

### Fixed

- Version


## [3.13.1] - 2022-02-13

### Enhancements

- method SetVarRefItem() supports an array of items

## [3.13.0] - 2022-02-07

### Enhancements

- Compatibility with PHP 8.1

### Added

- New methods SetVarRefItem() and GetVarRefItem() : uniformize and ensure compatibility to set a VarRef items.

## [3.12.2] - 2020-11-03

### Fixed

- Parameter 'magnet=#' used with MergeBlock() makes erroneous merging when magnet is activated, except for the first occurence.

## [3.12.1] - 2020-10-24

### Fixed

- Parameter 'frm' displays '1970-01-01' for a date over '2038-01-19 03:14:07' on a PHP 32-bits plateform.

## [3.12.0] - 2020-10-12

### Added

- Compatibility with PHP 8
- New parameters for blocks : 'firstingrp', 'lastingrp', 'singleingrp'. They define sections of a block displayed for the first, the last or the single record in a group.

### Changed

- MergeBlock() return false instead of 0 when no block and no field have been merged.

### Fixed

- Infinite loop when a plugin event OnOperation returns false during a mergin of an automatic field such as [onload.my_var].

## [3.11.0] - 2019-02-10

### Added

- New method GetAttValue($Name[,$delete]) : enables you to read an attribute of an HTML or XML element in the template.
- New method ReplaceFields(array($name=>$prms) [, $BlocName]) : replace simple TBS fields with new definitions of fields.
- New parameter 'combo' : apply a set of parameters defined at the PHP side using SetOption('prm_combo').
- Subnames for fields, array queries and ObjectRef path can support methods overloaded with magic function _call().

### Fixed

- PHP error: one meth_Misc_Alert() called with a wrong number of arguments.

## [3.10.1] 2015-12-03


### Fixed

- Coding OnCacheField event in a plug-in: in some circumstances, moved locators may be ignored by TBS.
  Could happen with OpenTBS when using parameter "ope=tbs:num" or a similar operator. 
- Coding OnCacheField event in a plug-in: stronger way to manage embedding fields + support property DelMe.  
 
## [3.10.0] - 2015-11-08

### Added

- New operator "*" for block definition syntax. Example: "block=3*tr".

- New operator "!" for block definition syntax. Use it on first or last tag in order to exclude the tag from the block bound. Example: "block=!div".

- New marker "." for block definition. Use it to represents the bound of the TBS field itself. Example: "block=(.)".

- New arguments $LocLst and $Pos for the event OnCacheField. It enables a plug-in to move, add or delete TBS fields.

- Native support for SQLite3.

- Parameter "parallel=tbs:table" now supports <tbody>, <tfoot>, <colgroup> and <col>. The <col> tags must be closed even if HTML actually allows unclosed tags.

- Better management of fields moved in a set of other fields, when using parameter "att".
  Parameter "att" can make a TBS field moving forward another of the same block.
  
- Support GetOption('parallel_conf').

- Support GetOption('block_alias').

### Fixed

- Parameter "att" does not find the self-closing tag if there is no space before "/>". Example :<input/>
- "Notice: Undefined property: clsTbsLocator::$AttName" can occurs if parameter "atttrue" is used for an attribute which is not already present in the target element.

- Parameter "ope=upperw" works only with lower case characters.

- Error message "Notice: Undefined property: clsTbsLocator::$AttName in tbs_class.php on line 1492".


## [3.9.0] - 2014-01-26

### Added

- New parameter "parallel" for merging a block in columns (or any other parallel ways).
- New way for merging sub-template: PHP error messages are not absorbed any more.
  This new way may not be compatible with subscripts that uses the echo command (very rare).
  In this case you should add set option $TBS->SetOptions('old_subtemplate') for compatibility.
- Now can merge DateTime objects and also objects with the magic method __toString().

### Changed

- Error messages are in plain text instead of HTML when PHP is used in command line (CLI).
- Some code enhancements.
  - replace or  with ||
  - replace and with &&
  
### Fixed

- HTML plug-in version 1.0.8: parameter "select" now works with values containing special HTML characters.
- Parameter "frm": leading zero coming with a prefix or a suffix may not format the number as expected.
  http://www.tinybutstrong.com/forum.php?thr=3208
- There use to have an error message about $Loc->AttForward when the entity of parameter "att" is not found.

## [3.8.2] - 2013-04-20


### Added

- Explicit error message when a colmun is missing for a grouping paremeter (headergrp, footergrp, splittergrp)

- Add option methods_allowed


## [3.8.1] - 2012-04-01

### Fixed

-  PHP 5.4 :
   Unexpected PHP error [Array to string conversion] severity [E_NOTICE] in [D:\Users\Qwerty\Dev HTML\Site Dev\svn_TinyButStrong\trunk\tbs_class.php line 72]
   Unexpected PHP error [Array to string conversion] severity [E_NOTICE] in [D:\Users\Qwerty\Dev HTML\Site Dev\svn_TinyButStrong\trunk\tbs_class.php line 1369]

### Added

- Plugin MergeOnFly

