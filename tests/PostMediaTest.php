<?php

use App\Contracts\BlogProvider;
use App\Events\RebuildSiteEvent;
use Illuminate\Support\Facades\Event;

class PostMediaTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->rrmdir(
            env('BASE_CONTENT_ENTRY_PATH'),
            true
        );
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNewPostUsingFormInput()
    {
        Event::fake();

        $this->post(
            '/',
            [
                'h' => 'entry',
                'content' => "I wrote some amazing content here don't you think?"
            ]
        );

        // Derived from first 3 words of content above.
        $slug = 'i-wrote-some';

        $now = new \DateTime();

        Event::assertDispatched(RebuildSiteEvent::class);

        $this->assertFileExists(
            sprintf(
                '%s/%s-%s.md',
                $this->app[BlogProvider::class]->getContentPathForType('entry'),
                $now->format('Y-m-d'),
                $slug
            )
        );

        $this->assertResponseStatus(201);
        $this->seeHeader(
            'Location',
            sprintf(
                '%s%d/%02d/%s/',
                env('ME_URL'),
                $now->format('Y'),
                $now->format('m'),
                $slug
            )
        );
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

        Event::assertDispatched(RebuildSiteEvent::class);

        $this->assertFileExists(
            sprintf(
                '%s/%s-%s.md',
                $this->app[BlogProvider::class]->getContentPathForType('entry'),
                $now->format('Y-m-d'),
                $slug
            )
        );

        $this->assertResponseStatus(201);
        $this->seeHeader(
            'Location',
            sprintf(
                '%s%d/%02d/%s/',
                env('ME_URL'),
                $now->format('Y'),
                $now->format('m'),
                $slug
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

        Event::assertDispatched(RebuildSiteEvent::class);

        $this->assertFileExists(
            sprintf(
                '%s/%s-%s.md',
                $this->app[BlogProvider::class]->getContentPathForType('entry'),
                $now->format('Y-m-d'),
                strtolower($now->format('l-jS'))
            )
        );
    }
}
