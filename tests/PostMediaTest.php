<?php

use App\Events\PostContentEvent;
use App\Providers\AbstractProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

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
        Event::fake();

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

        Event::assertDispatched(PostContentEvent::class);

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeHeader('Location', $this->generateUrl($now, $slug));
    }

    public function testNewPostIgnoresUrlsInPostBodyForSlug()
    {
        Event::fake();

        $this->post(
            '/',
            [
                'h' => 'entry',
                'content' => '[The Smoker Channels](https://overcast.fm/+B7NCosNIE) is one of my favourite podcast episodes of all time.  Dogs chasing cats.',
            ]
        );

        $expectedSlug = 'the-smoker-channels';

        $now = new \DateTime();

        Event::assertDispatched(PostContentEvent::class);

        $this->assertFileExists($this->generateFilename('entry', $now, $expectedSlug));
        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeHeader('Location', $this->generateUrl($now, $expectedSlug));
    }

    public function testNewPostWithCustomSlugUsingFormInput()
    {
        Event::fake();

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

        Event::assertDispatched(PostContentEvent::class);

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeHeader('Location', $this->generateUrl($now, $slug));
    }

    public function testNewPostWithCustomTitleUsingFormInput()
    {
        Event::fake();

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

        Event::assertDispatched(PostContentEvent::class);

        $this->assertFileExists($this->generateFilename('entry', $now, $slug));
        $this->assertResponseStatus(Response::HTTP_CREATED);
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
        Event::fake();

        $this->post(
            '/',
            [
                'h' => 'entry',
                'photo' => 'https://fake.photo/here.jpg',
            ]
        );

        $now = new \DateTime();

        Event::assertDispatched(PostContentEvent::class);

        $this->assertFileExists($this->generateFilename('entry', $now, strtolower($now->format('l-jS'))));
    }

    public function testCanUploadImage()
    {
        Event::fake();

        $file = UploadedFile::fake()->image('test.jpg');

        $this->call('POST', '/media', [], [], ['file' => $file], []);

        $this->assertResponseStatus(Response::HTTP_CREATED);
        Storage::disk('local')->assertExists('2019/'.$file->hashName().'.original');
        Storage::disk('local')->assertExists('2019/'.$file->hashName());
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
