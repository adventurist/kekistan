<?php

namespace Drupal\statusmessage\Controller;
require_once(DRUPAL_ROOT .'/vendor/autoload.php');

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use PHPHtmlParser;



/**
 * Class StatusPreviewController.
 *
 * @package Drupal\statusmessage\Controller
 */
class StatusPreviewController extends ControllerBase {

  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'));
  }

  /**
   * Constructor.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }
  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function generate($url) {

    \Drupal::logger('StatusPreviewController')->debug('This is getting called');

    $ogParsed = '';
    $contents = file_get_contents('http://' . $url);

    $dom = new PHPHtmlParser\Dom();
    $dom->load($contents);
    $imgs = array();
    $h1s = array();
    $h2s = array();
    $metas = array();
    foreach ($dom->find('img') as $img) {
      $imgs[] = $img;
    }

    foreach ($dom->find('meta') as $meta) {
      $metas[] = $meta;
    }

    foreach ($dom->find('h1') as $h1) {
      $h1s[] = $h1;
    }

    if (null !== null) {
//      $ogParsed = '<div><h3>' . $array['og:title'] . ' </h3>;
//    $jigga = '<img src="' . $array['og:image'][0]['og:image:url'] . '" />';
    } else {

    }


    $response = new Response();
    $response->setContent(\GuzzleHttp\json_encode(array('data' => $ogParsed)));
    $response->headers->set('Content-Type', 'application/json');
    return $response;

//    $seeethis = 'none';
//    return [
//      '#type' => 'form',
//      '#plain_text' => $url,
//    ];
  }

}
