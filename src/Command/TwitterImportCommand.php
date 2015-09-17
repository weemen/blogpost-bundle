<?php

namespace Weemen\BlogPostBundle\Command;


use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;

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
            $this->getContainer()->getParameter('twitter_consumerKey'),
            $this->getContainer()->getParameter('twitter_consumerSecret'),
            $this->getContainer()->getParameter('twitter_oauthToken'),
            $this->getContainer()->getParameter('twitter_oauthTokenSecret')
        );

        $this->verifyAccountCredentials($connection);
        $this->importTweets($connection);
    }

    protected function verifyAccountCredentials(TwitterOAuth $connection)
    {
        $connection->get("account/verify_credentials");

        if (200 !== $connection->getLastHttpCode()) {
            throw new \RuntimeException("Incorrect account credentials");
        }
    }

    protected function importTweets(TwitterOAuth $connection)
    {
        $tweets = $connection->get("statuses/user_timeline", array("count" => 100,"exclude_replies" => true));

        foreach ($tweets as $tweet) {

            $publishDate = \DateTime::createFromFormat("D M d H:i:s O Y", $tweet->created_at);


            $hashTagsFromTweet = $tweet->entities->hashtags;
            $hashTags          = array();
            foreach ($hashTagsFromTweet as $hashtagObj) {
                $hashTags[] = "#".$hashtagObj->text;
            }

            $title = "News From Twitter";
            if (!empty($hashTags)) {
                $title = implode(", ",$hashTags);
            }

            $author = "Leon Weemen (@leonweemen)";
            if ($tweet->retweeted) {
                $author = $tweet->retweeted_status->user->name . " (@" . $tweet->retweeted_status->user->screen_name . ")";
            }

            $blogPost = array(
                "blog_post" => array (
                    "title"     => $title,
                    "content"   => $tweet->text,
                    "author"    => $author,
                    "published" => true,
                    "source"    => strip_tags($tweet->source),
                    "publishDate" => $publishDate->format("Y-m-d H:i:s")
                )
            );
            $this->recordTweet(json_encode($blogPost, JSON_PRETTY_PRINT));
        }
    }

    protected function recordTweet($payload)
    {
        $url = 'http://dev.leonweemen.nl/app_dev.php/blog/post';

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => $payload,
            ),
        );

        $context  = stream_context_create($options);
        $result   = file_get_contents($url, false, $context);
    }
}