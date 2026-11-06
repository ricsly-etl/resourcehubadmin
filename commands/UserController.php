<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

class UserController extends Controller
{
    public function actionCreate(): int
    {
        $username = trim((string) getenv('ADMIN_USERNAME'));
        $email = trim((string) getenv('ADMIN_EMAIL'));
        $password = (string) getenv('ADMIN_PASSWORD');
        $first = trim((string) getenv('ADMIN_FIRST_NAME'));
        $last = trim((string) getenv('ADMIN_LAST_NAME'));
        if ($first === '') {
            $first = 'Platform';
        }
        if ($last === '') {
            $last = 'Admin';
        }

        if ($username === '' || $email === '' || $password === '') {
            $this->stderr("Set ADMIN_USERNAME, ADMIN_EMAIL, and ADMIN_PASSWORD.\n");
            return ExitCode::DATAERR;
        }
        if (strlen($password) < 12 || in_array($password, ['DevOnly-Hub-2026!', 'change-this-password', 'password', 'changeme'], true)) {
            $this->stderr("ADMIN_PASSWORD must be at least 12 characters and must not be a sample password.\n");
            return ExitCode::DATAERR;
        }
        if (User::find()->where(['or', ['username' => $username], ['email' => $email]])->exists()) {
            $this->stdout("That user already exists.\n");
            return ExitCode::OK;
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->first_name = $first;
        $user->last_name = $last;
        $user->role = User::ROLE_SYSTEM;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($password);
        if (!$user->save()) {
            $this->stderr(implode("\n", $user->getFirstErrors()) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("System user {$username} created.\n");
        return ExitCode::OK;
    }
}
