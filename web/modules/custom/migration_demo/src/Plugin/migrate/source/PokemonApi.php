<?php

namespace Drupal\migration_demo\Plugin\migrate\source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\DataParserPluginManager;
use Drupal\migrate_plus\Plugin\migrate\source\Url;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Source plugin for Pokémon migrations with caching.
 *
 * @MigrateSource(
 *   id = "pokemon_api"
 * )
 */
class PokemonApi extends Url implements ContainerFactoryPluginInterface {

  /**
   * HTTP client.
   */
  protected ClientInterface $httpClient;

  /**
   * Parser manager.
   */
  protected DataParserPluginManager $parserPluginManager;

  /**
   * Cache backend for API responses.
   */
  protected $cache;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    ?MigrationInterface $migration,
    DataParserPluginManager $parserPluginManager,
    ClientInterface $httpClient,
    CacheBackendInterface $cache
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $parserPluginManager);
    $this->httpClient = $httpClient;
    $this->parserPluginManager = $parserPluginManager;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition, ?MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('plugin.manager.migrate_plus.data_parser'),
      $container->get('http_client'),
      $container->get('cache.default'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $url = $row->getSourceProperty('src_url');

    $pokemon_data = $this->fetchApiData($url);
    if (empty($pokemon_data)) {
      return FALSE;
    }

    // Populate row with API data.
    foreach ($pokemon_data as $key => $value) {
      $row->setSourceProperty($key, $value);
    }

    if (isset($pokemon_data['height'])) {
      // Convert height from decimeter to centimeters.
      $row->setSourceProperty('height_cm', $pokemon_data['height'] * 10);
    }

    if (isset($pokemon_data['weight'])) {
      // Convert height from hectograms to kilograms.
      $row->setSourceProperty('weight_kg', $pokemon_data['weight'] / 10);
    }

    // Get species data.
    if (!empty($pokemon_data['species']['url'])) {
      $species_data = $this->fetchApiData($pokemon_data['species']['url']);
      if (empty($species_data)) {
        return FALSE;
      }

      // Add multilingual names.
      if (!empty($species_data['names'])) {
        foreach ($species_data['names'] as $name_data) {
          if (!empty($name_data['language']['name'])) {
            $row->setSourceProperty(
              'name_' . $name_data['language']['name'],
              $name_data['name'] ?? ''
            );
          }
        }
      }

      // Add flavor text entries.
      if (!empty($species_data['flavor_text_entries'])) {
        foreach ($species_data['flavor_text_entries'] as $flavor_text) {
          if (!empty($flavor_text['language']['name']) && !empty($flavor_text['version']['name'])) {
            $row->setSourceProperty(
              'flavor_text_' . $flavor_text['language']['name'] . '_' . $flavor_text['version']['name'],
              $flavor_text['flavor_text'] ?? ''
            );
          }
        }
      }

      // Get evolution chain.
      if (!empty($species_data['evolution_chain']['url'])) {
        $evolution_data = $this->fetchApiData($species_data['evolution_chain']['url']);
        if (empty($evolution_data)) {
          return FALSE;
        }

        $evolution_chain = [];
        $this->buildEvolutionChain($evolution_data['chain'], $evolution_chain);
        $row->setSourceProperty('evolution_chain', $evolution_chain);
      }
    }

    return parent::prepareRow($row);
  }

  /**
   * Recursive helper to build evolution chain.
   */
  protected function buildEvolutionChain(array $chain, array &$evolution_chain): void {
    $species_name = $chain['species']['name'];

    $evolution_chain[] = $species_name;

    if (!empty($chain['evolves_to'])) {
      foreach ($chain['evolves_to'] as $next) {
        $this->buildEvolutionChain($next, $evolution_chain);
      }
    }
  }

  /**
   * Fetch API data with caching.
   */
  protected function fetchApiData(string $url): ?array {
    $cid = 'pokemon_api:' . md5($url);

    if ($cache = $this->cache->get($cid)) {
      return $cache->data;
    }

    try {
      $response = $this->httpClient->get($url);
      $data = json_decode($response->getBody()->getContents(), TRUE);
    } catch (\Exception $e) {
      return NULL;
    }

    if ($data) {
      // Cache for 24 hours.
      $this->cache->set($cid, $data, strtotime('+1 day'));
    }

    return $data;
  }

  /**
   * Log row-level errors and mark as failed.
   */
  protected function logRowError($id, Row $row, \Exception $exception = NULL): void {
    $message = $exception ? $exception->getMessage() : "Skipping row $id due to missing data";
    $this->idMap->saveMessage($row->getSourceIdValues(), $message, MigrationInterface::MESSAGE_ERROR);
    $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_FAILED);
  }

}
