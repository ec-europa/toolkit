Feature: Generic
  In order to provide sub-sites with a minimal behat coverage
  I want to make sure that all public URL's are available

  @api
  Scenario: Multiple dynamic visits
    Given I am logged in as a user with the "administrator" role
    Given the page contents are correct
    Then the response status code should be 200
