<?php

namespace Tourze\Symfony\AsyncMessage\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class SecurityUserStamp implements StampInterface
{
    private string $userIdentifier;

    public function __construct(string $userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
