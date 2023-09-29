<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Service;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field\RealmRoleBackendRoleMapping;
use Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field\UserAttributeColumn;
use Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field\UserAttributeMapping;
use Renttek\KeycloakAdmin\Model\Config;
use RuntimeException;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\String\ByteString;

use function count;

class AdminUser
{
    const DEFAULT_LOCALE = 'en_US';

    public function __construct(
        private readonly CollectionFactory $userCollection,
        private readonly UserFactory $userFactory,
        private readonly ResourceUser $userResource,
        private readonly Config $config,
    ) {
    }

    public function getOrCreateUser(KeycloakResourceOwner $keycloakResourceOwner): User
    {
        [
            'email'        => $email,
            'realm_access' => [
                'roles' => $roles,
            ],
        ] = $keycloakResourceOwner->toArray();

        $allowedRoles  = $this->config->getUserRolesAllowedForLogin();
        $matchingRoles = array_intersect($roles, $allowedRoles);

        if (count($matchingRoles) === 0) {
            throw new RuntimeException('User is missing keycloak realm role(s) for login');
        }

        $user = $this->getUserByEmail($email);

        if ($user === null && !$this->config->isRegistrationEnabled()) {
            throw new RuntimeException('User could not be found and auto registration is disabled');
        }

        try {
            return $user ?? $this->createUser($keycloakResourceOwner);
        } catch (AlreadyExistsException) {
            throw new RuntimeException('Could not create user');
        }
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

    /**
     * @throws AlreadyExistsException
     */
    private function createUser(KeycloakResourceOwner $keycloakResourceOwner): User
    {
        $email        = $keycloakResourceOwner->getEmail();
        $keycloakUser = $keycloakResourceOwner->toArray();

        $mapping   = $this->getAttributeMapping();
        $username  = $keycloakUser[$mapping[UserAttributeColumn::USERNAME]];
        $firstname = $keycloakUser[$mapping[UserAttributeColumn::FIRSTNAME]];
        $lastname  = $keycloakUser[$mapping[UserAttributeColumn::LASTNAME]];
        $locale    = $keycloakUser[$mapping[UserAttributeColumn::LOCALE]];

        /** @var User $user */
        $user = $this->userFactory->create();
        $user->setUserName($username);
        $user->setEmail($email);
        $user->setFirstName($firstname);
        $user->setLastName($lastname);
        $user->setRoleId($this->getMagentoRole($keycloakResourceOwner));
        $user->setInterfaceLocale($locale ?? $this->config->getDefaultLocale());
        $user->setIsActive(true);
        $user->setPassword(ByteString::fromRandom(64)->toString());

        $this->userResource->save($user);

        return $this->getUserByEmail($email);
    }

    private function getMagentoRole(KeycloakResourceOwner $keycloakResourceOwner): int
    {
        [
            'realm_access' => [
                'roles' => $keycloakRoles,
            ],
        ] = $keycloakResourceOwner->toArray();

        $roleMapping = $this->getRoleMapping();

        foreach ($keycloakRoles as $keycloakRole) {
            if (isset($roleMapping[$keycloakRole])) {
                return (int)$roleMapping[$keycloakRole];
            }
        }

        throw new RuntimeException('Could not map magento role to user');
    }

    private function getAttributeMapping(): array
    {
        return array_column(
            $this->config->getUserAttributeMapping(),
            UserAttributeMapping::KEYCLOAK_ATTRIBUTE,
            UserAttributeMapping::MAGENTO_ATTRIBUTE,
        );
    }
    private function getRoleMapping(): array
    {
        return array_column(
            $this->config->getUserRoleMapping(),
            RealmRoleBackendRoleMapping::MAGENTO_ROLE,
            RealmRoleBackendRoleMapping::KEYCLOAK_ROLE,
        );
    }
}
