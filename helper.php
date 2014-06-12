<?php
/**
 *
 * @package phpBB Extensions Manager
 * @copyright (c) 2014 Carlo (carlino1994/carlo94it)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace carlo94it\phpbbextmanager;

class helper
{
	private $api_url = 'https://api.github.com/repos/%s/%s/%s';
	private $repository = 'carlo94it/phpbb-extensions-repository';

	function __construct()
	{
		// nothing for now
	}

	public function getVendors()
	{
		$trees = $this->doRequest('git/trees', 'master');

		if (!$trees)
		{
			return false;
		}

		$vendors = array();

		foreach($trees->tree as $vendor)
		{
			if ($vendor->type == 'tree')
			{
				$vendors[] = array(
					'name'	=> $vendor->path,
					'sha'	=> $vendor->sha,
				);
			}
		}

		return $vendors;
	}

	public function getExtensions($vendor_sha)
	{
		$trees = $this->doRequest('git/trees', $vendor_sha);

		if (!$trees)
		{
			return false;
		}

		$extensions = array();

		foreach($trees->tree as $extension)
		{
			if ($extension->type == 'blob' && substr(strrchr($extension->path, '.'), 1) == 'json')
			{
				$extensions[] = array(
					'name'	=> str_replace('.json', '', $extension->path),
					'sha'	=> $extension->sha,
				);
			}
		}

		return $extensions;
	}

	public function getExtension($extension_sha)
	{
		$blobs = $this->doRequest('git/blobs', $extension_sha);

		if (!$blobs)
		{
			return false;
		}

		if ($blobs->encoding == 'base64')
		{
			$blobs->content = base64_decode($blobs->content);
		}

		$extension = @json_decode($blobs->content, true);

		if (!$extension)
		{
			return false;
		}

		return $extension;
	}

	public function validate($value, $type)
	{
		$types = array(
			'vendor'	=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}$#',
			'extension'	=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#',
		);

		if (!preg_match($types[$type], $value))
		{
			return false;
		}

		return true;
	}

	public function doRequest($type, $sha)
	{
		$url = sprintf($this->api_url, $this->repository, $type, $sha);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "tan-tan.github-api");
		curl_setopt($ch, CURLOPT_TIMEOUT, 6);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$response = curl_exec($ch);

		curl_close($ch);

		if (!$response)
		{
			return false;
		}

		return $this->parseResponse($response);
	}

	private function parseResponse($response)
	{
		list($header, $body) = explode("\r\n\r\n", $response);

		return @json_decode($body);
	}
}
