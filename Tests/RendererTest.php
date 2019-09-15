<?php
namespace Tests\Core;

use Core\Renderer;
use PHPUnit\Framework\TestCase;

abstract class RendererTest extends TestCase
{
	/**
	 * @var Renderer
	 */
	private $renderer;

	/*public function setUp() : void
	{
		$this->renderer = new Renderer();
	}

	public function testRenderRightPath()
	{
		$this->renderer->addPath( __DIR__ . '/views','blog');
		$content = $this->renderer->render('@blog/demo');

		$this->assertEquals('Salut les gens', $content);
	}


	public function testRenderDefaultPath()
	{
		$this->renderer->addPath(__DIR__ . '/views');
		$content = $this->renderer->render('demo');

		$this->assertEquals('Salut les gens', $content);
	}*/
}