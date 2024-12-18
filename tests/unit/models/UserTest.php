<?php

namespace tests\unit\models;

use app\models\user\User;

class UserTest extends \Codeception\Test\Unit
{

    public function testFindUserById()
    {
        expect_that($user = User::findIdentity(1));
        expect($user->username)->equals('adam_first');

        expect_not(User::findIdentity(2));

        expect_that($user = User::findIdentity(10));
        expect($user->username)->equals('rei_buckley');
    }

    public function testFindUserByUsername()
    {
        expect_that($user = User::findByUsername('adam_first'));
        expect_not(User::findByUsername('not-admin'));
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser($user)
    {
        $user = User::findByUsername('adam_first');
        expect_that($user->validatePassword('password'));
        expect_not($user->validateAuthKey('123456'));
    }

    public function testCheckName()
    {
        expect_that($user = User::findIdentity(1));
        expect($user->name)->equals('Adam First');
    }

}
