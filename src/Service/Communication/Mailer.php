<?php

namespace App\Service\Communication;

use App\Email\InternalTemplatedEmail;

use App\Entity\Invitee;
use App\Entity\InternalUser;
use App\Entity\User;

use Psr\Log\LoggerInterface;

use Spatie\Emoji\Emoji;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class Mailer
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var string
     */
    protected string $supportEmail;

    /**
     * @var string
     */
    private string $uploadsPath;

    protected const DEFAULT_EMAIL_BASE_DIRECTORY = 'email/';
    protected const FINANCE_MAIL_TO = 'finance@shapeit.solutions';

    /**
     * @param string $template
     * @return string
     */
    protected function generateTemplateDirectory(string $template): string
    {
        $baseDirectory =
            (
                defined
                    (
                        sprintf
                            (
                                '%s::BASE_EMAIL_DIRECTORY',
                                static::class
                            )
                    )
                )
                ?
                constant
                    (
                        sprintf
                            (
                                '%s::BASE_EMAIL_DIRECTORY',
                                static::class
                            )
                    )
                :
                static::DEFAULT_EMAIL_BASE_DIRECTORY
        ;

        return sprintf('%s/%s', $baseDirectory, $template);
    }

    /**
     * @param TemplatedEmail $email
     * @return TemplatedEmail
     */
    protected function send(TemplatedEmail $email): TemplatedEmail
    {
        try {
            $this->mailer->send($email);
        }
        catch (TransportExceptionInterface $e) {
            $this->logger->warning('Email was not sent: ' . $e->getMessage());
        }

        return $email;
    }

    /**
     * Mailer constructor.
     * @param LoggerInterface $logger
     * @param MailerInterface $mailer
     * @param string $supportEmail
     * @param string $uploadsPath
     */
    public function __construct
        (
            LoggerInterface $logger,
            MailerInterface $mailer,
            string $supportEmail,
            string $uploadsPath
        )
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->supportEmail = $supportEmail;
        $this->uploadsPath = $uploadsPath;
    }

    /**
     * @param InternalUser $internalUser
     * @param $emailData
     * @return TemplatedEmail
     */
    public function sendContactEmail(InternalUser $internalUser, $emailData): TemplatedEmail
    {
        $senderFullName = $emailData['firstName'] . ' ' . $emailData['lastName'];

        $email = (new TemplatedEmail())
            ->to(new Address($internalUser->getFirstname(), $internalUser->getLastname()))
            ->subject("New Contact Us Form Submission")
            ->htmlTemplate('email/contact_provider.html.twig')
            ->context
                (
                    [
                        'internalUser' => $internalUser,
                        'data' => $emailData,
                        'senderFullName' => $senderFullName,
                    ]
                );

        return $this->send($email);
    }

    /**
     * @param InternalUser $internalUser
     * @param ResetPasswordToken $resetToken
     * @param UserInterface $user
     */
    public function sendCustomPasswordReset
        (
            InternalUser $internalUser,
            ResetPasswordToken $resetToken,
            User $user
        ): void
    {

        $htmlTemplate = 'email/user_password_reset.html.twig';

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Welcome to Shape It')
            ->htmlTemplate($htmlTemplate)
            ->context
                (
                    [
                        'internalUser' => $internalUser,
                        'resetToken' => $resetToken,
                        'user' => $user,
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ResetPasswordToken $resetToken
     * @param User $user
     * @param int $tokenLifetime
     */
    public function sendResetUserPassword
        (
            ResetPasswordToken $resetToken,
            User $user,
            int $tokenLifetime
        ): void
    {

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Reset password')
            ->htmlTemplate('email/user/reset_user_password.html.twig')
            ->context
                (
                    [
                        'internalUser' => $user->getUuid(),
                        'resetToken' => $resetToken,
                        'tokenLifetime' => $tokenLifetime,
                        'user' => $user,
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param User $user
     * @param $resetToken
     * @param $tokenLifetime
     */
    public function sendResetPasswordMessage(UserInterface $user, $resetToken, $tokenLifetime): void
    {
        $htmlTemplate = 'email/reset_password.html.twig';

        if ($user instanceof Invitee)
            $htmlTemplate = 'email/invitee_reset_password.html.twig';

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate($htmlTemplate)
            ->context
                (
                    [
                        'resetToken' => $resetToken,
                        'tokenLifetime' => $tokenLifetime,
                        'user' => $user,
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param InternalUser $user
     * @return TemplatedEmail
     */
    public function sendWelcomeMessage(InternalUser $user): TemplatedEmail
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFirstName()))
            ->subject('Welcome to Shape It Solutions!')
            ->htmlTemplate('email/welcome.html.twig')
            ->context
                (
                    [
                        'user' => $user
                    ]
                );

        $this->send($email);

        return $email;
    }
}
