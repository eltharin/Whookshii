<?php
namespace Core\App\Config;

use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\stream_for;

class AbstractTemplate extends AbstractConfigElement
{
	protected $content = '';

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	public function render(ResponseInterface $response) : ResponseInterface
	{
		\Config::get('HTMLTemplate')->setContent($response->getBody());

		if(\Config::get('HTMLTemplate')->getNoTemplate() === true)
		{
			return $response;
		}

		$TplFile = \Config::get('HTMLTemplate')->getTemplate();

		$template = \Core\App\Loader::SearchFile($TplFile ,'.php','Templates',true);

		if($template === null)
		{
			throw new HttpException('Le template ' . $TplFile . ' est inconnu',404);
		}

		ob_start();
		require $template['file'];

		return $response->withBody(stream_for(ob_get_clean()));
	}
}