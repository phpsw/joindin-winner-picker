<?php

namespace Phpsw\Console\Command;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PickCommand extends Command
{
   /**
    * @var Client
    */
    private $client;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $endDate;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $hosts = [];

    /**
     * @var array
     */
    private $entries = [];

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct() {
        parent::__construct();
        $this->client = new GuzzleClient();
    }

    /**
     * @{inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pick:pick')
            ->setDescription('Pick winners.')
            ->addArgument('tag', InputArgument::REQUIRED, 'The tag to search for.')
            ->addArgument('start', InputArgument::OPTIONAL, '', 'first day of last month')
            ->addArgument('end', InputArgument::OPTIONAL, '', 'last day of this month')
        ;
    }

    /**
     * @{inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Joind.in Winner Picker!');

        $this->tag = $input->getArgument('tag');
        $this->startDate = (new \DateTime($input->getArgument('start')))->format('Y-m-d');
        $this->endDate = (new \DateTime($input->getArgument('end')))->format('Y-m-d');

        $this->io->comment("Selecting from #$this->tag events between $this->startDate and $this->endDate");

        $this->getEvents($output);
        $this->getHosts();
        $this->getEntries($output);
        $this->pickWinner($output);
    }

    /**
     * Get events from Joind.in.
     */
    private function getEvents(OutputInterface $output)
    {
        $response = $this->client->get('http://api.joind.in/v2.1/events',
            ['query' => [
              'tags' => [$this->tag],
              'startdate' => $this->startDate,
              'enddate' => $this->endDate,
              'verbose' => 'yes',
            ]]
        );

        if (!$this->events = json_decode($response->getBody())->events) {
            $output->writeln('<error>No events found.</error>');
            exit(1);
        }
    }

    /**
     * Find the hosts for each event so that they can be excluded later.
     */
    private function getHosts()
    {
        foreach ($this->events as $event) {
            $this->hosts = array_unique(array_merge($this->hosts, array_map(function ($host) { return $host->host_name; }, $event->hosts)));
        }
    }

    /**
     * Get entries from event and talk comments for each event.
     */
    private function getEntries(OutputInterface $output)
    {
        foreach ($this->events as $event) {
            // Event comments.
            $response = $this->client->get($event->comments_uri, ['query' => ['resultsperpage' => 1000]])->getBody();
            $event->event_comments = json_decode($response)->comments;

            // Talk comments.
            $response = $this->client->get($event->all_talk_comments_uri, ['query' => ['resultsperpage' => 1000]])->getBody();
            $event->talk_comments = json_decode($response)->comments;

            $event->comments = array_merge($event->event_comments, $event->talk_comments);

            // Exclude hosts.
            $hosts = $this->hosts;
            $event->entries = array_filter($event->comments, function ($comment) use ($hosts) {
                return !in_array($comment->user_display_name, $hosts);
            });

            $this->io->section($event->name);

            $elements = [];
            foreach ($event->entries as $comment) {
                $elements[] = vsprintf('%s - %s', [
                    $comment->user_display_name,
                    substr(str_replace(PHP_EOL, ' ', $comment->comment ?: '<< no comment >>'), 0, 200),
                ]);
            }

            $this->io->listing($elements);

            $this->entries = array_merge($this->entries, $event->entries);
        }
    }

    /**
     * Pick a winner from the entries.
     */
    private function pickWinner(OutputInterface $output)
    {
        $winner = $this->entries[rand(0, count($this->entries) - 1)];

        $text = '';
        $text .= $winner->user_display_name . ' - ' . substr(str_replace(PHP_EOL, ' ', $winner->comment ?: '<< no comment >>'), 0, 200) . PHP_EOL;
        $text .= ' - ' . isset($winner->uri) ? $winner->uri : $winner->event_uri;

        $this->io->success($text);
    }
}
