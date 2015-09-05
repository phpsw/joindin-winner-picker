#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new GuzzleHttp\Client();

if (count($argv) < 2) {
    echo 'usage: ', $argv[0], ' <tag> [<start date>] [<end date>]', PHP_EOL;
    exit(1);
}

$tag   = $argv[1];
$start = (new DateTime(isset($argv[2]) ? $argv[2] : 'first day of last month'))->format('Y-m-d');
$end   = (new DateTime(isset($argv[3]) ? $argv[3] : 'last day of this month'))->format('Y-m-d');

echo 'Joind.in Winner Picker!', PHP_EOL;
echo 'Selecting from #', $tag, ' events between ', $start, ' and ', $end, PHP_EOL, PHP_EOL;

$response = $client->get('http://api.joind.in/v2.1/events', ['query' => ['tags' => [$tag], 'startdate' => $start, 'enddate' => $end, 'verbose' => 'yes']]);
$events = json_decode($response->getBody())->events;
$entries = [];
$hosts = [];

foreach ($events as $event) {
    $hosts = array_unique(array_merge($hosts, array_map(function ($host) { return $host->host_name; }, $event->hosts)));
}

foreach ($events as $event) {
    $response = $client->get($event->comments_uri, ['query' => ['resultsperpage' => 1000]])->getBody();
    $event->event_comments = json_decode($response)->comments;

    $response = $client->get($event->all_talk_comments_uri, ['query' => ['resultsperpage' => 1000]])->getBody();
    $event->talk_comments = json_decode($response)->comments;

    $event->comments = array_merge($event->event_comments, $event->talk_comments);
    $event->entries = array_filter($event->comments, function ($comment) use ($hosts) {
        return !in_array($comment->user_display_name, $hosts);
    });

    echo $event->name, PHP_EOL;

    foreach ($event->entries as $comment) {
        echo ' - ', $comment->user_display_name, ' - ', substr(str_replace(PHP_EOL, ' ', $comment->comment ?: '<< no comment >>'), 0, 200), PHP_EOL;
    }

    $entries = array_merge($entries, $event->entries);
}

echo PHP_EOL;

$winner = $entries[rand(0, count($entries) - 1)];

echo 'And the winner is...', PHP_EOL;
echo ' - ', $winner->user_display_name, ' - ', substr(str_replace(PHP_EOL, ' ', $winner->comment ?: '<< no comment >>'), 0, 200), PHP_EOL;
echo ' - ', isset($winner->uri) ? $winner->uri : $winner->event_uri, PHP_EOL;
