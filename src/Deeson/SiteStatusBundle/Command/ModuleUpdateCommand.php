<?php

namespace Deeson\SiteStatusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Deeson\SiteStatusBundle\Managers\SiteManager;
use Deeson\SiteStatusBundle\Managers\ModuleManager;

class ModuleUpdateCommand extends ContainerAwareCommand {

  protected function configure() {
    $this->setName('deeson:site-status:update-modules')
      ->setDescription('Update the module details');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    /** @var SiteManager $siteManager */
    $siteManager = $this->getContainer()->get('site_manager');
    /** @var ModuleManager $moduleManager */
    $moduleManager = $this->getContainer()->get('module_manager');

    $sites = $siteManager->getAllEntities();

    foreach ($sites as $site) {
      /** @var \Deeson\SiteStatusBundle\Document\Site $site */
      $output->writeln('Updating site: ' . $site->getId() . ' - ' . $site->getUrl());

      foreach ($site->getModules() as $module) {
        /** @var \Deeson\SiteStatusBundle\Document\Module $moduleObj */
        $moduleObj = $moduleManager->findByProjectName($module['name']);
        $sites = $moduleObj->getSites();

        // Check if the site URL is already in the list for this module.
        if (is_array($sites) && in_array($site->getUrl(), $sites)) {
          continue;
        }

        $moduleObj->addSite($site->getUrl(), $module['version']);
        $data = array(
          'sites' => $moduleObj->getSites()
        );
        $moduleManager->updateEntity($moduleObj->getId(), $data);
      }
    }
  }

}