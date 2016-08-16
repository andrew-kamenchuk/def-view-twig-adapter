<?php
namespace def\View\Adapter\Twig;

use Twig_Environment as Twig;
use Twig_Loader_Filesystem as Loader;

class View extends \def\View\View
{
    private $configs = [];

    private $twig;

    private $template;

    public function __construct()
    {
        parent::__construct(function (array $data) {
            return $this->twig()->render($this->template, $data);
        });
    }

    public function render($template, array $data = [])
    {
        return $this->twig()->render($template, array_merge($this->data(), $data));
    }

    public function renderString($string, array $data = [])
    {
        return $this->twig()->createTemplate($string)->render(array_merge($this->data(), $data));
    }

    public function configure(callable $configure)
    {
        $this->configs[] = $configure;
    }

    public function twig()
    {
        if (!isset($this->twig)) {
            $this->twig = new Twig(new Loader);
        }

        while (null !== $configure = array_shift($this->configs)) {
            $configure($this->twig);
        }

        return $this->twig;
    }

    public function template($template)
    {
        $this->template = $template;
    }

    public function addPath($path, $namespace = Loader::MAIN_NAMESPACE)
    {
        return $this->configure(function (Twig $twig) use ($path, $namespace) {
            $twig->getLoader()->addPath($path, $namespace);
        });
    }

    public function prependPath($path, $namespace = Loader::MAIN_NAMESPACE)
    {
        return $this->configure(function (Twig $twig) use ($path, $namespace) {
            $twig->getLoader()->prependPath($path, $namespace);
        });
    }

    public function assignGlobal($key, $value, callable ...$filters)
    {
        foreach ($filters as $filter) {
            $value = $filter($value);
        }

        $this->configure(function (Twig $twig) use ($key, $value) {
            $twig->addGlobal($key, $value);
        });

        return parent::assign($key, $value);
    }

    public function setCache($cache, $autoreload = false)
    {
        return $this->configure(function (Twig $twig) use ($cache, $autoreload) {
            $twig->setCache($cache);

            if (false !== $cache) {
                if ($autoreload) {
                    $twig->enableAutoReload();
                } else {
                    $twig->disableAutoReload();
                }
            }
        });
    }

    public function setDebug($debug = true)
    {
        return $this->configure(function (Twig $twig) use ($debug) {
            if ($debug) {
                $twig->enableDebug();
                $twig->addExtension(new \Twig_Extension_Debug);
            } else {
                $twig->disableDebug();
            }
        });
    }

    public function setCharset($charset)
    {
        return $this->configure(function (Twig $twig) use ($charset) {
            $twig->setCharset($charset);
        });
    }

    public function setStrictVariables($strict = true)
    {
        return $this->configure(function (Twig $twig) use ($strict) {
            if ($strict) {
                $twig->enableStrictVariables();
            } else {
                $twig->disableStrictVariables();
            }
        });
    }

    public function addFilter($name, callable $filter, array $options = [])
    {
        return $this->configure(function (Twig $twig) use ($name, $filter, $options) {
            $twig->addFilter(new \Twig_SimpleFilter($name, $filter, $options));
        });
    }

    public function addFunction($name, callable $function, array $options = [])
    {
        return $this->configure(function (Twig $twig) use ($name, $function, $options) {
            $twig->addFunction(new \Twig_SimpleFunction($name, $function, $options));
        });
    }

    public function addExtension(\Twig_ExtensionInterface $extension)
    {
        return $this->configure(function (Twig $twig) use ($extension) {
            $twig->addExtension($extension);
        });
    }
}
