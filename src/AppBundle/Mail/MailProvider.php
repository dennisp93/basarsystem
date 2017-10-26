<?php

namespace AppBundle\Mail;

use AppBundle\Entity\Participant;

class MailProvider
{
    private $mailer;
    private $twig;
    private $baseUrl;
    private $basePath;
    private $from;

    /**
     * @param $mailer
     * @param $twig
     * @param $baseUrl
     * @param $basePath
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, $baseUrl, $basePath)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->baseUrl = $baseUrl;
        $this->basePath = $basePath;
        $this->from = '';
    }

    /**
     * @param Participant $receiver
     * @param string $password
     */
    public function sendInvitationMail($receiver, $password)
    {
        $message = \Swift_Message::newInstance();

        $message
            ->setSubject('Anmeldung beim Basar')
            ->setFrom($this->from)
            ->setTo($receiver->getEmail())
            ->setBody(
                $this->twig->render(
                    'AppBundle:email:invitation.html.twig',
                    [
                        'user' => $receiver,
                        'password' => $password,
                        'base_url' => $this->baseUrl,
                        'base_path' => $this->basePath
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

}