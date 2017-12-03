<?php

use Drupal\DrupalExtension\Context\DrupalContext as DrupalExtensionDrupalContext;

/**
 * Provides step definitions for interacting with Drupal.
 */
class DrupalContext extends DrupalExtensionDrupalContext {

  /**
   * {@inheritdoc}
   */
  public function loggedIn() {
    $session = $this->getSession();
    $session->visit($this->locatePath('/'));

    // Check if the 'logged-in' class is present on the page.
    $element = $session->getPage();
    return $element->find('css', 'body.logged-in');
  }
  
    /**
   * Fill a captcha the captcha on the page.
   *
   * @Then (I )fill the captcha
   */
  public function fillCaptcha() {
    // Get the last captcha of this user.
    $query = 'SELECT MAX(csid), solution FROM {captcha_sessions} WHERE uid=:uid';
    $result = db_query($query, [':uid' => $this->user->uid]);
    $record = $result->fetchAssoc();
    if (!isset($record['solution'])) {
      throw new ExpectationException('The captcha solution could not be found', $this->getSession());
    }
    // Put the solution in the field.
    $this->getSession()->getPage()->fillField('edit-captcha-response', $record['solution']);
  }

}
