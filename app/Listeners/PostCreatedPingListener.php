<?php

namespace App\Listeners;

use App\Events\PostContentEvent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Log\Logger;

/**
 * Class PostCreatedPingListener
 */
class PostCreatedPingListener
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * PostCreatedPingListener constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->logger = app('logger');
    }

    /**
     * @param PostContentEvent $event
     *
     * @return void
     */
    public function handle(PostContentEvent $event): void
    {
        $newPostUrl = $event->getUrl();

        $notifyUrl = env('POST_CREATED_PING_URL', null);

        // If no notification URL has been set then obviously we can't notify anyone.
        if (null === $newPostUrl || null === $notifyUrl) {
            return;
        }

        try {
            $this->client->post(
                $notifyUrl,
                [
                    'url' => $newPostUrl,
                ]
            );

            $this->logger->info(
                "Notified URL '{notifyUrl}' about new post",
                [
                    'notifyUrl' => $notifyUrl,
                    'newPostUrl' => $newPostUrl,
                ]
            );
        } catch (RequestException $e) {
            $this->logger->error(
                "Unable to notify URL '{notifyUrl}' due to a request exception",
                [
                    'notifyUrl' => $notifyUrl,
                    'newPostUrl' => $newPostUrl,
                    'e' => $e,
                ]
            );
        }
    }
}
