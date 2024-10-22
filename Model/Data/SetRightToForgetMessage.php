<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Model\Data;

use Magento\Framework\Phrase;
use Tuqiri\GDPR\Api\Data\SetRightToForgetMessageInterface;

class SetRightToForgetMessage implements SetRightToForgetMessageInterface
{
    /** @var Phrase */
    private Phrase $message;

    /** @var bool */
    private bool $success;

    /**
     * @param Phrase|null $message
     * @param bool $success
     */
    public function __construct(
        Phrase $message = null,
        bool $success = false
    ) {
        $this->message = $message;
        $this->success = $success;
    }

    /**
     * @inheritDoc
     */
    public function setMessage(Phrase $message): void
    {
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): Phrase
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @inheritDoc
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }
}
