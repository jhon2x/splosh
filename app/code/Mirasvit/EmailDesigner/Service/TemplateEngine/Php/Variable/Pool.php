<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;


class Pool
{
    /**
     * List of registered handlers
     *
     * @var []
     */
    protected $handlers;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $handlers
     */
    public function __construct(
        Context $context,
        $handlers = []
    ) {
        $this->context = $context;
        $this->handlers = $handlers;
    }

    /**
     * Context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Resolve
     *
     * @param  string $name
     * @param array   $args
     * @return string|array|bool
     */
    public function resolve($name, $args = [])
    {
        $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        foreach ($this->handlers as $handler) {
            if ($this->canInvoke($handler, $name, $args)) {
                return call_user_func_array([$handler, $name], $args);
            }

            if ($this->canInvoke($handler, $methodName, $args)) {
                return $handler->{$methodName}();
            }
        }

        return false;
    }

    /**
     * Random variables (for preview)
     *
     * @return array
     */
    public function getRandomVariables()
    {
        $variables = [];

        foreach ($this->handlers as $handler) {
            if (method_exists($handler, 'getRandomVariables')) {
                $vars = $handler->getRandomVariables();

                $variables = array_merge_recursive($variables, $vars);
            }
        }

        return $variables;
    }

    /**
     * We cannot invoke $handler's method if number of passed $args less than number of required args.
     *
     * @param object $handler - possible host object of invoked method
     * @param string $name    - method name
     * @param array  $args    - method arguments
     *
     * @return bool
     */
    private function canInvoke($handler, $name, array $args = [])
    {
        if (!method_exists($handler, $name)) {
            return false;
        }

        $reflection = new \ReflectionObject($handler);
        $reflectionMethod = $reflection->getMethod($name);
        if (count($args) < $reflectionMethod->getNumberOfRequiredParameters()
            || count($args) > $reflectionMethod->getNumberOfParameters()
        ) {
            return false;
        }

        return true;
    }
}
