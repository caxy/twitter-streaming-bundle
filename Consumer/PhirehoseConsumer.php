<?php
namespace Bangpound\Bundle\TwitterStreamingBundle\Consumer;

use Bangpound\Atom\DataBundle\CouchDocument\CategoryType;
use Bangpound\Atom\DataBundle\CouchDocument\ContentType;
use Bangpound\Atom\DataBundle\CouchDocument\LinkType;
use Bangpound\Atom\DataBundle\CouchDocument\PersonType;
use Bangpound\Atom\DataBundle\CouchDocument\SourceType;
use Bangpound\Atom\DataBundle\CouchDocument\TextType;
use Bangpound\Twitter\DataBundle\Entity\Tweet;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\CouchDB\Attachment;
use Doctrine\ODM\CouchDB\Types\BooleanType;
use Doctrine\ODM\CouchDB\Types\MixedType;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Rshief\PubsubBundle\CouchDocument\AtomEntry;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Model\Message;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class PhirehoseConsumer
 * @package Bangpound\Bundle\TwitterStreamingBundle\Consumer
 */
class PhirehoseConsumer implements ConsumerInterface, LoggerAwareInterface
{

    private $objectManager;
    private $serializer;
    private $jsonOptions;
    private $maxUnitOfWorkSize;
    private $atomEntryClass;
    private $logger;
    private $count;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param $atomEntryClass
     * @param int $maxUnitOfWorkSize
     */
    public function __construct(ObjectManager $objectManager, SerializerInterface $serializer, $atomEntryClass, $maxUnitOfWorkSize = 100)
    {
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->atomEntryClass = $atomEntryClass;
        $this->maxUnitOfWorkSize = $maxUnitOfWorkSize;
        $this->count = 0;
        $this->jsonOptions = (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0', '>=')) ? JSON_BIGINT_AS_STRING : 0;
        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * Sets a logger instance on the object
     *
     * @param  LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        pcntl_signal(SIGINT, [ $this, 'signalHandler' ]);
        pcntl_signal(SIGHUP, [ $this, 'signalHandler' ]);
        pcntl_signal(SIGTERM, [ $this, 'signalHandler' ]);

        /* @var $message Message */
        $message = $event->getMessage();

        /** @var \Doctrine\Common\Persistence\ObjectRepository $repository */
        $repository = $this->objectManager->getRepository($this->atomEntryClass);

        $data = json_decode($message->getValue('tweet'), true, 512, $this->jsonOptions);

        $created_at = \DateTime::createFromFormat('D M j H:i:s P Y', $data['created_at']);
        $tweet_path = $data['user']['screen_name'].'/status/'. $data['id_str'];

        $id = 'tag:twitter.com,'. $created_at->format('Y-m-d') .':/'. $tweet_path;

        $entry = new $this->atomEntryClass;
        $entry->setId($id);
        $entry->setOriginalData($message->getValue('tweet'), 'application/json');

        $title = new TextType();
        $title->setText($data['text']);
        $entry->setTitle($title);

        $content = new ContentType();
        $content->setContent($data['text']);
        $entry->setContent($content);

        $author = new PersonType();
        $author->setName($data['user']['name']);
        $author->setUri($data['user']['url']);
        $entry->addAuthor($author);

        $link = new LinkType();
        $link->setHref('https://twitter.com/intent/user?user_id='. $data['user']['id_str']);
        $link->setRel('author');
        $entry->addLink($link);

        if (isset($data['entities']['hashtags'])) {
            foreach ($data['entities']['hashtags'] as $hashtag) {
                $category = new CategoryType();
                $category->setTerm($hashtag['text']);
                $entry->addCategory($category);
            }
        }

        if (isset($data['entities']['urls'])) {
            foreach ($data['entities']['urls'] as $url) {
                $link = new LinkType();
                $link->setHref($url['expanded_url']);
                if (substr_compare($url['expanded_url'], $url['display_url'], -strlen($url['display_url']), strlen($url['display_url'])) === 0) {
                    $link->setRel('shortlink');
                }
                else {
                    $link->setRel('nofollow');
                }
                $entry->addLink($link);
            }
        }

        if (isset($data['entities']['media'])) {
            foreach ($data['entities']['media'] as $media) {
                $link = new LinkType();
                $link->setHref($media['media_url']);
                $link->setRel('enclosure');
                if ($media['type'] == 'photo') {
                    $link->setType('image');
                }
                $link->setType('image');
                $entry->addLink($link);
            }
        }

        $link = new LinkType();
        $link->setHref('http://twitter.com/'.$tweet_path);
        $link->setRel('canonical');
        $entry->addLink($link);

        $link = new LinkType();
        $link->setHref(strtr($data['user']['profile_image_url'], ['_normal' => '']));
        $link->setRel('author thumbnail');
        $entry->addLink($link);

        $entry->setPublished($created_at);

        $source = new SourceType();
        $title = new TextType();
        $title->setText('Twitter');
        $source->setTitle($title);
        $entry->setSource($source);

        $entry->setLang($data['lang']);

        $this->objectManager->persist($entry);
        $this->count++;

        if ($this->count >= $this->maxUnitOfWorkSize) {
            $this->objectManager->flush();
            $this->objectManager->clear();
            $this->count = 0;
        }
        pcntl_signal_dispatch();
    }

    public function shutdown() {
        $this->objectManager->flush();
        $this->objectManager->clear();
    }

    protected function signalHandler($signal) {
        exit;
    }
}
