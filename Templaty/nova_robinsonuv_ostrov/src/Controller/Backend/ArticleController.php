<?php

namespace App\Controller\Backend;

use App\Entity\Article;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Form\DataTransformer\UrlcodeDataTransformer;

/**
 * Class ArticleController
 * @package App\Controller\Backend
 */
class ArticleController extends BackendController
{
    /** @var UrlcodeDataTransformer */
    private $urlcodeTransdormer;

    /**
     * @param UrlcodeDataTransformer $urlcodeTransformer
     */
    public function __construct(UrlcodeDataTransformer $urlcodeTransformer)
    {
        $this->urlcodeTransdormer = $urlcodeTransformer;
    }

    /**
     * @Route("/article", name="article")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Backend/view/grid.html.twig', [
            'title' => 'Article',
            'grid' => [
                'data' => $this->getGridData(),
                'columns' => [
                    'name' => [
                        'caption' => 'Name',
                    ],
                    'created_at' => [
                        'caption' => 'Created At',
                        'type' => 'date',
                    ],
                    'active' => [
                        'caption' => 'Status',
                        'replacement' => \App\Twig\JsonGridExtension::REPLACEMENT_STATUS,
                    ],
                    '_actions' => [
                        'actions' => [
                            'add' => $this->generateUrl('article-add'),
                            'edit' => $this->generateUrl('article-edit', ['id' => '--id--']),
                            'delete' => $this->generateUrl('article-delete', ['id' => '--id--']),
                        ],
                    ],
                ],
                'setting' => [],
            ],
        ]);
    }

    /**
     * @Route("/article/add", name="article-add")
     * @param Request $request
     * @return Response
     */
    public function add(Request $request): Response
    {
        $article = new Article();
        $form = $this->getForm($article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'The item has been added.');
            return $this->json([
                'redirect' => $this->generateUrl('article'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Add article',
        ]);
    }

    /**
     * @Route("/article/edit/{id}", name="article-edit")
     * @param Request
     * @param int $id
     * @return Response|NotFoundHttpException
     */
    public function edit(Request $request, int $id)
    {
        /** @var \App\Entity\Article $article */
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        if (empty($article)) {
            return $this->createNotFoundException("Can not find item #$id.");
        }

        $form = $this->getForm($article, $this->generateUrl('article-edit', ['id' => $id]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'The item has been updated.');
            return $this->json([
                'redirect' => $this->generateUrl('article'),
            ]);
        }

        return $this->render('Backend/view/modal.html.twig', [
            'form' => $form->createView(),
            'h1' => 'Edit article',
        ]);
    }

    /**
     * @Route("/article/delete/{id}", name="article-delete")
     * @param int $id
     * @param LoggerInterface $logger
     * @return Response
     */
    public function delete(int $id, LoggerInterface $logger): Response
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        if (empty($article)) {
            $this->addFlash('warning', "Can not find item #$id.");
            return $this->redirectToRoute('article');
        }

        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'The item has been deleted.');
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ['exception' => $e]);
            $this->addFlash('danger', "Can not delete item #$id.");
        }

        return $this->redirectToRoute('article');
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param Article $article
     * @param string $action
     * @return FormInterface
     */
    private function getForm(Article $article, string $action = null): FormInterface
    {
        $builder = $this->createFormBuilder($article)
            ->add('name', TextType::class)
            ->add('urlcode', TextType::class, [
                'required' => false,
            ])
            ->add('content', TextareaType::class)
            ->add('seoDescription', TextType::class)
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'not active' => 0,
                    'active' => 1,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->setAction(isset($action) ? $action : $this->generateUrl('article-add'));

        $builder->get('urlcode')->addModelTransformer($this->urlcodeTransdormer);

        return $builder->getForm();
    }

    /**
     * @return array
     */
    private function getGridData(): array
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();

        if (empty($articles)) {
            return [];
        }

        $data = [];

        foreach ($articles as $item) {
            $data[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'created_at' => $item->getCreatedAt(),
                'active' => $item->isActive(),
            ];
        }

        return $data;
    }
}
