<?php

namespace DB\ManagerBundle\EventListener;

use DB\ManagerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ExceptionInterface) {
            return;
        }

        $responseData = [
            'exception' => [
                'title' => $exception->getTitle(),
                'message' => $exception->getMessage(),
                'statusCode' => $exception->getStatusCode(),
                'headers' => $exception->getHeaders()
            ]
        ];

        $content = $this->twig->render('DBManagerBundle:Exception:error.html.twig', $responseData);

        $event->setResponse(new Response($content));
    }
}
