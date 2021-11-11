<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class JsonGridExtension
 * @package App\Twig
 */
class JsonGridExtension extends AbstractExtension
{
    // date format
    const DATE_FORMAT = 'd.m.Y';
    // datetime format
    const DATETIME_FORMAT = 'd.m.Y H:i';

    // replacements
    const
        REPLACEMENT_YES_NO = 'replacement:yesno',
        REPLACEMENT_STATUS = 'replacement:status';

    // setting
    const
        SETTING_ID = 'id',
        SETTING_TITLE = 'title',
        SETTING_HIDE_ADD_BUTTON_WHEN_NO_ITEMS = 'hide-add',
        SETTING_DATE_FORMAT = 'date-format',
        SETTING_DATETIME_FORMAT = 'datetime-format',
        SETTING_ADDITIONAL_ACTIONS = 'additional-actions',
        SETTING_DISABLE_ORDERING = 'disable-ordering';

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('grid', [$this, 'renderGrid'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function renderGrid(\Twig_Environment $environment, $data, $columns, $setting)
    {
        try {
            // fix replacement
            foreach ($columns as &$columnItem) {
                if (isset($columnItem['replacement']) && !is_array($columnItem['replacement'])) {
                    $columnItem['replacement'] = $this->getReplacement($columnItem['replacement']);
                }
            }

            $actions = [
                'add' => [
                    'modal' => true,
                    'icon' => 'plus-square-o',
                    'title' => 'add',
                ],
                'edit' => [
                    'key' => true,
                    'modal' => true,
                    'icon' => 'pencil-square-o',
                    'title' => 'edit',
                ],
                'delete' => [
                    'key' => true,
                    'icon' => 'trash',
                    'title' => 'delete',
                ],
                'send' => [
                    'key' => true,
                    'icon' => 'paper-plane-o',
                    'title' => 'send',
                ],
                'view' => [
                    'key' => true,
                    'modal' => true,
                    'icon' => 'eye',
                    'title' => 'view',
                ],
                'sort-up' => [
                    'key' => true,
                    'icon' => 'arrow-up',
                    'title' => 'sort up',
                ],
                'sort-down' => [
                    'key' => true,
                    'icon' => 'arrow-down',
                    'title' => 'sort down',
                ],
            ];

            if (isset($setting[self::SETTING_ADDITIONAL_ACTIONS])) {
                foreach($setting[self::SETTING_ADDITIONAL_ACTIONS]
                        as $settingAdditionalActionsKey => $settingAdditionalActionsItem) {
                    $actions[$settingAdditionalActionsKey] = [
                        'icon' => $settingAdditionalActionsItem['icon'],
                        'key' => isset($settingAdditionalActionsItem['key']) && $settingAdditionalActionsItem['key'],
                        'modal' => isset($settingAdditionalActionsItem['modal']) && $settingAdditionalActionsItem['modal'],
                        'title' => isset($settingAdditionalActionsItem['title'])
                            ? $settingAdditionalActionsItem['title'] : $settingAdditionalActionsKey,
                    ];
                }
            }

            return $environment->render('Backend/grid/JsonGridExtension.html.twig', [
                'data' => $data,
                'columns' => $columns,
                'id' => isset($setting[self::SETTING_ID]) ? $setting[self::SETTING_ID] : 'table',
                'title' => isset($setting[self::SETTING_TITLE]) ? $setting[self::SETTING_TITLE] : null,
                'actionAddUrl' => (isset($columns['_actions']['actions']['add']) && !isset($setting[self::SETTING_HIDE_ADD_BUTTON_WHEN_NO_ITEMS]))
                    ? $columns['_actions']['actions']['add'] : null,
                'dateFormat' => isset($setting[self::SETTING_DATE_FORMAT]) ? $setting[self::SETTING_DATE_FORMAT] : self::DATE_FORMAT,
                'datetimeFormat' => isset($setting[self::SETTING_DATETIME_FORMAT]) ? $setting[self::SETTING_DATETIME_FORMAT] : self::DATETIME_FORMAT,
                'disableOrdering' => isset($setting[self::SETTING_DISABLE_ORDERING]) && $setting[self::SETTING_DISABLE_ORDERING],
                'actionWidth' => '154px',
                'actions' => $actions,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $replacement
     * @return array
     */
    private function getReplacement(string $replacement) : array
    {
        switch ($replacement) {
            case self::REPLACEMENT_STATUS: return [
                '0' => '<small class="label bg-red">not active</small>',
                '1' => '<small class="label bg-green">active</small>',
            ];

            case self::REPLACEMENT_YES_NO: return [
                '0' => '<small class="label bg-red">no</small>',
                '1' => '<small class="label bg-green">yes</small>',
            ];

            default:
                throw new \InvalidArgumentException("Invalid $replacement replacement.");
        }
    }
}
