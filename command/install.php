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
class install extends \phpbb\console\command\command
{
	/** @var \carlo94it\phpbbextmanager\helper */
	protected $helper;

	/* @var string phpBB root path */
	protected $root_path;

	function __construct(\carlo94it\phpbbextmanager\helper $helper, $root_path)
	{
		$this->helper = $helper;
		$this->root_path = $root_path;

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('manager:install')
			->setDescription('Install a new phpBB Extension.')
			->addArgument('extension-name', InputArgument::REQUIRED, 'Name of the extension to install (vendor/ext-name)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('extension-name');

		if (!$this->helper->validate($name, 'extension'))
		{
			$output->writeln('<error>The extension name is invalid (eg. vendor/ext-name)</error>');

			return 1;
		}

		$extension = $this->helper->doRequest('contents', $name . '.json');

		if (!$extension)
		{
			$output->writeln('<error>There was an error while retrieving information</error>');

			return 1;
		}

		if (isset($extension->message))
		{
			$output->writeln("<error>The extension '{$name}' doesn't exist</error>");

			return 1;
		}

		if (file_exists($this->root_path . 'ext/' . $name))
		{
			$output->writeln('<info>The extension is already installed.</info>');
			$output->writeln('<info>Do you want to update it? Use the manager:update command.</info>');

			return 0;
		}

		$output->writeln("<info>Installing phpBB Extension {$name}</info>");

		$extension = @json_decode(@base64_decode($extension->content));

		$class = '\\carlo94it\\phpbbextmanager\\downloader\\' . $extension->download->type;

		$downloader = new $class();
		$downloader->setUrl($extension->download->url);
		$downloader->setVersion($extension->download->version);
		$downloader->setPath($this->root_path . 'ext/' . $name);

		$output->writeln('');
		$output->writeln('<comment>====================================</comment>');

		$downloader->download();

		$output->writeln('<comment>====================================</comment>');
		$output->writeln('');

		$output->writeln('<info>The extension was installed successfully.</info>');
		$output->writeln('<info>Now you can activate it.</info>');

		return 0;
	}
}
