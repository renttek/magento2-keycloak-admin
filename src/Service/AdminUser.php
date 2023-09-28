<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Service;

use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\String\ByteString;

class AdminUser
{
    public function __construct(
        private readonly CollectionFactory $userCollection,
        private readonly UserFactory $userFactory,
        private readonly ResourceUser $userResource,
    ) {
    }

    public function getOrCreateUser(KeycloakResourceOwner $keycloakResourceOwner): User
    {
        $email = $keycloakResourceOwner->getEmail();
        $user  = $this->getUserByEmail($email) ?? $this->createUser($keycloakResourceOwner);

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

    private function createUser(KeycloakResourceOwner $keycloakResourceOwner): User
    {
        [
            'email' => $email,
            'name' => $username,
            'given_name' => $firstname,
            'family_name' => $lastname,
        ] = $keycloakResourceOwner->toArray();

        /** @var User $user */
        $user = $this->userFactory->create();
        $user->setUserName($username);
        $user->setEmail($email);
        $user->setFirstName($firstname);
        $user->setLastName($lastname);
        $user->setRoleId(1); // TODO: use a mapping between keycloak realm roles and magento roles
        $user->setInterfaceLocale('en_US'); // TODO: map using a keycloak attribute
        $user->setIsActive(true);
        $user->setPassword(ByteString::fromRandom(64)->toString());

        $this->userResource->save($user);

        return $this->getUserByEmail($email);
    }
}
