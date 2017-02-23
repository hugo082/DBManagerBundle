<?php

namespace DB\ManagerBundle\EventListener;

use DB\ManagerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    protected $twig;
    protected $environment;

    public function __construct(\Twig_Environment $twig, string $environment)
    {
        $this->twig = $twig;
        $this->environment = $environment;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ExceptionInterface) {
            return;
        }

        $responseData = [
            'exception' => [
                'title' => $exception->getTitle($this->environment),
                'message' => $exception->getMessage(),
                'statusCode' => $exception->getStatusCode($this->environment),
                'headers' => $exception->getHeaders($this->environment)
            ]
        ];

        if ($this->environment != 'prod')
            $responseData['exception']['message'] = $exception->getDevMessage();

        $content = $this->twig->render('DBManagerBundle:Exception:error.html.twig', $responseData);

        $event->setResponse(new Response($content));
    }
}
