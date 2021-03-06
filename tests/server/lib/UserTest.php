<?php

require_once dirname(__FILE__).'/../../../server/lib/User.php';
/**
 * Test class for User.
 * Generated by PHPUnit on 2016-08-25 at 21:02:10.
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before the test case class' first test.
     */
    public static function setUpBeforeClass()
    {
        require_once dirname(__FILE__).'/../../TestingTool.php';
        $test = new TestingTool();
        $test->setupDummySqlConnection();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $id = 1;
        $this->object = new User($id);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a the test case class' last test.
     */
    public static function tearDownAfterClass()
    {
        require_once dirname(__FILE__).'/../../TestingTool.php';
        $test = new TestingTool();
        $test->restoreConfig();
    }

    /**
     * @covers User::__construct
     */
    public function testConstruct()
    {
        $id = 2;
        $this->object = new User($id);
        $this->assertEquals($this->object->id, $id, 'User id should be set to '.$id);
    }

    /**
     * @covers User::populate
     */
    public function testPopulate()
    {
        $this->assertTrue($this->object->populate(), 'Populating user failed');
        $this->assertEquals($this->object->login, 'admin', 'User is not correct: "admin" expected but get "'.$this->object->login.'"');
    }

    /**
     * @covers User::populate
     */
    public function testPopulateWithEmptyId()
    {
        $this->object = new User();
        $this->assertFalse($this->object->populate(), 'Populating user require a valid id');
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModel()
    {
        $user = new User();
        $user->sub = 99;
        $user->login = 'login';
        $user->name = 'name';
        $user->password = 'password';
        $user->status = 1;
        $this->assertTrue($this->object->validateModel($user, $error), 'Validate user should be ok, but refused with following reason: '.$error);
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModelErrorWithNullUser()
    {
        $user = null;
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate null user should be refused');
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModelErrorIdIsNotInt()
    {
        $user = new User();
        $user->id = 'alpha';
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate null user should be refused');
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModelErrorNoLogin()
    {
        $user = new User();
        $user->name = 'name';
        $user->password = 'password';
        $user->status = 1;
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate user should be refused without login');
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModelErrorNoPassword()
    {
        $user = new User();
        $user->login = 'login';
        $user->name = 'name';
        $user->status = 1;
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate user should be refused without password');
    }

    /**
     * @covers User::validateModel
     */
    public function testValidateModelErrorNoStatus()
    {
        $user = new User();
        $user->login = 'login';
        $user->password = 'password';
        $user->name = 'name';
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate user should be refused without status');
        $user->status = 'a';
        $this->assertFalse($this->object->validateModel($user, $error), 'Validate user should be refused with alphanumeric status');
    }

    /**
     * @covers User::create
     * @depends testValidateModel
     */
    public function testCreate()
    {
        $user = new User();
        $user->login = 'login';
        $user->name = 'name';
        $user->password = 'password';
        $user->status = 1;
        if (!$this->object->validateModel($user, $error)) {
            $this->markTestIncomplete('User creation has been skipped because model validation failed');

            return;
        }
        $this->assertTrue($this->object->create($user, $error), 'User creation should be ok, but refused with following reason: '.$error);
        $this->assertGreaterThanOrEqual(2, $this->object->id, 'User identifier should be set with a numeric value greater than 1');
    }

    /**
     * @covers User::create
     * @depends testValidateModel
     */
    public function testCreateWithLoginAlreadyUsed()
    {
        $user = new User();
        $user->login = 'login';
        $user->name = 'name';
        $user->password = 'password';
        $user->status = 1;
        if (!$this->object->validateModel($user, $error)) {
            $this->markTestIncomplete('User creation has been skipped because model validation failed');

            return;
        }
        $this->assertFalse($this->object->create($user, $error), 'User creation should be refused because requested login is already used');
        $this->assertEquals('login `'.$user->login.'` already exists', $error, 'Error message should explain that login is already used, but following reason is returned: '.$error);
    }

    /**
     * @covers User::updateScope
     */
    public function testUpdateScope()
    {
        $scope = '';
        $this->assertTrue($this->object->updateScope($scope), 'Scope update failed');
        $scope = 'admin user';
        $this->assertTrue($this->object->updateScope($scope), 'Scope update failed');
    }

    /**
     * @covers User::updateScope
     */
    public function testUpdateScopeOnUnknownUser()
    {
        $scope = 'admin user';
        $this->object->id = null;
        $this->assertFalse($this->object->updateScope($scope), 'Scope was updated on unknown user');
    }

    /**
     * @covers User::update
     */
    public function testUpdate()
    {
        $this->object->login = 'admin';
        $this->object->name = 'admin name';
        $this->object->email = 'mail';
        $this->object->password = 'password';
        $this->object->status = 1;
        $this->assertTrue($this->object->update($this->object, $error), 'User update failed with following reason: '.$error);
    }

    /**
     * @covers User::update
     */
    public function testUpdateWithLoginAlreadyUsed()
    {
        $this->object->login = 'login';
        $this->object->name = 'name';
        $this->object->password = 'password';
        $this->object->status = 1;
        $this->assertFalse($this->object->update($this->object, $error), 'User updated but login already used');
        $this->assertEquals(': login `'.$this->object->login.'` already exists', $error, 'Error message should explain that login "'.$this->object->login.'" is already used, but following reason is returned: '.$error);
    }

    /**
     * @covers User::checkCredentials
     */
    public function testCheckCredentials()
    {
        $login = 'admin';
        $password = 'password';
        $this->assertTrue($this->object->checkCredentials($login, $password), 'Credentials should be ok');
        $login = 'ADMIN';
        $this->assertTrue($this->object->checkCredentials($login, $password), 'Credentials should be ok, login should be case insensitive');
    }

    /**
     * @covers User::checkCredentials
     */
    public function testCheckCredentialsInvalid()
    {
        $login = 'admin';
        $password = 'wrongPassword';
        $this->assertFalse($this->object->checkCredentials($login, $password), 'Credentials should not be ok');
        $password = null;
        $this->assertFalse($this->object->checkCredentials($login, $password), 'Credentials should not be ok, password should not be null');
        $password = 'PASSWORD';
        $this->assertFalse($this->object->checkCredentials($login, $password), 'Credentials should not be ok, password should be case sensitive');
    }

    /**
     * @covers User::getScope
     */
    public function testGetScope()
    {
        $scope = $this->object->getScope();
        $this->assertContains('user', $scope, 'Scope should contain "user"');
        $this->assertContains('admin', $scope, 'Scope should contain "admin"');
    }

    /**
     * @covers User::getProfile
     */
    public function testGetProfile()
    {
        $this->object->populate();
        $user = $this->object->getProfile();
        $attributes = [
            'sub' => 1,
            'name' => 'admin name',
            'login' => 'admin',
            'email' => 'mail',
            'scope' => 'admin user',
        ];
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->assertObjectHasAttribute($attributeName, $user, 'User should have the "'.$attributeName.'" attribute');
            $this->assertAttributeEquals($attributeValue, $attributeName, $user, 'User->'.$attributeName.' should be set to "'.$attributeValue.'" but found: "'.$user->$attributeName.'"');
        }
    }

    /**
     * @covers User::getAllUsers
     */
    public function testGetAllUsers()
    {
        $users = $this->object->getAllUsers();
        $this->assertInternalType('array', $users, 'Return should be an array');
        $this->assertNotCount(0, $users, 'Array should contain at least 1 user');
        $this->assertInstanceOf('User', $users[0], 'Array should contain User class');
    }
}
