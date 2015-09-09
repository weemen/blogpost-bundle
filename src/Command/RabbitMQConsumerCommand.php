<?php

namespace Weemen\BlogPostBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RabbitMQConsumerCommand extends ContainerAwareCommand
{

    private $loop;
    private $client;


    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;


    public function __construct()
    {
        parent::__construct();
        $this->loop     = null;
        $this->client   = null;


    }

    protected function configure()
    {
        $this
            ->setName('leonweemen:blog:consume')
            ->setDescription('Starts consuming messages from mq');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Start cosuming messages</info>\n");
    }


}