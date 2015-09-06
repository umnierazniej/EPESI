<?php

/**
 * Created by PhpStorm.
 * User: pjedwabny
 * Date: 12.06.15
 * Time: 17:16
 */
class Base_Menu_Launchpad extends Module
{
    public function modal()
    {
        $launchpad = array();
        if (Base_AclCommon::is_user()) {
            foreach (Base_Menu_LaunchpadCommon::get_options() as $k => $v) {
                if (Base_User_SettingsCommon::get('Base_Menu_Launchpad', $v['name'])) {
                    $ii = array();
                    $trimmed_label = trim(substr(strrchr($v['label'], ':'), 1));
                    $ii['label'] = $trimmed_label ? $trimmed_label : $v['label'];
                    $ii['description'] = $v['label'];
                    $arr = $v['link'];
                    if (isset($arr['__url__']))
                        $ii['url'] = $arr['__url__'];
                    else {
                        $main_mod = $arr['box_main_module'];
                        $main_func = isset($arr['box_main_function']) ? $arr['box_main_function'] : null;
                        $main_args = isset($arr['box_main_arguments']) ? $arr['box_main_arguments'] : null;
                        $constructor_args = isset($arr['box_main_constructor_arguments']) ? $arr['box_main_constructor_arguments'] : null;
                        $ii['url'] = Base_BoxCommon::create_href($this, $main_mod, $main_func, $main_args, $constructor_args, $arr);
                    }
                    $ii['icon'] = 'cogs';//TODO: Add icons support
                    $launchpad[] = $ii;
                }
            }
            usort($launchpad, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return $this->render('launchpad.twig', array(
                'icons' => $launchpad
            ));
        }
    }
}