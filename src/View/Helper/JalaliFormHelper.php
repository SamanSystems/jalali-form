<?php

namespace JalaliForm\View\Helper;

use Cake\View\Helper\FormHelper as Helper;
use Cake\View\View;
use InvalidArgumentException;
use IntlDateTime\IntlDateTime;

class JalaliFormHelper extends Helper
{
    protected $_widgets = [
        'datetime' => ['JalaliForm\View\Widget\DateTimeWidget', 'select'],
    ];

    /**
     * Construct the widgets and binds the default context providers.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(View $View, array $config = [])
    {
        $this->_defaultWidgets = $this->_widgets + $this->_defaultWidgets;
        parent::__construct($View, $config);
    }
	
    /**
     * Helper method for converting from FormHelper options data to widget format.
     *
     * @param array $options Options to convert.
     * @return array Converted options.
     */
	protected function _datetimeOptions($options)
    {
        foreach ($this->_datetimeParts as $type) {
            if (!isset($options[$type])) {
                $options[$type] = [];
            }

            // Pass empty options to each type.
            if (!empty($options['empty']) &&
                is_array($options[$type])
            ) {
                $options[$type]['empty'] = $options['empty'];
            }

            // Move empty options into each type array.
            if (isset($options['empty'][$type])) {
                $options[$type]['empty'] = $options['empty'][$type];
            }
        }
        unset($options['empty']);

        $hasYear = is_array($options['year']);
        if ($hasYear && isset($options['minYear'])) {
            $options['year']['start'] = $options['minYear'];
        }
        if ($hasYear && isset($options['maxYear'])) {
            $options['year']['end'] = $options['maxYear'];
        }
        if ($hasYear && isset($options['orderYear'])) {
            $options['year']['order'] = $options['orderYear'];
        }
        unset($options['minYear'], $options['maxYear'], $options['orderYear']);

        if (is_array($options['month'])) {
            $options['month']['names'] = $options['monthNames'];
        }
        unset($options['monthNames']);

        if (is_array($options['hour']) && isset($options['timeFormat'])) {
            $options['hour']['format'] = $options['timeFormat'];
        }
        unset($options['timeFormat']);

        if (is_array($options['minute'])) {
            $options['minute']['interval'] = $options['interval'];
            $options['minute']['round'] = $options['round'];
        }
        unset($options['interval'], $options['round']);

        if (!isset($options['val'])) {
            $val = new IntlDateTime(null, null, 'persian');
            $currentYear = $val->format('Y');
            if (isset($options['year']['end']) && $options['year']['end'] < $currentYear) {
                $val->set($val->format($options['year']['end'].'/MM/dd'));
            }
            $options['val'] = $val;
        }

        return $options;
    }
}