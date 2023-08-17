<?php


namespace App\Service\Communication;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class NotificationMailer extends Mailer
{
    protected const BASE_EMAIL_DIRECTORY = self::DEFAULT_EMAIL_BASE_DIRECTORY . 'notification';

    public function notifyAdmin(array $data): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('Test')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'test.html.twig'
                        )
                )
            ->context($data);

        $this->send($email);
    }
}