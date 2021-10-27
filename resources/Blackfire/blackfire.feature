@api @blackfire
Feature: Example feature for Blackfire.

    Scenario: Profile the /user page
        Given I am at "user"
        Then I should see the text "Log in"
