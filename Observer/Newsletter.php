<?php

declare(strict_types=1);

interface NewsAgencyInterface
{
    public function notify(string $message): void;
    public function addSubscriber(NewsSubscriberInterface $subscriber): void;
    public function removeSubscriber(NewsSubscriberInterface $subscriber): void;
}

interface NewsSubscriberInterface
{
    public function getNews(string $message): void;
}

class NewsAgency implements NewsAgencyInterface
{
    private array $subscribers = [];
    public function notify(string $message): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->getNews($message);
        }
    }

    public function addSubscriber(NewsSubscriberInterface $subscriber): void
    {
        $this->subscribers[spl_object_hash($subscriber)] = $subscriber;
    }

    public function removeSubscriber(NewsSubscriberInterface $subscriber): void
    {
        unset($this->subscribers[spl_object_hash($subscriber)]);
    }
}

class SmsSubscriber implements NewsSubscriberInterface
{
    public function getNews(string $message): void
    {
        echo "Sending news via SMS: {$message} <br>";
    }
}

class EmailSubscriber implements NewsSubscriberInterface
{
    public function getNews(string $message): void
    {
        echo "Sending news via email: {$message} <br>";
    }
}

class WebhookSubscriber implements NewsSubscriberInterface
{
    public function getNews(string $message): void
    {
        echo "Sending news via webhook: {$message} <br>";
    }
}

$agency = new NewsAgency();
$smsSub = new SmsSubscriber();
$emailSub = new EmailSubscriber();
$webhookSub = new WebhookSubscriber();
$agency->addSubscriber($smsSub);
$agency->addSubscriber($emailSub);
$agency->addSubscriber($webhookSub);
$agency->notify('Breaking News! All three subscribers get the news!');
$agency->removeSubscriber($smsSub);
$agency->notify("Another breaking news! Only two subscribers left!");