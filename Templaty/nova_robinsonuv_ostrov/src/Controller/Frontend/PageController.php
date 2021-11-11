<?php

namespace App\Controller\Frontend;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PageController
 * @package App\Controller\Frontend
 */
class PageController extends Controller
{
    /**
     * @Route("/", name="page-homepage")
     * @return Response
     */
    public function homepage(): Response
    {
        return $this->render('Frontend/view/homepage.html.twig');
    }

    /**
     * @Route("/prizes", name="page-prizes")
     * @return Response
     */
    public function prizes(): Response
    {
        return $this->render('Frontend/view/prizes.html.twig');
    }

    /**
     * @Route("/rules", name="page-rules")
     * @return Response
     */
    public function rules(): Response
    {
        return $this->render('Frontend/view/rules.html.twig');
    }

}
