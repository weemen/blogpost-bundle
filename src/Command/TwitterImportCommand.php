<?php

namespace Weemen\BlogPostBundle\Command;


use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterImportCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('leonweemen:twitter:import')
            ->setDescription('Imports all tweets from twitter timeline');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Start importing tweets</info>\n");

        $connection = new TwitterOAuth(
            $this->getContainer()->get('twitter_consumerKey'),
            $this->getContainer()->get('twitter_consumerSecret'),
            $this->getContainer()->get('twitter_oauthToken'),
            $this->getContainer()->get('twitter_oauthTokenSecret')
        );

        $content = $connection->get("account/verify_credentials");

        var_dump($connection);
    }
}