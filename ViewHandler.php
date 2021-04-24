<?php

namespace App;

class ViewHandler {
	private const CMD_EXTENDS = '/@extends\([\'"](.*?)[\'"]\)/im';
	private const CMD_INCLUDE = '/@include\([\'"](.*?)[\'"]\)/im';

	private $name;
	private $vars;

	/**
	 * ViewHandler constructor.
	 */
	public function __construct($name, array $vars = []) {
		$this->name = $name;

		$request = RequestHandler::$currentRequest;
		$this->vars = array_merge($request->all(), $vars);
	}

	public function render(): string {
		require_once implode(DIRECTORY_SEPARATOR, [
			BASE_DIR,
			'resources',
			'views',
			self::fileByName('helpers')
		]);
		$content = $this->view($this->name);
		$content = $this->layout($content);

		return $this->include($content);
	}

	public function with(string $name, $value): ViewHandler {
		$this->vars[$name] = $value;

		return $this;
	}

	private static function fileByName(string $name) {
		return implode(DIRECTORY_SEPARATOR, explode('.', $name)) . '.php';
	}

	private function view(string $name) {
		ob_start();
		// load vars
		foreach ($this->vars as $var_name => $value) {
			$$var_name = $value;
		}
		// get the file
		include implode(DIRECTORY_SEPARATOR, [
			BASE_DIR,
			'resources',
			'views',
			self::fileByName($name)
		]);

		$additionalVars = get_defined_vars();
		unset($additionalVars['name']);

		$this->vars = array_merge($this->vars, $additionalVars);

		return ob_get_clean();
	}

	private function layout(string $content) {
		preg_match(self::CMD_EXTENDS, $content, $matches);
		if (count($matches) > 0) {
			$content = preg_replace(self::CMD_EXTENDS, '', $content);
			$extends = $matches[1];
		} else {
			return $content;
		}

		ob_start();
		// load vars
		foreach ($this->vars as $name => $value) {
			$$name = $value;
		}
		include implode(DIRECTORY_SEPARATOR, [
			BASE_DIR,
			'resources',
			'views',
			self::fileByName($extends)
		]);

		$additionalVars = get_defined_vars();
		unset(
			$additionalVars['content'],
			$additionalVars['matches'],
			$additionalVars['value'],
			$additionalVars['name'],
			$additionalVars['extends']
		);

		$this->vars = array_merge($this->vars, $additionalVars);

		return ob_get_clean();
	}

	private function include(string $content) {
		$offset = 0;

		while (preg_match(self::CMD_INCLUDE, $content, $matches, 0, $offset)) {
			if (count($matches) > 0) {
				$parts = preg_split(self::CMD_INCLUDE, $content, 2);
				$partial = new ViewHandler($matches[1], $this->vars);
				$partialRendered = $partial->render();

				$offset = strlen($parts[0]) + strlen($partialRendered) - 1;

				$content = implode('', [$parts[0], $partialRendered, $parts[1]]);
			} else {
				break;
			}
		}

		return $content;
	}
}