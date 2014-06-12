<?php
/**
 *
 * @package phpBB Extensions Manager
 * @copyright (c) 2014 Carlo (carlino1994/carlo94it)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace carlo94it\phpbbextmanager\downloader;

interface downloader
{
	public function setUrl($url);
	public function setVersion($version);
	public function setPath($path);
	public function download();
	public function update();
}
