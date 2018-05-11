<?php

require('../vendor/autoload.php');

use \Curl\Curl;

if (getenv('PHP_ENVIRONMENT') != 'production') {
  $dotenv = new Dotenv\Dotenv(__DIR__);
  $dotenv->load();
}

function query($query) {
  $curl = new Curl();

  $curl->setHeader('Content-Type', 'application/json');
  $curl->setHeader('Accept', 'application/json');
  $curl->setHeader('Authorization', 'Bearer '.getenv('DATO_API_TOKEN'));
  $curl->post('https://site-api.datocms.com/graphql', array('query' => $query));

  return $curl->response->data;
}

$app = new Slim\App();
$container = $app->getContainer();

$container['view'] = function ($container) {
  $view = new \Slim\Views\Twig(__DIR__.'/views/', [ 'cache' => false ]);
  $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
  $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
  return $view;
};

$app->get('/', function ($request, $response, $args) {
  return $this->view->render($response, 'pages/homepage.twig', [
    'data' => query('
      {
        generalInfo: generalInfo {
          siteName
          callToAction
          socialProfiles {
            name
            url
          }
        }
        homepage {
          quote
          slides {
            url
            title
            alt
          }
        }
        works: allWorks(filter: {showInHome: {eq: true}}) {
          title
          creationDate
          tags { name }
          image { url }
          showInHome
        }
      }
    ')
  ]);
});

$app->get('/about', function ($request, $response, $args) {
  return $this->view->render($response, 'pages/about.twig', [
    'data' => query('
      {
        generalInfo: generalInfo {
          siteName
          callToAction
          socialProfiles {
            name
            url
          }
        }
        aboutPage {
          title
          heroImage { url }
          content
        }
        skillGroups: allSkillGroups(orderBy: [position_ASC]) {
          title
          description
          image { url }
          skills {
            name
            value
          }
        }
      }
    ')
  ]);
});

$app->get('/services', function ($request, $response, $args) {
  return $this->view->render($response, 'pages/services.twig', [
    'data' => query('
      {
        generalInfo: generalInfo {
          siteName
          callToAction
          socialProfiles {
            name
            url
          }
        }
        servicesPage {
          title
          heroImage { url }
          content
        }
        services: allServices(orderBy: [position_ASC]) {
          title
          image { url }
          description
        }
        testimonials: allTestimonials(orderBy: [position_ASC]) {
          name
          content
        }
        counters: allCounters(orderBy: [position_ASC]) {
          title
          value
          image { url }
        }
      }
    ')
  ]);
});

$app->get('/portfolio', function ($request, $response, $args) {
  return $this->view->render($response, 'pages/portfolio.twig', [
    'data' => query('
      {
        generalInfo: generalInfo {
          siteName
          callToAction
          socialProfiles {
            name
            url
          }
        }
        portfolioPage {
          title
          heroImage { url }
          content
        }
        works: allWorks(filter: {showInHome: {eq: false}}) {
          title
          creationDate
          tags { name }
          image { url }
          showInHome
        }
        tags: allTags {
          name
        }
      }
    ')
  ]);
});

$app->get('/contact', function ($request, $response, $args) {
  return $this->view->render($response, 'pages/contact.twig', [
    'data' => query('
      {
        generalInfo: generalInfo {
          siteName
          callToAction
          socialProfiles {
            name
            url
          }
        }
        contactPage {
          title
          heroImage { url }
          content
          location {
            latitude
            longitude
          }
        }
      }
    ')
  ]);
});

$app->run();
