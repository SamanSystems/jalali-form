<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace JalaliForm\View\Widget;
use IntlDateTime\IntlDateTime;

/**
 * Input widget class for generating a date time input widget.
 *
 * This class is intended as an internal implementation detail
 * of Cake\View\Helper\FormHelper and is not intended for direct use.
 */
class DateTimeWidget extends \Cake\View\Widget\DateTimeWidget
{
    /**
     * Deconstructs the passed date value into all time units
     *
     * @param string|int|array|\DateTime|null $value Value to deconstruct.
     * @param array $options Options for conversion.
     * @return array
     */
    protected function _deconstructDate($value, $options)
    {
        if ($value === '' || $value === null) {
            return [
                'year' => '', 'month' => '', 'day' => '',
                'hour' => '', 'minute' => '', 'second' => '',
                'meridian' => '',
            ];
        }
        try {
            if (is_string($value)) {
                $date = new IntlDateTime($value, null, 'persian');
            } elseif (is_bool($value)) {
                $date = new IntlDateTime(null, null, 'persian');
            } elseif (is_int($value)) {
                $date = new IntlDateTime('@' . $value, null, 'persian');
            } elseif (is_array($value)) {
                $dateArray = [
                    'year' => '', 'month' => '', 'day' => '',
                    'hour' => '', 'minute' => '', 'second' => '',
                    'meridian' => 'pm',
                ];
                $validDate = false;
                foreach ($dateArray as $key => $dateValue) {
                    $exists = isset($value[$key]);
                    if ($exists) {
                        $validDate = true;
                    }
                    if ($exists && $value[$key] !== '') {
                        $dateArray[$key] = str_pad($value[$key], 2, '0', STR_PAD_LEFT);
                    }
                }
                if ($validDate) {
                    if (!isset($dateArray['second'])) {
                        $dateArray['second'] = 0;
                    }
                    if (isset($value['meridian'])) {
                        $isAm = strtolower($dateArray['meridian']) === 'am';
                        $dateArray['hour'] = $isAm ? $dateArray['hour'] : $dateArray['hour'] + 12;
                    }
                    if (!empty($dateArray['minute']) && isset($options['minute']['interval'])) {
                        $dateArray['minute'] += $this->_adjustValue($dateArray['minute'], $options['minute']);
                    }

                    return $dateArray;
                }

                $date = new IntlDateTime(null, null, 'persian');
            } else {
                $date = clone $value;
            }
        } catch (\Exception $e) {
            $date = new IntlDateTime(null, null, 'persian');
        }

        if (isset($options['minute']['interval'])) {
            $change = $this->_adjustValue($date->format('mm'), $options['minute']);
            $date->modify($change > 0 ? "+$change minutes" : "$change minutes");
        }

        return [
            'year' => $date->format('Y'),
            'month' => $date->format('MM'),
            'day' => $date->format('dd'),
            'hour' => $date->format('H'),
            'minute' => $date->format('mm'),
            'second' => $date->format('ss'),
            'meridian' => $date->format('a'),
        ];
    }

    /**
     * Generates a year select
     *
     * @param array $options Options list.
     * @param \Cake\View\Form\ContextInterface $context The current form context.
     * @return string
     */
    protected function _yearSelect($options, $context)
    {
		$date = new IntlDateTime(null, null, 'persian');
        $options += [
            'name' => '',
            'val' => null,
            'start' => $date->format('Y')-5,
            'end' => $date->format('Y')+5,
            'order' => 'desc',
            'options' => []
        ];

        if (!empty($options['val'])) {
            $options['start'] = min($options['val'], $options['start']);
            $options['end'] = max($options['val'], $options['end']);
        }
        if (empty($options['options'])) {
            $options['options'] = $this->_generateNumbers($options['start'], $options['end']);
        }
        if ($options['order'] === 'desc') {
            $options['options'] = array_reverse($options['options'], true);
        }
        unset($options['start'], $options['end'], $options['order']);
        return $this->_select->render($options, $context);
    }

    /**
     * Returns a translated list of month names
     *
     * @param bool $leadingZero Whether to generate month keys with leading zero.
     * @return array
     */
    protected function _getMonthNames($leadingZero = false)
    {
        $months = [
            '01' => __d('cake', 'Farvardin'),
            '02' => __d('cake', 'Ordibehesht'),
            '03' => __d('cake', 'Khordad'),
            '04' => __d('cake', 'Tir'),
            '05' => __d('cake', 'Mordad'),
            '06' => __d('cake', 'Shahrivar'),
            '07' => __d('cake', 'Mehr'),
            '08' => __d('cake', 'Aban'),
            '09' => __d('cake', 'Azar'),
            '10' => __d('cake', 'Dey'),
            '11' => __d('cake', 'Bahman'),
            '12' => __d('cake', 'Esfand'),
        ];

        if ($leadingZero === false) {
            $i = 1;
            foreach ($months as $key => $name) {
                $months[$i++] = $name;
                unset($months[$key]);
            }
        }

        return $months;
    }
}
