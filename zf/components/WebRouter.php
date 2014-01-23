<?php

namespace zf\components;

class WebRouter
{
	private $rules = [];
	private $module;
	public $params = [];

	public function __construct($request)
	{
		$this->method = $request->method;
		$this->path = $request->path;
		$this->segments = $request->segments;
		$this->base = '/' . $request->segments[0];
		$this->baseLength = strlen($this->base);
	}

	public function module($module)
	{
		$this->module = $module;
	}

	public function bulk($rules)
	{
		foreach($rules as $rule)
		{
			list($method, $path, $handlers) = $rule;
			$this->append($method, $path, $handlers);
		}
	}

	public function append($method, $pattern, $handlers)
	{
		if (!strncmp('/:', $pattern, 2) || !strncmp($pattern, $this->base, $this->baseLength))
		{
			$this->rules[] = [strtoupper($method), $pattern, $handlers, $this->module];
		}
	}

	public function parse($pattern)
	{
		preg_match_all('/:([^\/?]+)/', $pattern, $names);
		$regexp = preg_replace(['(\/[^:\\/?]+)','(\/:[^\\/?\\(]+)'], ['(?:\0)','(?:/([^/?]+))'], $pattern);
		return [$names[1], '/^'.str_replace('/','\/', $regexp).'$/'];
	}

	public function match($pattern, $path)
	{
		list($names, $regexp) = $this->parse($pattern);
		if(preg_match($regexp, $path, $values))
		{
			foreach($names as $idx=>$name)
			{
				$params[$name] = isset($values[$idx+1]) ? $values[$idx+1] : null;
			}
			return $params;
		}
	}

	public function dispatch()
	{
		foreach ($this->rules as $rule)
		{
			list($method, $pattern, $handlers, $module) = $rule;

			if ($method === 'ANY' || $method === $this->method)
			{
				if ($this->path === $pattern)
				{
					return [$handlers, null, $module];
				}
				elseif(false !== strpos($pattern, '/:'))
				{
					if ($params = $this->match($pattern, $this->path))
					{
						$this->params = $params;
						return [$handlers, $params, $module];
					}
				}
			}
		}
		return [null, null, null];
	}

}