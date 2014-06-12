<?php
/**
 *
 * @package phpBB Extensions Manager
 * @copyright (c) 2014 Carlo (carlino1994/carlo94it)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace carlo94it\phpbbextmanager\downloader;

class git implements downloader
{
	private $url;
	private $version;
	private $path;

	function __construct()
	{
		$this->url = null;
		$this->version = null;
		$this->path = null;
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function setVersion($version)
	{
		$this->version = $version;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}

	public function download()
	{
		$command = 'git clone %s %s && cd %2$s && git remote add manager %1$s && git fetch manager';
		exec(sprintf($command, escapeshellarg($this->url), escapeshellarg($this->path)));

		if ($this->version != null)
		{
			$command = 'cd %s && git checkout manager/%s';
			exec(sprintf($command, escapeshellarg($this->path), escapeshellarg($this->version)));
		}

		return true;
	}

	public function update()
	{
		if (!file_exists($path . '/.git'))
		{
			return false;
		}

		$command = 'cd %s && git remote set-url manager %s && git fetch manager && git fetch --tags manager';
		shell_exec(sprintf($command, escapeshellarg($this->path), escapeshellarg($this->url)));

		return true;
	}
}
