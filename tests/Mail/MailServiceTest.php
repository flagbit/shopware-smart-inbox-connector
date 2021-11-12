<?php

namespace EinsUndEins\SmartInboxConnector\Tests\Mail;

use EinsUndEins\SmartInboxConnector\Mail\MailService;
use EinsUndEins\SmartInboxConnector\SchemaOrg\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Service\MailService as ParentMailService;
use Shopware\Core\Framework\Context;
use stdClass;
use Swift_Message;

class MailServiceTest extends TestCase
{
    /**
     * @var MockObject&Context
     */
    private $context;

    /**
     * @var MockObject&Swift_Message
     */
    private $message;

    /**
     * @var MockObject&ParentMailService
     */
    private $parentMailer;

    /**
     * @var MockObject&Renderer
     */
    private $renderer;

    protected function setUp(): void
    {
        $this->message = $this->createMock(Swift_Message::class);
        $this->context = $this->createMock(Context::class);
        $this->parentMailer = $this->createMock(ParentMailService::class);
        $this->renderer = $this->createMock(Renderer::class);
    }

    /**
     * @dataProvider provideDataThatSkipsRendering
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    public function testSkipRendering(array $data, array $templateData): void
    {
        $this->renderer->expects(self::never())
            ->method('render');

        $mailer = new MailService($this->parentMailer, $this->renderer);

        $this->parentMailer->expects(self::once())
            ->method('send')
            ->with($data, $this->context, $templateData)
            ->willReturn($this->message);

        $mailer->send($data, $this->context, $templateData);
    }

    public function provideDataThatSkipsRendering(): array
    {
        return [
            'Missing Order in templateData' => [
                ['contentHtml' => ''],
                [],
            ],
            'Invalid Order in templateData' => [
                ['contentHtml' => ''],
                ['order' => new stdClass()],
            ],
            'Missing contentHtml in data' => [
                [],
                ['order' => $this->createStub(OrderEntity::class)],
            ],
        ];
    }

    public function testAppendRenderedStringToMail(): void
    {
        $order = $this->createStub(OrderEntity::class);

        $this->renderer->expects(self::once())
            ->method('render')
            ->with($order)
            ->willReturn('<div>append</div>');

        $mailer = new MailService($this->parentMailer, $this->renderer);

        $data = ['contentHtml' => '<div>original</div>'];
        $expectedData = ['contentHtml' => '<div>original</div><div>append</div>'];
        $templateData = ['order' => $order];

        $this->parentMailer->expects(self::once())
            ->method('send')
            ->with($expectedData, $this->context, $templateData)
            ->willReturn($this->message);

        $mailer->send($data, $this->context, $templateData);
    }
}
