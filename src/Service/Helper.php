<?php

// src/Service/Helper.php
namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Helper
{
  private ParameterBagInterface $params;
  private string $scope = 'User.Read';

  public function __construct(ParameterBagInterface $params)
  {
    $this->params = $params;
  }

  /**
   * Get authentication token for microsoft graph.
   *
   * @return mixed
   *   A decoded guzzle response.
   * @throws GuzzleException
   */
  public function connect()
  {
    // see https://github.com/microsoftgraph/msgraph-sdk-php for approach
    $guzzle = new \GuzzleHttp\Client();
    $url = 'https://login.microsoftonline.com/' . $this->params->get('tenant_id') . '/oauth2/v2.0/token';
    try {
      return json_decode($guzzle->post($url, [
        'form_params' => [
          'client_id' => $this->params->get('client_id'),
          'scope' => rawurlencode($this->scope),
          'username' => $this->params->get('username'),
          'password' => $this->params->get('password'),
          'grant_type' => 'password',
        ],
      ])->getBody()->getContents());
    } catch (\Exception $e) {
      print_r($e->getMessage());
    }
  }

  /**
   * Create a microsoft graph request.
   *
   * @param $token
   *   The authentication token for microsoft graph.
   * @param $endpoint
   *   A microsoft graph endpoint.
   * @return GraphResponse
   *   The microsoft graph response.
   * @throws GuzzleException
   */
  public function createRequest($token, $endpoint) : GraphResponse
  {
    $graph = new Graph();
    $graph->setAccessToken($token->access_token);

    try {
      return $graph->createRequest("GET", $endpoint)
        ->execute();
    }
    catch (\Exception $e) {
      print_r($e->getMessage());
    }
  }
}

