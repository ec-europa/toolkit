Feature: Website is available

  Scenario Outline: Anonymous can see the homepage
    Given I am not logged in
    When I go to "<path>"

    Examples:
      | path            |
      | /    |
