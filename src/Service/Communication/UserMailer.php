<?php


namespace App\Service\Communication;


use App\Event\Domain\User\UserAdded;
use App\Event\Domain\User\UserConfirmationEmailResent;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserMailer extends Mailer
{
    protected const BASE_EMAIL_DIRECTORY = self::DEFAULT_EMAIL_BASE_DIRECTORY . 'user';

    /**
     * @param UserAdded $event
     */
    public function sendUserAdded(UserAdded $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getUser()->getEmail())
            ->subject('Welcome to the Unbounce Apps & Integrations Marketplace')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'user_added.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'resetToken' => $event->getResetToken(),
                        'tokenLifetime' => $event->getTokenLifetime(),
                        'user' => $event->getUser(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * Sends a test mail to a user
     */
    public function sendTestMail(): void
    {
        $email = (new TemplatedEmail())
            ->to('don.kizanga@morphed.io')
            ->addTo('kizangadon@yahoo.com')
            ->subject('Test Email')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'test.html.twig'
                        )
                )
            ->embedFromPath('build/images/logo/unbounce-identity-gray-1.svg', 'logo')
        ;

        $this->send($email);
    }

    /**
     * @param UserConfirmationEmailResent $event
     */
    public function sendUserConfirmationEmailResent(UserConfirmationEmailResent $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getUser()->getEmail())
            ->subject('Confirm your email address')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'email_confirmation.html.twig'
                        )
                )
            ->context
                (
                    [
                        'signedUrl' => $event->getVerifiableEmailLink()->getSignedUrl(),
                        'user' => $event->getUser(),
                    ]
                )
        ;

        $this->send($email);
    }
}