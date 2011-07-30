<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * eWide Client
 *
 * @copyright  Copyright (c) 2010 eWide
 * @package    eWide Client
 */

/**
 * Base class for all application presenters.
 *
 * @author     eWide
 * @package    eWide Client
 */

// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';

// Step 2: Configure environment
// 2a) enable Debug for better exception and error visualisation
Debug::enable();
//Debug::enable(Debug::DEVELOPMENT);

// 2b) load configuration from config.ini file
Environment::loadConfig();

// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = FALSE;

// Step 4: Setup application router
$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'presenter' => 'Login',
	'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('login', array(
	'presenter' => 'Login',
	'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('export', array(
	'presenter' => 'Export',
	'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('visits/<date_from>/<date_to>', array(
	'presenter' => 'Overview',
	'action' => 'visits',
	'date_from' => null,
	'date_to' => null,
));

$router[] = new Route('diary/<month>/<year>', array(
	'presenter' => 'Diary',
	'action' => 'default',
	'month' => null,
	'year' => null,
));

$router[] = new Route('orders/<date_from>/<date_to>', array(
	'presenter' => 'Overview',
	'action' => 'orders',
	'date_from' => null,
	'date_to' => null,
));

$router[] = new Route('settings/<action>/<id>', array(
	'presenter' => 'Settings',
	'action' => 'default',
	'id' => NULL,
));

$router[] = new Route('client/<id>', array(
	'presenter' => 'Client',
	'action' => 'show',
	'id' => NULL,
));

$router[] = new Route('client/<id>/<action>/<actionid>', array(
	'presenter' => 'Client',
	'action' => NULL,
	'id' => NULL,
	'actionid' => NULL
));

$router[] = new Route('help/<category>/<id>', array (
	'presenter' => 'Help',
	'category' => NULL,
	'id' => NULL,
));

$router[] = new Route('<group>/<order>/<sort>/<search>/<tags>/<groups>/<columns>/<vp-page>', array(
	'presenter' => 'Homepage',
	'action' => 'default',
	'group' => '0',
	'order' => 'id',
	'sort' => 'asc',
	'vp-page' => '1',
	'search' => '*',
	'tags' => '*',
	'groups' => '*',
	'columns' => '*',
	'id' => NULL,
));

$router[] = new Route('<presenter>/<action>/<id>', array(
	'presenter' => 'Homepage',
	'action' => 'default',
	'id' => NULL,
));

// Step 5: Run the application!
/* Database */
dibi::connect((array)Environment::getConfig('db'));

/* Form extensions */
function Form_addDateTimePicker(Form $_this, $name, $label, $cols = NULL, $maxLength = NULL) {
	return $_this[$name] = new DateTimePicker($label, $cols, $maxLength);
}

function Form_addDatePicker(Form $_this, $name, $label, $cols = NULL, $maxLength = NULL) {
	return $_this[$name] = new DatePicker($label, $cols, $maxLength);
}

Form::extensionMethod('Form::addDateTimePicker', 'Form_addDateTimePicker');
Form::extensionMethod('Form::addDatePicker', 'Form_addDatePicker');

$application->run();
