<?php
/**
 *
 * @package phpBB Extensions Manager
 * @copyright (c) 2014 Carlo (carlino1994/carlo94it)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace carlo94it\phpbbextmanager\command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class settings
 *
 * @package carlo94it\phpbbextmanager\command
 */
class vendor extends \phpbb\console\command\command
{
	/** @var \carlo94it\phpbbextmanager\helper */
	protected $helper;

	function __construct(\carlo94it\phpbbextmanager\helper $helper)
	{
		$this->helper = $helper;

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('manager:search:vendor')
			->setDescription('Search extensions by vendor name.')
			->addArgument('vendor-name', InputArgument::REQUIRED, 'Name of the vendor')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('vendor-name');

		if (!$this->helper->validate($name, 'vendor'))
		{
			$output->writeln("<error>Vendor name is invalid</error>");

			return 1;
		}

		$output->writeln("<info>phpBB Extensions list by vendor</info>");
		$output->writeln('');

		$vendors = $this->helper->getVendors();

		if (!$vendors)
		{
			$output->writeln("<error>There was an error while retrieving information</error>");

			return 1;
		}

		$vendor_exists = false;

		foreach($vendors as $vendor)
		{
			if ($vendor['name'] == $name)
			{
				$vendor_exists = true;

				$extensions = $this->helper->getExtensions($vendor['sha']);

				if (!$extensions)
				{
					$output->writeln("<error>There was an error while retrieving information on the vendor</error>");

					return 1;
				}

				foreach($extensions as $extension)
				{
					$extension = $this->helper->getExtension($extension['sha']);

					if (!$extension)
					{
						$output->writeln("<error>There was an error while retrieving information on a extension</error>");

						return 1;
					}

					$output->writeln("- <info>{$extension['display-name']}</info> [{$extension['name']}]");
					$output->writeln("  <comment>{$extension['description']}</comment>");
				}
			}
		}

		if (!$vendor_exists)
		{
			$output->writeln("<error>Vendor '{$name}' don't exists</error>");

			return 1;
		}

		return 0;
	}
}
