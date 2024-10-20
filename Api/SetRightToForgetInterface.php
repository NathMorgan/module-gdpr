<?php

declare(strict_types=1);

namespace Tuqiri\GDPR\Api;

use Tuqiri\GDPR\Api\Data\SetRightToForgetMessageInterface;

/**
 * Save right to forget
 *
 * @api
 */
interface SetRightToForgetInterface
{
    /**
     * @param int $customerId
     * @return SetRightToForgetMessageInterface
     */
    public function save(
        int $customerId
    ): SetRightToForgetMessageInterface;
}
