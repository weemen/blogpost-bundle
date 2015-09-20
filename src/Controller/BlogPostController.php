<?php

namespace Weemen\BlogPostBundle\Controller;


use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;

use Elasticsearch\Client;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;

use FOS\RestBundle\Util\Codes;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Weemen\BlogPost\Application\Command\CreateBlogPost;
use Weemen\BlogPost\Application\Command\EditBlogPost;
use Weemen\BlogPost\Domain\BlogPost\BlogPostRepository;

use Weemen\ElasticsearchRepository\ReadModel\ElasticsearchRepository;
use Weemen\BlogPostBundle\Form\Type\BlogPost as BlogPostType;
use Weemen\BlogPostBundle\Form\Type\EditBlogPost as EditBlogPostType;

/**
 * Class BlogPostController
 * @package Weemen\BlogPostBundle\Controller
 * @uses FOSRestController
 */
class BlogPostController extends FOSRestController
{

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ElasticSearchRepository
     */
    private $repository;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     * @param FormFactory $formFactory
     * @param Router $router
     * @param BlogPostRepository $repository
     */
    public function __construct(
        CommandBusInterface $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        FormFactory $formFactory,
        Router $router,
        ElasticSearchRepository $repository
    ) {
        $this->commandBus    = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->formFactory   = $formFactory;
        $this->router        = $router;
        $this->repository    = $repository;
    }

    /**
     * @Annotations\Get("/blog/published/posts")
     * @param Request $request
     */
    public function getPublishedBlogPostsAction()
    {
        $client = new Client();
        $params['index'] = 'blogposts_published';
        $params['type']  = 'Weemen\BlogPost\ReadModel\BlogPostsPublished';
        $params['body']['sort']  = array(
            array(
                "publishDate" => array(
                    "order" => "desc"
                )
            )
        );
        $results = $client->search($params);

        return $results['hits']['hits'];
    }

    /**
     * @Annotations\Post("/blog/post")
     * @param Request $request
     */
    public function postBlogPostAction(Request $request)
    {
        $blogPostId          = $this->uuidGenerator->generate();

        $requestBlogPost     = $request->request->get('blog_post');
        $command             = new CreateBlogPost();
        $command->blogPostId = $blogPostId;
        $command->title      = $requestBlogPost['title'];
        $command->content    = $requestBlogPost['content'];
        $command->author     = $requestBlogPost['author'];
        $command->published  = $requestBlogPost['published'];
        $command->source     = $requestBlogPost['source'];
        $date = \DateTime::createFromFormat('Y-m-d H:i:s',$requestBlogPost['publishDate']);
        $command->publishDate = $date->format('Y-m-d H:i:s');
        $form                = $this->formFactory->create(new BlogPostType(),$command);

        return $this->handleForm($request, $form, $blogPostId, $command);
    }

    /**
     * @Annotations\Put("/blog/post/{id}")
     * @param Request $request
     */
    public function putBlogPostAction(Request $request)
    {
        $blogPostId          = $this->uuidGenerator->generate();

        $command             = new EditBlogPost();
        $command->blogPostId = $blogPostId;
        $command->title      = $request->request->get('title');
        $command->content    = $request->request->get('content');
        $command->author     = $request->request->get('author');
        $command->published  = (bool) $request->request->get('published');
        $command->source     = $request->request->get('source');

        $form                = $this->formFactory->create(new EditBlogPostType(),$command);

        return $this->handleForm($request, $form, $blogPostId, $command);
    }

    /**
     * @Annotations\Get("/blog/post/{id}")
     * @param Request $request
     * @param $id
     * @return \Broadway\ReadModel\ReadModelInterface|mixed|null
     */
    public function getBlogPostAction(Request $request, $id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param string $blogPostId
     * @param $command
     */
    private function handleForm(Request $request, FormInterface $form, string $blogPostId, $command)
    {
        $form->submit($request);

        if ($form->isValid()) {
            $this->handleCommand($command);

            return $this->redirectView(
                $this->router->generate(
                    'get_blog_post',
                    array('id' => $blogPostId)
                ),
                Codes::HTTP_CREATED
            );
        }

        return $form;
    }

    /**
     * @param $command
     */
    private function handleCommand($command)
    {
        try {
            $this->commandBus->dispatch($command);
        } catch(\Exception $e) {
            throw $this->createNotFoundException($e);
        }
    }
}