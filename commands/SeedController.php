<?php

namespace app\commands;

use app\components\MediaStorage;
use app\models\Category;
use app\models\HubEvent;
use app\models\Resource;
use app\models\User;
use Yii;
use yii\console\Controller;

class SeedController extends Controller
{
    public $force = false;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['force']);
    }

    public function actionIndex(): int
    {
        if (YII_ENV !== 'dev') {
            $this->stderr("Sample content is only for YII_ENV=dev.\n");
            return 1;
        }
        if (Resource::find()->exists() && !$this->force) {
            $this->stdout("Content already exists. Run with --force to replace it.\n");
            return 0;
        }
        if ($this->force) {
            $this->clearContent();
        }
        $admin = $this->ensureAdmin();
        $categories = $this->categories();
        $events = $this->events();
        $images = $this->images((int) $admin->id);

        $today = new \DateTimeImmutable('today');
        $stamp = static function (\DateTimeImmutable $time): string {
            return $time->format('Y-m-d H:i:s');
        };

        $this->article($admin, [
            'title' => 'Top music nights to put on your calendar',
            'summary' => 'From courtyard jazz to waterfront stages, these are the nights people in Nairobi keep making room for.',
            'body' => '<p>A good night out starts before the ticket scan. Look at the set times, the way in, and how late the last act finishes.</p><h2>What to look for</h2><p>Outdoor stages need a layer you can tie around your waist. Seated rooms need less, and they reward arriving for the opener.</p><p>If a show is linked from Discover, the ticket button only appears while that event is still on sale.</p>',
            'author_name' => 'NoveTix Editorial',
            'featured' => 1,
            'tags' => 'Music, Events, Nightlife',
            'publish_at' => $stamp($today->modify('-5 days')->setTime(9, 0)),
            'cover' => $images['stage'],
            'alt' => 'Crowd facing a lit stage at night',
        ]);
        $this->article($admin, [
            'title' => 'How to buy event tickets like a pro',
            'summary' => 'A short field guide to tiers, entry, and the details worth checking before you pay.',
            'body' => '<p>Start with the tier name, not the color of the button. Some tickets admit one person. Some admit a group, and the door counts each arrival.</p><h2>Before you pay</h2><ul><li>Check the start time and the entry note.</li><li>Use the phone number that will be at the door.</li><li>Keep the ticket page available offline if the venue network is weak.</li></ul>',
            'author_name' => 'NoveTix Editorial',
            'tags' => 'Events, Tips',
            'publish_at' => $stamp($today->modify('-2 days')->setTime(11, 0)),
            'cover' => $images['tickets'],
            'alt' => 'Hands holding phones at an event entrance',
        ]);
        $this->article($admin, [
            'title' => 'Weekend resets within reach of the city',
            'summary' => 'Three easy escapes when you want a Saturday that does not start in traffic.',
            'body' => '<p>Naivasha, the ridge trails, and a quiet coastal morning each ask for a different bag. Pack for the actual plan, not the photograph.</p>',
            'author_name' => 'NoveTix Editorial',
            'tags' => 'Lifestyle, Travel',
            'publish_at' => $stamp($today->modify('-12 days')->setTime(8, 30)),
            'cover' => $images['coast'],
            'alt' => 'Coastal path above the water',
        ]);
        $this->article($admin, [
            'title' => 'Live shows worth leaving the house for',
            'summary' => 'Rooms, rooftops, and one seated theatre night that still has seats.',
            'body' => '<p>Smaller rooms are the ones that sell out on a Thursday. If the listing is on NoveTix, open the event page rather than guessing the door policy.</p>',
            'author_name' => 'NoveTix Editorial',
            'tags' => 'Entertainment, Music',
            'publish_at' => $stamp($today->modify('-8 days')->setTime(16, 0)),
            'cover' => $images['lights'],
            'alt' => 'Stage lights over a small audience',
        ]);
        $this->article($admin, [
            'title' => 'Notes from a backstage walk-through',
            'summary' => 'This draft is not public.',
            'body' => '<p>Internal notes stay off the public site until someone publishes them.</p>',
            'status' => 'draft',
            'tags' => 'Events',
            'cover' => $images['notes'],
            'alt' => 'Notebook on a table',
        ]);
        $this->article($admin, [
            'title' => 'New year planning, not yet published',
            'summary' => 'Scheduled for later. Visitors should not see this yet.',
            'body' => '<p>This article is waiting for its publish time.</p>',
            'status' => 'scheduled',
            'publish_at' => $stamp($today->modify('+30 days')->setTime(8, 0)),
            'tags' => 'Events',
            'cover' => $images['notes'],
            'alt' => 'Calendar page',
        ]);

        $this->guide($admin, $categories['checklists'], [
            'title' => 'Event-day checklist for hosts',
            'summary' => 'A one-page run of show you can open on your phone at the door.',
            'featured' => 1,
            'body' => '<p>Print is optional. The point is that every owner knows the next hour.</p><h2>Morning</h2><p>Confirm power, water, and the name on the door list.</p><h2>Doors</h2><p>Brief the scanners on group tickets. A ticket can stay valid until every admission has been used.</p><h2>After close</h2><p>Count the unscanned names before you leave the venue.</p>',
            'tags' => 'Event Planning, Checklists',
            'cover' => $images['checklist'],
            'alt' => 'Printed checklist beside a cup',
        ]);
        $this->guide($admin, $categories['tickets'], [
            'title' => 'How to choose a ticket type',
            'summary' => 'General admission, seated, and group entry, in plain language.',
            'body' => '<p>The type is a promise about entry, not a decoration.</p><h2>General admission</h2><p>You choose where to stand. Arrive earlier than the headline if the room is small.</p><h2>Group entry</h2><p>One purchase can admit more than one person. Each arrival is counted at the door.</p>',
            'tags' => 'Ticket Buying',
            'cover' => $images['tickets'],
            'alt' => 'Ticket confirmation on a phone',
        ]);
        $this->guide($admin, $categories['planning'], [
            'title' => 'A six-week planning timeline',
            'summary' => 'What actually has to be true six weeks out, three weeks out, and the day before.',
            'body' => '<h2>Six weeks</h2><p>Date, venue hold, and a draft capacity.</p><h2>Three weeks</h2><p>Ticket tiers live, and the public description matches the door plan.</p><h2>The day before</h2><p>Crew list, scanner logins, and a paper fallback.</p>',
            'tags' => 'Event Planning, How-to',
            'cover' => $images['plan'],
            'alt' => 'Desk with a planning notebook',
        ]);
        $pdf = $this->writePdf(Yii::getAlias('@runtime/seed-arrival.pdf'), 'What to bring', [
            'A charged phone and the ticket page.',
            'The name that matches the booking.',
            'A layer for outdoor venues.',
            'Patience at the door. Group tickets are counted one by one.',
        ]);
        $doc = MediaStorage::storeLocalFile($pdf, 'what-to-bring.pdf', MediaStorage::KIND_DOCUMENT, [
            'created_by' => $admin->id,
            'alt_text' => '',
        ]);
        $arrival = $this->guide($admin, $categories['attendee'], [
            'title' => 'What to bring to an outdoor show',
            'summary' => 'A short document you can read in the page. It is not offered as a download.',
            'format' => 'document',
            'document_asset_id' => $doc->id,
            'body' => '',
            'tags' => 'Attendee Tips',
            'cover' => $images['coast'],
            'alt' => 'Open air venue before doors',
        ]);
        unset($arrival);

        $galleryIds = [];
        foreach (['stage' => 'Main stage', 'lights' => 'Lights', 'crowd' => 'Crowd'] as $key => $caption) {
            $copy = MediaStorage::storeLocalFile($images[$key], $key . '.png', MediaStorage::KIND_IMAGE, [
                'created_by' => $admin->id,
                'alt_text' => $caption . ' photograph',
                'caption' => $caption,
            ]);
            $galleryIds[] = $copy->id;
        }
        $gallery = new Resource();
        $gallery->section = Resource::SECTION_MEDIA;
        $gallery->title = 'Night stage, in three frames';
        $gallery->summary = 'A small gallery from a Nairobi stage. Open it to move through the frames.';
        $gallery->body = '<p>These frames are previews. The original upload stays in private storage.</p>';
        $gallery->status = 'published';
        $gallery->featured = 1;
        $gallery->media_format = 'gallery';
        $gallery->cover_asset_id = $galleryIds[0];
        $gallery->author_name = 'NoveTix Media';
        $gallery->tagInput = 'Music, Events';
        $gallery->syncTags = true;
        $gallery->created_by = $admin->id;
        $gallery->publish_at = $stamp($today->modify('-1 day')->setTime(18, 0));
        $gallery->published_at = $gallery->publish_at;
        $this->mustSave($gallery);
        $gallery->replaceGallery($galleryIds);

        $wav = Yii::getAlias('@runtime/seed-tone.wav');
        $this->writeWav($wav);
        $audioFile = MediaStorage::storeLocalFile($wav, 'room-tone.wav', MediaStorage::KIND_AUDIO, ['created_by' => $admin->id]);
        $audio = new Resource();
        $audio->section = Resource::SECTION_MEDIA;
        $audio->title = 'A two-minute room check';
        $audio->summary = 'A short audio check used to confirm the player. There is no download control.';
        $audio->body = '<p>Press play. The control shows position and length. Saving the file is not part of the page.</p>';
        $audio->status = 'published';
        $audio->media_format = 'audio';
        $audio->interview = 1;
        $audio->media_asset_id = $audioFile->id;
        $audio->cover_asset_id = $this->storeImage($images['notes'], 'Audio cover', (int) $admin->id);
        $audio->author_name = 'NoveTix Media';
        $audio->tagInput = 'Interviews';
        $audio->syncTags = true;
        $audio->created_by = $admin->id;
        $audio->publish_at = $stamp($today->modify('-3 days')->setTime(12, 0));
        $audio->published_at = $audio->publish_at;
        $this->mustSave($audio);

        $videoId = $this->maybeVideo((int) $admin->id);
        if ($videoId) {
            $video = new Resource();
            $video->section = Resource::SECTION_MEDIA;
            $video->title = 'Ten seconds of stage light';
            $video->summary = 'A generated clip so the player can be tried locally.';
            $video->body = '<p>Standard controls. No download button.</p>';
            $video->status = 'published';
            $video->media_format = 'video';
            $video->media_asset_id = $videoId;
            $video->poster_asset_id = $this->storeImage($images['lights'], 'Video poster', (int) $admin->id);
            $video->cover_asset_id = $video->poster_asset_id;
            $video->tagInput = 'Events';
            $video->syncTags = true;
            $video->created_by = $admin->id;
            $video->publish_at = $stamp($today->setTime(10, 0));
            $video->published_at = $video->publish_at;
            $this->mustSave($video);
        }

        $this->discover($admin, [
            'title' => 'A ridge walk before the city wakes',
            'summary' => 'An early trail with a wide view, best before the heat settles in.',
            'discover_type' => 'experience',
            'city' => 'Nairobi',
            'location_label' => 'Ngong hills trailhead',
            'body' => '<p>Go early, take water, and leave the summit before the crowds make the path a queue.</p>',
            'featured' => 1,
            'tags' => 'Experiences, Outdoors',
            'cover' => $images['ridge'],
            'alt' => 'Ridge path in the morning',
        ]);
        $this->discover($admin, [
            'title' => 'A neighbourhood for a slow afternoon',
            'summary' => 'Studios, a courtyard, and coffee that does not hurry you.',
            'discover_type' => 'local',
            'city' => 'Nairobi',
            'location_label' => 'Kilimani courtyards',
            'body' => '<p>This is a place, not a ticket. Walk it. Nothing here is a checkout.</p>',
            'tags' => 'Culture, Food',
            'cover' => $images['courtyard'],
            'alt' => 'Courtyard tables',
        ]);
        $this->discover($admin, [
            'title' => 'Friday lights at the square',
            'summary' => 'Tonight’s linked event. The ticket button opens the event page.',
            'discover_type' => 'event',
            'event_id' => $events['tonight']->id,
            'body' => '<p>Doors, set times, and tickets live on the event page.</p>',
            'tags' => 'Music, Nightlife',
            'cover' => $images['stage'],
            'alt' => 'Evening square before the show',
        ]);
        $this->discover($admin, [
            'title' => 'Karura picnic session',
            'summary' => 'A Saturday afternoon set among the trees.',
            'discover_type' => 'event',
            'event_id' => $events['weekend']->id,
            'body' => '<p>Blankets are welcome. Glass is not.</p>',
            'tags' => 'Music, Outdoors',
            'cover' => $images['park'],
            'alt' => 'Trees in a city park',
        ]);
        $this->discover($admin, [
            'title' => 'Mombasa night market',
            'summary' => 'Food stalls and a late set, a week and a bit from now.',
            'discover_type' => 'event',
            'event_id' => $events['later']->id,
            'body' => '<p>The linked event carries the date. This page does not invent a second clock.</p>',
            'tags' => 'Food, Travel',
            'cover' => $images['market'],
            'alt' => 'Evening market stalls',
        ]);
        $this->discover($admin, [
            'title' => 'Harbour showcase',
            'summary' => 'This event was cancelled. The page must not offer tickets.',
            'discover_type' => 'event',
            'event_id' => $events['cancelled']->id,
            'body' => '<p>The status comes from the event link, not from a hopeful button.</p>',
            'tags' => 'Events',
            'cover' => $images['harbour'],
            'alt' => 'Quiet harbour at dusk',
        ]);
        $this->discover($admin, [
            'title' => 'City festival season',
            'summary' => 'A prompt for the month, not a single ticket.',
            'discover_type' => 'inspiration',
            'city' => 'Nairobi',
            'body' => '<p>Use it to browse. When a real event is attached elsewhere, that page is the one that sells.</p>',
            'featured' => 1,
            'tags' => 'Events, Culture',
            'cover' => $images['lights'],
            'alt' => 'Festival lights',
        ]);

        $this->stdout("Seeded.\n");
        $this->stdout("Admin username: {$admin->username}\n");
        $this->stdout("Admin email: {$admin->email}\n");
        if (getenv('SEED_ADMIN_PASSWORD')) {
            $this->stdout("Password: the SEED_ADMIN_PASSWORD value from the environment.\n");
        }
        return 0;
    }

    private function ensureAdmin(): User
    {
        $user = User::findOne(['username' => 'admin']);
        if ($user) {
            return $user;
        }
        $password = getenv('SEED_ADMIN_PASSWORD') ?: Yii::$app->security->generateRandomString(16);
        $user = new User();
        $user->username = 'admin';
        $user->email = 'admin@novetix.local';
        $user->first_name = 'Platform';
        $user->last_name = 'Admin';
        $user->phone_no = '+254700000000';
        $user->role = User::ROLE_SYSTEM;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($password);
        $this->mustSave($user);
        if (!getenv('SEED_ADMIN_PASSWORD')) {
            $file = Yii::getAlias('@runtime/seed-admin.txt');
            file_put_contents($file, $password);
            $this->stdout("Generated password written to runtime/seed-admin.txt\n");
        }
        return $user;
    }

    private function categories(): array
    {
        $rows = [
            'planning' => ['Event Planning', 'Planning timelines and venue preparation.', 1],
            'tickets' => ['Ticket Buying', 'How to choose a ticket and prepare for entry.', 2],
            'checklists' => ['Checklists', 'Lists you can reuse.', 3],
            'howto' => ['How-to', 'Step-by-step notes.', 4],
            'attendee' => ['Attendee Tips', 'What to bring and how to arrive.', 5],
        ];
        $out = [];
        foreach ($rows as $key => [$name, $description, $order]) {
            $slug = strtolower(str_replace(' ', '-', $name));
            $category = Category::findOne(['section' => Resource::SECTION_GUIDE, 'slug' => \app\components\Slugger::slugify($name)]) ?: new Category();
            $category->section = Resource::SECTION_GUIDE;
            $category->name = $name;
            $category->slug = \app\components\Slugger::slugify($name);
            $category->description = $description;
            $category->sort_order = $order;
            $this->mustSave($category);
            $out[$key] = $category;
        }
        return $out;
    }

    private function events(): array
    {
        $today = new \DateTimeImmutable('today');
        $dow = (int) $today->format('N');
        $saturday = $dow === 6 ? $today : ($dow === 7 ? $today->modify('-1 day') : $today->modify('+' . (6 - $dow) . ' days'));
        $specs = [
            'tonight' => ['Friday lights at the square', 'music', $today->setTime(19, 0), 'published', 'Nairobi', 'Central square'],
            'weekend' => ['Karura picnic session', 'music', $saturday->setTime(14, 0), 'published', 'Nairobi', 'Karura gate'],
            'later' => ['Mombasa night market', 'food', $today->modify('+10 days')->setTime(18, 0), 'published', 'Mombasa', 'Old town market'],
            'cancelled' => ['Harbour showcase', 'arts', $today->modify('+4 days')->setTime(20, 0), 'cancelled', 'Mombasa', 'Harbour stage'],
            'past' => ['Last Sunday session', 'music', $today->modify('-4 days')->setTime(17, 0), 'published', 'Nairobi', 'Courtyard'],
        ];
        $base = getenv('NOVETIX_WEB_URL') ?: 'https://nove-tix.com';
        $out = [];
        foreach ($specs as $key => [$title, $type, $start, $status, $city, $venue]) {
            $slug = \app\components\Slugger::slugify($title);
            $event = HubEvent::findOne(['slug' => $slug]) ?: new HubEvent();
            $event->title = $title;
            $event->slug = $slug;
            $event->event_type = $type;
            $event->starts_at = $start->format('Y-m-d H:i:s');
            $event->ends_at = $start->modify('+3 hours')->format('Y-m-d H:i:s');
            $event->status = $status;
            $event->city = $city;
            $event->venue = $venue;
            $event->url = rtrim($base, '/') . '/' . $slug;
            $event->external_ref = 'seed-' . $key;
            $this->mustSave($event);
            $out[$key] = $event;
        }
        return $out;
    }

    private function images(int $userId): array
    {
        $specs = [
            'stage' => [18, 70, 150],
            'tickets' => [20, 110, 80],
            'coast' => [14, 120, 150],
            'lights' => [90, 40, 140],
            'notes' => [120, 90, 40],
            'checklist' => [30, 90, 50],
            'plan' => [40, 70, 120],
            'crowd' => [160, 80, 30],
            'ridge' => [70, 110, 60],
            'courtyard' => [140, 100, 60],
            'park' => [30, 120, 70],
            'market' => [170, 90, 40],
            'harbour' => [20, 60, 110],
        ];
        $paths = [];
        foreach ($specs as $name => [$r, $g, $b]) {
            $path = Yii::getAlias('@runtime/seed-' . $name . '.png');
            $image = imagecreatetruecolor(1200, 800);
            for ($y = 0; $y < 800; $y += 2) {
                $shade = (int) ($y / 800 * 50);
                $color = imagecolorallocate($image, max(0, $r - $shade), max(0, $g - $shade), max(0, $b - $shade));
                imagefilledrectangle($image, 0, $y, 1199, $y + 1, $color);
            }
            $ink = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 5, 48, 48, $name, $ink);
            imagepng($image, $path);
            imagedestroy($image);
            $paths[$name] = $path;
        }
        return $paths;
    }

    private function storeImage(string $path, string $alt, int $userId): int
    {
        $asset = MediaStorage::storeLocalFile($path, basename($path), MediaStorage::KIND_IMAGE, [
            'created_by' => $userId,
            'alt_text' => $alt,
        ]);
        return (int) $asset->id;
    }

    private function article(User $admin, array $spec): void
    {
        $model = new Resource();
        $model->section = Resource::SECTION_BLOG;
        $model->title = $spec['title'];
        $model->summary = $spec['summary'];
        $model->body = $spec['body'];
        $model->status = $spec['status'] ?? 'published';
        $model->featured = !empty($spec['featured']) ? 1 : 0;
        $model->author_name = $spec['author_name'] ?? $admin->getName();
        $model->tagInput = $spec['tags'];
        $model->syncTags = true;
        $model->created_by = $admin->id;
        $model->cover_asset_id = $this->storeImage($spec['cover'], $spec['alt'], (int) $admin->id);
        if (isset($spec['publish_at'])) {
            $model->publish_at = $spec['publish_at'];
            if ($model->status === 'published') {
                $model->published_at = $spec['publish_at'];
            }
        }
        $this->mustSave($model);
    }

    private function guide(User $admin, Category $category, array $spec): Resource
    {
        $model = new Resource();
        $model->section = Resource::SECTION_GUIDE;
        $model->title = $spec['title'];
        $model->summary = $spec['summary'];
        $model->body = $spec['body'];
        $model->status = 'published';
        $model->featured = !empty($spec['featured']) ? 1 : 0;
        $model->guide_format = $spec['format'] ?? 'html';
        $model->toc_enabled = $model->guide_format === 'html' ? 1 : 0;
        $model->category_id = $category->id;
        $model->document_asset_id = $spec['document_asset_id'] ?? null;
        $model->tagInput = $spec['tags'];
        $model->syncTags = true;
        $model->created_by = $admin->id;
        $model->author_name = 'NoveTix Guides';
        $model->cover_asset_id = $this->storeImage($spec['cover'], $spec['alt'], (int) $admin->id);
        $model->publish_at = (new \DateTimeImmutable('yesterday'))->format('Y-m-d H:i:s');
        $model->published_at = $model->publish_at;
        $this->mustSave($model);
        return $model;
    }

    private function discover(User $admin, array $spec): void
    {
        $model = new Resource();
        $model->section = Resource::SECTION_DISCOVER;
        $model->title = $spec['title'];
        $model->summary = $spec['summary'];
        $model->body = $spec['body'];
        $model->status = 'published';
        $model->featured = !empty($spec['featured']) ? 1 : 0;
        $model->discover_type = $spec['discover_type'];
        $model->city = $spec['city'] ?? null;
        $model->location_label = $spec['location_label'] ?? null;
        $model->event_id = $spec['event_id'] ?? null;
        $model->tagInput = $spec['tags'];
        $model->syncTags = true;
        $model->created_by = $admin->id;
        $model->cover_asset_id = $this->storeImage($spec['cover'], $spec['alt'], (int) $admin->id);
        $model->publish_at = (new \DateTimeImmutable('yesterday'))->format('Y-m-d H:i:s');
        $model->published_at = $model->publish_at;
        $this->mustSave($model);
    }

    private function maybeVideo(int $userId): ?int
    {
        $ffmpeg = trim((string) shell_exec('command -v ffmpeg 2>/dev/null'));
        if ($ffmpeg === '') {
            $this->stdout("ffmpeg is not installed, so no sample video was created.\n");
            return null;
        }
        $path = Yii::getAlias('@runtime/seed-stage.mp4');
        $command = escapeshellcmd($ffmpeg) . ' -y -f lavfi -i color=c=0x1E90FF:s=640x360:d=2 -pix_fmt yuv420p ' . escapeshellarg($path) . ' 2>/dev/null';
        exec($command, $output, $code);
        if ($code !== 0 || !is_file($path)) {
            $this->stdout("Could not generate a sample video.\n");
            return null;
        }
        $asset = MediaStorage::storeLocalFile($path, 'stage.mp4', MediaStorage::KIND_VIDEO, ['created_by' => $userId]);
        return (int) $asset->id;
    }

    private function writePdf(string $path, string $title, array $lines): string
    {
        $escape = static function (string $text): string {
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], preg_replace('/[^\x20-\x7E]/', '', $text) ?? '');
        };
        $stream = "BT /F1 18 Tf 72 740 Td (" . $escape($title) . ") Tj /F1 12 Tf\n";
        foreach ($lines as $line) {
            $stream .= "0 -22 Td (" . $escape($line) . ") Tj\n";
        }
        $stream .= 'ET';
        $objects = [
            "<< /Type /Catalog /Pages 2 0 R >>",
            "<< /Type /Pages /Count 1 /Kids [3 0 R] >>",
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
            "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        file_put_contents($path, $pdf);
        return $path;
    }

    private function writeWav(string $path): void
    {
        $rate = 8000;
        $samples = (int) ($rate * 1.2);
        $data = '';
        for ($i = 0; $i < $samples; $i++) {
            $value = (int) (sin(2 * M_PI * 440 * $i / $rate) * 8000);
            $data .= pack('s', max(-32767, min(32767, $value)));
        }
        $header = 'RIFF' . pack('V', 36 + strlen($data)) . 'WAVEfmt ' . pack('V', 16) . pack('v', 1) . pack('v', 1)
            . pack('V', $rate) . pack('V', $rate * 2) . pack('v', 2) . pack('v', 16) . 'data' . pack('V', strlen($data));
        file_put_contents($path, $header . $data);
    }

    private function mustSave($model): void
    {
        if ($model->save()) {
            return;
        }
        $name = $model->title ?? $model->username ?? $model::class;
        throw new \RuntimeException('Seed failed for ' . $name . ': ' . json_encode($model->getFirstErrors()));
    }

    private function clearContent(): void
    {
        $db = Yii::$app->db;
        $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        foreach (['slug_redirects', 'resource_assets', 'resource_tags', 'resources', 'media_assets', 'hub_events', 'categories', 'tags', 'audit_logs'] as $table) {
            $db->createCommand()->delete('{{%' . $table . '}}')->execute();
        }
        $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
        $root = Yii::getAlias('@app/storage/private');
        if (is_dir($root)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->getFilename() === '.gitkeep') {
                    continue;
                }
                $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
            }
        }
    }
}
