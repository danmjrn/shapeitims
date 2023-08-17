<?php


namespace App\Service\Communication;


use App\Event\Domain\Application\ApplicationFilesApproved;
use App\Event\Domain\Application\ApplicationFilesBeingReviewed;
use App\Event\Domain\Application\ApplicationFilesChangesNeeded;
use App\Event\Domain\Application\ApplicationFilesDeclined;
use App\Event\Domain\Application\ApplicationListingApproved;
use App\Event\Domain\Application\ApplicationListingBeingReviewed;
use App\Event\Domain\Application\ApplicationListingChangesNeeded;
use App\Event\Domain\Application\ApplicationListingPublished;
use App\Event\Domain\Application\ApplicationListingSuspendedByAdmin;
use App\Event\Domain\Application\ApplicationListingSuspendedByPartner;
use App\Event\Domain\Application\ApplicationMessageSent;
use App\Event\Domain\Application\PublishedApplicationUpdated;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ApplicationMailer extends Mailer
{
    protected const BASE_EMAIL_DIRECTORY = self::DEFAULT_EMAIL_BASE_DIRECTORY . 'application';

    /**
     * @param ApplicationFilesBeingReviewed $event
     */
    public function sendApprovedApplicationFilesBeingReviewedToAdmin(ApplicationFilesBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('App listing edits to review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_approved_application_files_being_reviewed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'author' => $event->getAuthor(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationFilesApproved $event
     */
    public function sendApplicationFilesApprovedToProvider(ApplicationFilesApproved $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAuthor()->getEmail())
            ->subject('App files approved')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_files_approved.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'creator' => $event->getAuthor(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationFilesBeingReviewed $event
     */
    public function sendApplicationFilesBeingReviewedToAdmin(ApplicationFilesBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('App files ready for review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_application_files_being_reviewed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'author' => $event->getAuthor(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationFilesBeingReviewed $event
     */
    public function sendApplicationFilesBeingReviewedToProvider(ApplicationFilesBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getAuthor()->getEmail())
            ->subject('App files submitted for review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_files_being_reviewed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'creator' => $event->getAuthor(),
                    ]
            );

        $this->send($email);
    }

    /**
     * @param ApplicationFilesChangesNeeded $event
     */
    public function sendApplicationFilesChangesNeededToProvider(ApplicationFilesChangesNeeded $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App files need changes')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_files_changes_needed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationFilesDeclined $event
     */
    public function sendApplicationFilesDeclinedToProvider(ApplicationFilesDeclined $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App files declined')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_files_declined.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                        'note' => $event->getNote(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationListingApproved $event
     */
    public function sendApplicationListingApprovedToProvider(ApplicationListingApproved $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App listing approved!')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_listing_approved.html.twig'
                        )
                )
            ->context
                (
                    [
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingBeingReviewed $event
     */
    public function sendApplicationListingBeingReviewedToAdmin(ApplicationListingBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('App listing ready for review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_application_listing_being_reviewed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingBeingReviewed $event
     */
    public function sendApplicationListingBeingReviewedToProvider(ApplicationListingBeingReviewed $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App listing submitted for review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_listing_being_reviewed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingChangesNeeded $event
     */
    public function sendApplicationListingChangesNeededToProvider(ApplicationListingChangesNeeded $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App listing need changes')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_files_changes_needed.html.twig'
                        )
                )
            ->context
                (
                    [
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingPublished $event
     */
    public function sendApplicationListingPublishedToProvider(ApplicationListingPublished $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App listing published!')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_listing_published.html.twig'
                        )
                )
            ->context
                (
                    [
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingSuspendedByAdmin $event
     */
    public function sendApplicationListingSuspendedByAdmin(ApplicationListingSuspendedByAdmin $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getCreator()->getEmail())
            ->subject('App listing suspended')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_listing_suspended.html.twig'
                        )
                )
            ->context
                (
                    [
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                        'note' => $event->getNote(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationListingSuspendedByPartner $event
     */
    public function sendApplicationListingSuspendedByPartner(ApplicationListingSuspendedByPartner $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('App listing suspended')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_application_listing_suspended.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                    ]
                )
        ;

        $this->send($email);
    }

    /**
     * @param ApplicationMessageSent $event
     */
    public function sendApplicationMessageSentToAdmin(ApplicationMessageSent $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject
                (
                    sprintf
                        (
                            'New message from %s',
                            $event->getAccount()->getName()
                        )
                )
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_application_message_sent.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'author' => $event->getAuthor(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param ApplicationMessageSent $event
     */
    public function sendApplicationMessageSentToProvider(ApplicationMessageSent $event): void
    {
        $email = (new TemplatedEmail())
            ->to($event->getOwner()->getEmail())
            ->subject('New message from Unbounce App Team')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'provider_application_message_sent.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'author' => $event->getOwner(),
                    ]
                );

        $this->send($email);
    }

    /**
     * @param PublishedApplicationUpdated $event
     */
    public function sendPublishedApplicationUpdatedToAdmin(PublishedApplicationUpdated $event): void
    {
        $email = (new TemplatedEmail())
            ->to($this->supportEmail)
            ->subject('App listing edits to review')
            ->htmlTemplate
                (
                    $this->generateTemplateDirectory
                        (
                            'admin_published_application_updated.html.twig'
                        )
                )
            ->context
                (
                    [
                        'account' => $event->getAccount(),
                        'application' => $event->getApplication(),
                        'creator' => $event->getCreator(),
                    ]
                )
        ;

        $this->send($email);
    }
}