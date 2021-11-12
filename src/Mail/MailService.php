<?php

namespace EinsUndEins\SmartInboxConnector\Mail;

use EinsUndEins\SmartInboxConnector\SchemaOrg\Renderer;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Service\MailServiceInterface;
use Shopware\Core\Framework\Context;

class MailService implements MailServiceInterface
{
    /**
     * @var MailServiceInterface
     */
    private $mailService;

    /**
     * @var Renderer
     */
    private $renderer;

    public function __construct(MailServiceInterface $mailService, Renderer $renderer)
    {
        $this->mailService = $mailService;
        $this->renderer = $renderer;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    public function send(array $data, Context $context, array $templateData = []): ?\Swift_Message
    {
        if ($this->containsOrderForMail($data, $templateData)) {
            $schemaOrgHtml = $this->renderer->render($templateData['order']);

            $data['contentHtml'] .= $schemaOrgHtml;
        }

        return $this->mailService->send($data, $context, $templateData);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    private function containsOrderForMail(array $data, array $templateData): bool
    {
        return isset($data['contentHtml'], $templateData['order']) &&
            $templateData['order'] instanceof OrderEntity;
    }
}
