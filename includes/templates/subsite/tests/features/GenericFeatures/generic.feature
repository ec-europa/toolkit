Feature: Generic
  In order to provide sub-sites with a minimal behat coverage
  I want to make sure that all available URL's are clean.

  @api
  Scenario: As adminsitrator user I should visit all pages
    Given I am logged in as a user with the "administrator" role
    Given the page contents have the correct code
