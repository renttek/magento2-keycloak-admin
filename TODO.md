# TODO

- Automatically Login if user with matching email-address is found
- Register new user if no user with matching email address is found
  - Filter registration based on email allow-list/pattern
  - Set magento user role based on keycloak realm roles (mapping)
- Handle access token expiration
  - Save refresh token in user session
  - refresh token on every backend request
  - automatically logout if token is no longer valid / can not be refreshed
