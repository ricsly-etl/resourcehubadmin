<?php

namespace app\commands;

use app\components\HtmlSanitizer;
use app\components\MediaStorage;
use app\components\Slugger;
use app\models\Resource;
use app\models\User;
use Yii;
use yii\console\Controller;

class SelfCheckController extends Controller
{
    public function actionIndex(): int
    {
        $failed = 0;
        $check = function (bool $ok, string $message) use (&$failed): void {
            if ($ok) {
                $this->stdout("ok  {$message}\n");
                return;
            }
            $failed++;
            $this->stderr("FAIL {$message}\n");
        };

        $clean = HtmlSanitizer::clean('<p>Hello</p><script>alert(1)</script><a href="javascript:alert(1)">x</a><img src="https://evil.test/a.png" alt="no"><a href="https://nove-tix.com">site</a>');
        $check(!str_contains($clean, '<script') && !str_contains($clean, 'javascript:'), 'sanitizer strips scripts and javascript links');
        $check(str_contains($clean, 'https://nove-tix.com'), 'sanitizer keeps ordinary links');
        $check(!str_contains($clean, 'evil.test'), 'sanitizer strips foreign images');
        $check(Slugger::slugify('Nairobi Jazz Night!') === 'nairobi-jazz-night', 'slugger builds a readable slug');

        $expired = new User();
        $expired->password_reset_token = (string) time();
        $expired->password_reset_expire = time() - 30;
        $check(User::resetTokenValid($expired) === false, 'reset links expire from password_reset_expire');
        $expired->password_reset_expire = time() + 600;
        $check(User::resetTokenValid($expired) === true, 'a future expiry still accepts the token');

        $admin = User::findOne(['username' => 'admin']);
        $check($admin !== null && !hash_equals(md5('DevOnly-Hub-2026!'), (string) $admin->password_hash), 'stored password is not an MD5 digest');
        $check($admin && $admin->validatePassword((string) getenv('SEED_ADMIN_PASSWORD')), 'admin password verifies with the hasher');

        $check(Resource::find()->section('blog')->publicVisible()->andWhere(['like', 'resources.title', 'backstage'])->exists() === false, 'draft article stays off the public query');
        $check(Resource::find()->section('blog')->publicVisible()->andWhere(['like', 'resources.title', 'not yet published'])->exists() === false, 'future scheduled article stays off the public query');
        $check(Resource::find()->section('blog')->publicVisible()->andWhere(['resources.featured' => 1])->exists(), 'a published featured article is visible');

        $bad = Yii::getAlias('@runtime/not-a-jpeg.jpg');
        file_put_contents($bad, 'this is not a jpeg');
        $rejected = false;
        try {
            MediaStorage::storeLocalFile($bad, 'not-a-jpeg.jpg', MediaStorage::KIND_IMAGE);
        } catch (\RuntimeException $e) {
            $rejected = true;
        }
        $check($rejected, 'a renamed text file is rejected as an image');

        $harbour = Resource::find()->section('discover')->andWhere(['resources.slug' => 'harbour-showcase'])->one();
        $check($harbour && $harbour->event && $harbour->event->isTicketable() === false, 'a cancelled event does not offer tickets');

        if ($failed > 0) {
            $this->stderr("{$failed} check(s) failed.\n");
            return 1;
        }
        $this->stdout("All checks passed.\n");
        return 0;
    }
}
