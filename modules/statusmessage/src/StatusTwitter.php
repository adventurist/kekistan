<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/9/17
 * Time: 4:12 PM
 */

namespace Drupal\statusmessage;

require_once(DRUPAL_ROOT .'/vendor/autoload.php');

use TwitterAPIExchange;


class StatusTwitter {

  protected $oauthAccessToken;
  protected $oauthAccessTokenSecret;
  protected $consumerKey;
  protected $consumerSecret;
  protected $parameter;

  private $twitterConfig;

  public function __construct($parameter) {
    $this->twitterConfig = \Drupal::config('twitter_api.settings');
    $this->parameter = $parameter;
  }

  /**
   * @return mixed
   */
  public function getConsumerSecret()
  {
    return $this->consumerSecret;
  }

  /**
   * @param mixed $consumerSecret
   */
  public function setConsumerSecret($consumerSecret)
  {
    $this->consumerSecret = $consumerSecret;
  }

  /**
   * @return mixed
   */
  public function getConsumerKey()
  {
    return $this->consumerKey;
  }

  /**
   * @param mixed $consumerKey
   */
  public function setConsumerKey($consumerKey) {
    $this->consumerKey = $consumerKey;
  }

  /**
   * @return mixed
   */
  public function getOauthAccessTokenSecret()
  {
    return $this->oauthAccessTokenSecret;
  }

  /**
   * @param mixed $oauthAccessTokenSecret
   */
  public function setOauthAccessTokenSecret($oauthAccessTokenSecret) {
    $this->oauthAccessTokenSecret = $oauthAccessTokenSecret;
  }

  /**
   * @return mixed
   */
  public function getOauthAccessToken() {
    return $this->oauthAccessToken;
  }

  /**
   * @param mixed $oauthAccessToken
   */
  public function setOauthAccessToken($oauthAccessToken) {
    $this->oauthAccessToken = $oauthAccessToken;
  }

  private function getApiStatusParameter() {
    return 'https://api.twitter.com/1.1/statuses/show.json';
  }


  public function generateRequest($url) {

    $request= $this->parseUrl($url);

    $settings = [
      'oauth_access_token' => $this->twitterConfig->get('oauth_access_token'),
      'oauth_access_token_secret' => $this->twitterConfig->get('oauth_access_token_secret'),
      'consumer_key' => $this->twitterConfig->get('consumer_key'),
      'consumer_secret' => $this->twitterConfig->get('consumer_secret'),
    ];

    $twitterApi = new TwitterAPIExchange($settings);
    $getField = '?id=' . $request . '&tweet_mode=extended';
    $dataRequest = $twitterApi
      ->setGetField($getField)
      ->buildOauth($this->getApiStatusParameter(), 'GET');

    if ($response = $dataRequest->performRequest()) {
      return \GuzzleHttp\json_decode($response);
    } else {
      return null;
    }




  }

  public function sendRequest($twid) {

    if ($data = $this->generateRequest())

    $twitterConfig = \Drupal::config('twitter_api.settings');


    }

  private function parseUrl ($text) {
    /*********************************************************
     * *****************Example url***************************
     * https://twitter.com/FoxNews/status/873623005842243584 *
     *********************************************************/
    return explode('status/', $text)[1];
  }


  public function setNodeData() {

  }


}
