<?php
namespace yimaWidgetator\Widget;

use yimaWidgetator\Service\InitializeFeatureInterface;

use Zend\StdLib\AbstractOptions;
use Zend\Filter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractMvcWidget
 *
 * @package yimaWidgetator\Widget
 */
abstract class AbstractWidget extends AbstractOptions
    implements
    InitializeFeatureInterface,
    WidgetInterface,
    ServiceLocatorAwareInterface // to get serviceManager and other registered widgets from within
{
    /**
     * We don't need to have an exception on options that not
     * implemented in class with getter and setter.
     *
     * this key completly ignored.
     * @see AbstractOptions
     *
     * @var bool
     */
    protected $__strictMode__ = false;

    /**
     * @var ServiceLocatorInterface|\yimaWidgetator\Service\WidgetManager
     */
    protected $serviceLocator;

    /**
     * FilterInterface/inflector used to normalize names for use as template identifiers
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * @var string Unique ID of widget
     */
    private $ID;

    /**
     * Some services injected into class after constructor
     * with dependency injection from pluginManager.
     *
     * we call init() to ensure all dependency presents.
     */
    public function init()
    {
        // register widget in service locator with own unique id
        $sl = $this->getServiceLocator();
        $sl->setService($this->getID(), $this);
    }

    /**
     * Return Unique ID for each widget
     *
     * note: usage on every where that you need unique call of each widget
     *       exp. use on jscripts on each widget id
     *
     * @return string
     */
    final public function getID()
    {
        $uniqStr = function($length) {
            $char = "abcdefghijklmnopqrstuvwxyz0123456789";
            $char = str_shuffle($char);
            for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
                $rand .= $char{mt_rand(0, $l)};
            }

            return $rand;
        };

        if ($this->ID == null)
        {
            $class     = get_called_class();
            $module    = $this->deriveModuleNamespace($class);
            $widget    = $this->deriveWidgetName($class);

            $this->ID  = (($module != '') ? $this->inflectName($module).'_' : '')
                .$this->inflectName($widget).'_'.$uniqStr(5);
        }

        return $this->ID;
    }

    /**
     * Render widget as string output
     *
     * @return string
     */
    abstract public function render();

	/**
	 * Inflect a name to a normalized value
	 *
	 * @param  string $name
     *
	 * @return string
	 */
	protected function inflectName($name)
	{
		if (! $this->inflector) {
			$this->inflector = new Filter\Word\CamelCaseToDash();
		}

		return strtolower($this->inflector->filter($name));
	}
	
	/**
	 * Determine the top-level namespace of the controller
	 *
	 * @return string
	 */
	protected function deriveModuleNamespace()
	{
        $widget = get_class($this);

		if (!strstr($widget, '\\')) {

			return '';
		}

		$module = substr($widget, 0, strpos($widget, '\\'));

		return $module;
	}
	
	/**
	 * Determine the name of the widget
	 *
	 * Strip the namespace, and the suffix "yimaWidgetator" if present.
	 *
	 * @return string
	 */
	protected function deriveWidgetName()
	{
        $widget = get_class($this);

		if (strstr($widget, '\\')) {
			$widget = substr($widget, strpos($widget, '\\') + 1);
			$widget = substr($widget, 0, strrpos($widget, '\\'));
		}
		
		if (strstr($widget, '\\')) {
			$widget = substr($widget,strpos($widget, '\\')+1);
		}
		
		return $widget;
	}

    // implement methods ----------------------------------------------------------------
	
	/**
	 * Set serviceManager instance
     *
     * Attention: in very first time set WidgetManager and after get method
     *            from initializers, and before that not accessible.
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
     *
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
        /** @var $serviceLocator \yimaWidgetator\Service\WidgetManager */
        $this->serviceLocator = $serviceLocator;
	}
	
	/**
	 * Retrieve WidgetManager instance
     *
     * Attention: this will set from WidgetManager and after get method
     *            from initializers, and before that not accessible.
	 *
	 * @return \yimaWidgetator\Service\WidgetManager
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
}
