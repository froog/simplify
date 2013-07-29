<?php

Object::add_extension('LeftAndMain', 'SimplifyLeftAndMainDecorator');
DataObject::add_extension('Page', 'SimplifyDataObjectDecorator');
DataObject::add_extension('Group', 'SimplifyGroupDecorator');

Director::addRules(100, array(
	'admin/simplify/$Action/$ID' => 'SimplifyAction',
));



?>