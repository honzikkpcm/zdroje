<?php

namespace App;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Facade for creating common responses.
 */
final class ResponseFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(RouterInterface $router, Environment $templating)
    {
        $this->router = $router;
        $this->templating = $templating;
    }

    /*** @param string $name          The name of the route
     * @param mixed  $parameters    An array of parameters
     * @param int    $referenceType The type of reference to be generated (one of UrlGeneratorInterface constants)
     * @return RedirectResponse
     */
    public function redirect($name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return new RedirectResponse($this->router->generate($name, $parameters, $referenceType));
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return Response
     */
    public function render($name, array $parameters = array())
    {
        return new Response($this->templating->render($name, $parameters));
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    public function json($data)
    {
        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }
}
