<?php

namespace Deeson\WardenDrupalBundle\Controller;

use Deeson\WardenDrupalBundle\Document\ModuleDocument;
use Deeson\WardenBundle\Document\SiteDocument;
use Deeson\WardenBundle\Managers\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Deeson\WardenDrupalBundle\Managers\ModuleManager;

class ModulesController extends Controller {

  /**
   * Default action for listing the modules available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    /** @var ModuleManager $manager */
    $moduleManager = $this->get('warden.drupal.module_manager');

    /** @var SiteManager $siteManager */
    $siteManager = $this->get('warden.site_manager');

    $sitesTotalCount = $siteManager->getAllDocumentsCount();
    $modules = $moduleManager->getDocumentsBy(array('isNew' => FALSE), array('projectName' => 'asc'));

    $moduleList = array();
    foreach ($modules as $module) {
      /** @var ModuleDocument $module */
      $module->setUsagePercentage($sitesTotalCount);
      $moduleList[$module->getSiteCount()][] = $module;
    }
    krsort($moduleList);

    $modules = array();
    foreach ($moduleList as $count) {
      foreach ($count as $module) {
        $modules[] = $module;
      }
    }

    $params = array(
      'modules' => $modules,
    );

    return $this->render('DeesonWardenDrupalBundle:Modules:index.html.twig', $params);
  }

  /**
   * Show the detail of the specific module
   *
   * @param string $projectName
   *   The projectName of the site to view
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function ShowAction($projectName) {
    /** @var ModuleManager $manager */
    $manager = $this->get('warden.drupal.module_manager');
    $module = $manager->getDocumentBy(array('projectName' => $projectName));

    /** @var SiteManager $manager */
    $manager = $this->get('warden.site_manager');
    $sites = $manager->getDocumentsBy(array(), array('name' => 'asc'));

    $sitesNotUsingModule = array();
    foreach ($sites as $site) {
      /** @var SiteDocument $site */
      $usingModule = FALSE;
      foreach ($site->getModules() as $siteModule) {
        if ($siteModule['name'] == $module->getProjectName()) {
          $usingModule = TRUE;
          break;
        }
      }
      if (!$usingModule) {
        $sitesNotUsingModule[$site->getName()] = $site;
      }
    }

    $sitesUsingModule = array();
    foreach ($module->getSites() as $moduleSite) {
      $sitesUsingModule[$moduleSite['name']] = $moduleSite;
    }
    ksort($sitesUsingModule);

    $params = array(
      'module' => $module,
      'sitesUsingModule' => $sitesUsingModule,
      'sitesNotUsingModule' => $sitesNotUsingModule,
    );

    return $this->render('DeesonWardenDrupalBundle:Modules:show.html.twig', $params);
  }

}
