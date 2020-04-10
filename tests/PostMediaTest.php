<?php

use App\Events\RebuildSiteEvent;
use App\Providers\AbstractProvider;
use Illuminate\Http\UploadedFile;

class PostMediaTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->rrmdir(
            env('BASE_CONTENT_ENTRY_PATH'),
            true
        );
    }

    public function testNewPostUsingFormInput()
    {
        $this->expectsEvents(RebuildSiteEvent::class);

        $this->post(
            '/',
            [
                'h' => 'entry',
                'content' => "I wrote some amazing content here don't you think?",
                'name' => '',
            ]
        );

        // Derived from first 3 words of content above.
        $slug = 'i-wrote-some';

        $now = new \DateTime();

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(201);
        $this->seeHeader('Location', $this->generateUrl($now, $slug));
    }

    public function testNewPostIgnoresUrlsInPostBodyForSlug()
    {
        $this->expectsEvents(RebuildSiteEvent::class);

        $this->post(
            '/',
            [
                'h' => 'entry',
                'content' => '[The Smoker Channels](https://overcast.fm/+B7NCosNIE) is one of my favourite podcast episodes of all time.  Dogs chasing cats.',
            ]
        );

        $expectedSlug = 'the-smoker-channels';

        $now = new \DateTime();

        $this->assertFileExists($this->generateFilename('entry', $now, $expectedSlug));
        $this->assertResponseStatus(201);
        $this->seeHeader('Location', $this->generateUrl($now, $expectedSlug));
    }

    public function testNewPostWithCustomSlugUsingFormInput()
    {
        $this->expectsEvents(RebuildSiteEvent::class);

        $slug = 'sweet-custom-slug';

        $this->post(
            '/',
            [
                'h' => 'entry',
                'content' => "I wrote some amazing content here don't you think?",
                'mp-slug' => $slug,
            ]
        );

        $now = new \DateTime();

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(201);
        $this->seeHeader('Location', $this->generateUrl($now, $slug));
    }

    public function testNewPostWithCustomTitleUsingFormInput()
    {
        $this->expectsEvents(RebuildSiteEvent::class);

        $title = 'My Very Cool Post';
        $slug = 'my-very-cool-post';

        $this->post(
            '/',
            [
                'h' => 'entry',
                'name' => $title,
                'content' => 'This is a lovely test post that demonstrates content.',
            ]
        );

        $now = new \DateTime();

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(201);
        $this->seeHeader('Location', $this->generateUrl($now, $slug));
        $this->assertContains(
            "title: '${title}'",
            file_get_contents(
                $this->generateFilename('entry', $now, $slug)
            )
        );
    }

    public function testNewPostWithImageAndNoText()
    {
        $this->expectsEvents(RebuildSiteEvent::class);

        $this->post(
            '/',
            [
                'h' => 'entry',
                'photo' => 'https://fake.photo/here.jpg',
            ]
        );

        $now = new \DateTime();

        $this->assertFileExists($this->generateFilename('entry', $now, strtolower($now->format('l-jS'))));
    }

    public function testCanUploadImage()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $this->post(
            '/media',
            [
                'file' => $file,
            ]
        );
    }

    private function generateUrl(
        \DateTimeInterface $now,
        string $slug
    ): string {
        return sprintf(
            '%s%d/%d/%s/',
            env('ME_URL'),
            $now->format('Y'),
            $now->format('m'),
            $slug
        );
    }

    private function generateFilename(
        string $type,
        \DateTimeInterface $now,
        string $slug
    ): string {
        return sprintf(
            '%s/%s-%s.md',
            $this->app[AbstractProvider::class]->getContentPathForType($type),
            $now->format('Y-m-d'),
            $slug
        );
    }
}
