<?php


namespace App\Service\Communication;


use App\Event\Domain\Account\AccountAcceptedPortalInvitation;
use App\Event\Domain\Account\AccountApproved;
use App\Event\Domain\Account\AccountApprovedOrPublishedFromSuspension;
use App\Event\Domain\Account\AccountBeingReviewed;
use App\Event\Domain\Account\AccountDeclined;
use App\Event\Domain\Account\AccountInvitedToPortal;
use App\Event\Domain\Account\AccountRegistered;
use App\Event\Domain\Account\AccountSuspended;
use App\Event\Domain\Account\AccountUpdated;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class AccountMailer extends Mailer
{
    /**
     * @param AccountAcceptedPortalInvitation $event
     */
    public function sendAccountAcceptedPortalInvitationToAdmin(AccountAcceptedPortalInvitation $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('Partner has accepted invitation')
            ->htmlTemplate('email/account/admin_provider_accepted_portal_invitation.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountApproved $event
     */
    public function sendAccountApprovedToProvider(AccountApproved $event): void
    {
        $email = (new TemplatedEmail())
            ->addTo($event->getAccount()->getContactEmail())
            ->subject('Welcome to Unbounce\'s App Marketplace Partner Portal')
            ->htmlTemplate('email/account/provider_account_approved.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'link' => $event->getVerifiableEmailLink(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param AccountApprovedOrPublishedFromSuspension $event
     */
    public function sendAccountApprovedOrPublishedFromSuspensionToProvider
        (
            AccountApprovedOrPublishedFromSuspension $event
        ): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAccount()->getContactEmail())
            ->subject('App partner account reactivated')
            ->htmlTemplate
                (
                    'email/account/provider_account_approved_or_published_from_suspension.html.twig'
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountDeclined $event
     */
    public function sendAccountDeclinedToProvider(AccountDeclined $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAccount()->getContactEmail())
            ->subject('App partner application declined')
            ->htmlTemplate('email/account/provider_account_declined.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'note' => $event->getNote(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountBeingReviewed $event
     */
    public function sendAccountBeingReviewedToProvider(AccountBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAccount()->getContactEmail())
            ->subject('App partner application review has started')
            ->htmlTemplate('email/account/provider_account_being_reviewed.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountInvitedToPortal $event
     */
    public function sendAccountInvitedToPortalToProvider(AccountInvitedToPortal $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getUser()->getEmail())
            ->subject('Welcome to Unbounce\'s App Marketplace Partner Portal')
            ->htmlTemplate('email/account/provider_new_account_portal_invitation.html.twig')
            ->context
                (
                    [
                        'resetToken' => $event->getToken(),
                        'tokenLifetime' => $event->getTokenLifetime(),
                        'user' => $event->getUser(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountRegistered $event
     */
    public function sendAccountRegisteredToAdmin(AccountRegistered $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('New App Partner Application')
            ->htmlTemplate('email/account/admin_new_account_registration.html.twig')
            ->context
                (
                    [
                        'account' => $event->getUser()->getAccount(),
                        'user' => $event->getUser(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountRegistered $event
     */
    public function sendAccountRegisteredToProvider(AccountRegistered $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getUser()->getEmail())
            ->subject('App Partner Application Received')
            ->htmlTemplate('email/account/provider_new_account_registration.html.twig')
            ->context
                (
                    [
                        'user' => $event->getUser(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountSuspended $event
     */
    public function sendAccountSuspendedToProvider(AccountSuspended $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAccount()->getContactEmail())
            ->subject('App partner account suspended')
            ->htmlTemplate('email/account/provider_account_suspended.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'note' => $event->getNote(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param AccountUpdated $event
     */
    public function sendAccountUpdatedToAdmin(AccountUpdated $event): void
    {
        $email = (new TemplatedEmail())
            ->addTo($this->supportEmail)
            ->subject('Partner has completed their account profile')
            ->htmlTemplate('email/account/admin_account_updated.html.twig')
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                    ]
                )
        ;

        $this->send($email);
    }
}