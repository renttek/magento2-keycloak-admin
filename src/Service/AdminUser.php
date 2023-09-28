<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Service;

use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;

class AdminUser
{
    public function __construct(
        private readonly CollectionFactory $userCollection,
        private readonly UserFactory $userFactory,
    ) {
    }

    public function getOrCreateUser(KeycloakResourceOwner $keycloakResourceOwner): User
    {
        $email = $keycloakResourceOwner->getEmail();
        $user  = $this->getUserByEmail($email);

        if ($user === null) {
            dd(__LINE__);
        }

        return $user;
    }

    private function getUserByEmail(string $email): ?User
    {
        /** @var Collection $collection */
        $collection = $this->userCollection->create();
        $collection->addFieldToFilter('email', ['eq' => $email]);
        $collection->setPageSize(1);

        /** @var User $user */
        $user = $collection->getFirstItem();

        return $user->getId() !== null
            ? $user
            : null;
    }
}
