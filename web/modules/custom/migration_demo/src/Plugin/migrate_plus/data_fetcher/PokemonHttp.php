<?php

namespace Drupal\migration_demo\Plugin\migrate_plus\data_fetcher;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\AuthenticationPluginManager;
use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Retrieve data over an HTTP connection for migration with filtering.
 *
 * Example:
 *
 * @code
 *  source:
 *    plugin: url
 *    data_fetcher_plugin: pokemon_http
 *    headers:
 *      Accept: application/json
 *      User-Agent: Internet Explorer 6
 *      Authorization-Key: secret
 *      Arbitrary-Header: fooBarBaz
 *    # Guzzle request options can be added.
 *    # See https://docs.guzzlephp.org/en/stable/request-options.html
 *    request_options:
 *      timeout: 300
 *      allow_redirects: false
 * @endcode
 *
 * @DataFetcher(
 *    id = "pokemon_http",
 *    title = @Translation("Pokemon API HTTP")
 *  )
 */
class PokemonHttp extends Http {

  /**
   * The url parameters.
   */
  protected array $urlParameters = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $client, AuthenticationPluginManager $auth_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $client, $auth_plugin_manager);

    $limit = \Drupal::config('migration_demo.settings')->get('limit');
    $offset = \Drupal::config('migration_demo.settings')->get('offset');

    $parameters = [];

    if (!empty($limit)) {
      if ($limit != '0') {
        $parameters['limit'] = $limit;
      }
    }
    if (!empty($offset)) {
      if ($offset != '0') {
        $parameters['offset'] = $offset;
      }
    }

    // Set url parameters for all API calls.
    $this->urlParameters = $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent(string $url): string {
    // Build url with query parameters.
    $options['query'] = $this->urlParameters;

    return (string) $this->getResponse($url, $options)->getBody();
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse($url, $options = []): ResponseInterface {
    try {
      $options['headers'] = $this->getRequestHeaders();
      if (!empty($this->configuration['authentication'])) {
        $options = NestedArray::mergeDeep($options, $this->getAuthenticationPlugin()->getAuthenticationOptions($url));
      }
      if (!empty($this->configuration['request_options'])) {
        $options = NestedArray::mergeDeep($options, $this->configuration['request_options']);
      }
      $method = $this->configuration['method'] ?? 'GET';
      $response = $this->httpClient->request($method, $url, $options);
      if (empty($response)) {
        throw new MigrateException('No response at ' . $url . '.');
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at ' . $url . '.');
    }
    return $response;
  }

}
