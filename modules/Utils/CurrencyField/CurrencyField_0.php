<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @version 1.0
 * @license MIT
 * @package epesi-utils
 * @subpackage CurrencyField
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Utils_CurrencyField extends Module {
	private static $positions;
	private static $active;
	
	public function construct() {
		self::$positions = array(0=>__('After'), 1=>__('Before'));
		self::$active = array(1=>__('Yes'), 0=>__('No'));
	}
	
	public function admin() {
		if($this->is_back()) {
			if($this->parent->get_type()=='Base_Admin')
				$this->parent->reset();
			else
				location(array());
			return;
		}
		print('<h1>'.__('Currencies').'</h1>');
		$fiat_gb = $this->init_module('Utils_GenericBrowser',null,'currencies');
		$fiat_gb->set_table_columns([
            ['name'=>__('ID')],
			['name'=>__('Code')],
			['name' => __('Full Name')],
			['name'=>__('Default')],
			['name'=>__('Active')]
		]);
		$ret = DB::Execute('SELECT id, code, default_currency, active FROM utils_currency ORDER BY id ASC');
		while($row = $ret->FetchRow()) {
			$fiat_gb_row = $fiat_gb->get_new_row();
			$fiat_gb_row->add_data_array(array(
                    $row['id'],
					$row['code'],
					Utils_CommonDataCommon::get_value('Currencies_Codes/'.$row['code']),
					self::$active[$row['default_currency']],
					self::$active[$row['active']]
				));
			$fiat_gb_row->add_action($this->create_callback_href(array($this, 'edit_currency'),array($row['id'])),'edit');
		}
		Base_ActionBarCommon::add('add', __('Add New Currency'), $this->create_callback_href([$this,'add_currency']));
		Base_ActionBarCommon::add('settings', __('Currencies Settings'), $this->create_callback_href([$this, 'currencies_settings']));
		Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());
        Base_ActionBarCommon::add('settings', __('Synchronize'), $this->create_callback_href([__CLASS__, 'update_currencies_list']));
		$this->display_module($fiat_gb);

        print('<h1>'.__('Cryptocurrencies').'</h1>');
		$cr_gb = $this->init_module('Utils_GenericBrowser', null, 'cryptocurrencies');
        $cr_gb->set_table_columns([
            ['name'=>__('ID')],
            ['name'=>__('Code')],
            ['name'=>__('Cryptocurrency Name')],
            ['name'=>__('Default')],
            ['name'=>__('Active')]
        ]);
        $ret = DB::Execute('SELECT * FROM utils_cryptocurrencies ORDER BY id ASC');
        while($row = $ret->FetchRow()) {
        	$cr_gb_row = $cr_gb->get_new_row();
        	$cr_gb_row->add_data_array([
        		$row['id'],
				$row['code'],
                Utils_CommonDataCommon::get_value('Cryptocurrencies_Codes/'.$row['code']),
                self::$active[$row['default_currency']],
                self::$active[$row['active']]
			]);
        	$cr_gb_row->add_action($this->create_callback_href([$this,'edit_cryptocurrency'],[$row['id']]),'edit');
        }
		$this->display_module($cr_gb);
	}

	public function add_currency() {

        if ($this->is_back()) return false;

        $crypto_href = $this->create_callback_href(array($this, 'add_cryptocurrency'), array(null));
        print('<h1>'.__('Currency'));
        print(' | ');
        print('<a '.$crypto_href.'>'.__('Cryptocurrency').'</a></h1>');

        $form = $this->init_module('Libs_QuickForm');
        $options = self::get_currency_options();
        $form->addElement('select', 'currency', __('Currency'), $options);
        $form->addElement('select', 'default_currency', __('Default'), self::$active);
        $form->addElement('select', 'active', __('Active'), self::$active);

        if ($form->validate()) {
            $vals = $form->exportValues();
            if(isset($vals['default_currency']) && $vals['default_currency']) DB::Execute('UPDATE utils_currency SET default_currency=0');
            $vals = [
            	' ',
				$vals['currency'],
				'.',
				' ',
				2,
				0,
                htmlspecialchars($vals['active']),
                isset($vals['default_currency'])?htmlspecialchars($vals['default_currency']):1
            ];
            $sql = 'INSERT INTO utils_currency (symbol, code, decimal_sign, thousand_sign, decimals, pos_before, active, default_currency) VALUES (%s, %s, %s, %s, %d, %d, %d, %d)';
            DB::Execute($sql, $vals);
        }
        Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());
        Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
        if($form->validate_with_message('Currency saved',__('Problem encountered'))) {
            if ($form->process(function () {
            	return true;
			})) {
				Base_BoxCommon::location('Utils_CurrencyField','admin');
            }
        }
        else $form->display();
        return true;
	}

	public function edit_currency($id) {
		if ($this->is_back()) return false;
		$form = $this->init_module('Libs_QuickForm');
        $code = Utils_CurrencyFieldCommon::get_code($id);
        $name = $code.' - '.Utils_CommonDataCommon::get_value('Currencies_Codes/'.$code);
        $form->addElement('header', null, '<h4>'.$name.'</h4>');
        $form->addElement('static', null, '');
		$form->addElement('select', 'default_currency', __('Default'), self::$active);
		$form->addElement('select', 'active', __('Active'), self::$active);

		$defs = DB::GetRow('SELECT active, default_currency FROM utils_currency WHERE id=%d', [$id]);
		$form->setDefaults($defs);
		if($defs['default_currency']) $form->freeze(array('default_currency'));

		if ($form->validate()) {
			$vals = $form->exportValues();
			if(isset($vals['default_currency']) && $vals['default_currency']) DB::Execute('UPDATE utils_currency SET default_currency=0');
			$vals = [
				htmlspecialchars($vals['active']),
				isset($vals['default_currency'])?htmlspecialchars($vals['default_currency']):1,
				$id
			];
			$sql = 'UPDATE utils_currency SET '.
							'active=%d,'.
							'default_currency=%d'.
							' WHERE id=%d';
			DB::Execute($sql, $vals);
		}
        Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());
        Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
        if($form->validate_with_message(__('Currency saved'),__('Problem encountered'))) {
            if ($form->process(function () {
                return true;
            })) {
                Base_BoxCommon::location('Utils_CurrencyField','admin');
            }
        }
        else $form->display();
        return true;
	}

	public function edit_cryptocurrency($id) {

        if ($this->is_back()) return false;
        $form = $this->init_module('Libs_QuickForm');
        $curr = Utils_CurrencyFieldCommon::get_cryptocurrency_by_id($id);
        $name = $curr.' - '.Utils_CommonDataCommon::get_value('Cryptocurrencies_Codes/'.$curr);

		$form->addElement('header', null, '<h4>'.$name.'</h4>');
        $form->addElement('select', 'default_currency', __('Default Cryptocurrency'), self::$active);
        $form->addElement('select', 'active', __('Active'), self::$active);

        $defs = DB::GetRow('SELECT * FROM utils_cryptocurrencies WHERE id=%d', [$id]);
        $form->setDefaults($defs);
        if($defs['default_currency']) $form->freeze(['default_currency']);
        if ($form->validate()) {
            $vals = $form->exportValues();
            if(isset($vals['default_currency']) && $vals['default_currency']) DB::Execute('UPDATE utils_cryptocurrencies SET default_currency=0');
            $vals = [
                htmlspecialchars($vals['active']),
                isset($vals['default_currency']) ? htmlspecialchars($vals['default_currency']) : 1,
            	$id
			];
            $sql = 'UPDATE utils_cryptocurrencies SET '.
                'active=%d,'.
                'default_currency=%d'.
                ' WHERE id=%d';
            DB::Execute($sql, $vals);
        }

        Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());
        Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
        if($form->validate_with_message('Currency saved',__('Problem encountered'))) {
            if ($form->process(function () {
                return true;
            })) {
                Base_BoxCommon::location('Utils_CurrencyField','admin');
            }
        }
        else $form->display();
        return true;
	}

	public function add_cryptocurrency() {

        if ($this->is_back()) return false;

        $curr_href = $this->create_callback_href(array($this, 'add_currency'), array(null));
        print('<h1><a '.$curr_href.'>'.__('Currency').'</a>');
        print(' | ');
        print(__('Cryptocurrency').'</h1>');

        $options = self::get_crypto_options();
		$form = $this->init_module(Libs_QuickForm::module_name());
		$form->addElement('select', 'cryptocurrencies', __('Add Cryptocurrency').': ', $options);
		$form->addElement('select', 'default_currency', __('Default cryptocurrency'), self::$active);
		$form->addElement('select', 'active', __('Active'), self::$active);

		if ($form->validate()) {
			$vals = $form->exportValues();
			if(isset($vals['default_currency']) && $vals['default_currency']) DB::Execute('UPDATE utils_cryptocurrencies SET default_currency=0');
			$vals = [
				htmlspecialchars($vals['cryptocurrencies']),
				htmlspecialchars($vals['active']),
				isset($vals['default_currency']) ? htmlspecialchars($vals['default_currency']) : 1
			];
			$sql = 'INSERT INTO utils_cryptocurrencies ('.
								'code, '.
								'active, '.
								'default_currency'.
							') VALUES ('.
								'%s, '.
								'%d, '.
								'%d'.
							')';
            DB::Execute($sql, $vals);
		}
        Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());
        Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
        if($form->validate_with_message('Currency saved',__('Problem encountered'))) {
            if ($form->process(function () {
                return true;
            })) {
                Base_BoxCommon::location('Utils_CurrencyField','admin');
            }
        }
        else $form->display();
        return true;
	}

	public function get_crypto_options() {
        $options = Utils_CommonDataCommon::get_array('Cryptocurrencies_Codes');
        $local_crypto = array_values(Utils_CurrencyFieldCommon::get_all_cryptocurrencies());

        foreach($local_crypto as $k => $v) {
            if(array_search($v, array_keys($options)) !== false) unset($options[$v]);
        }

        foreach($options as $k => $v) $options[$k] = $k.' - '.$v;
        ksort($options);
		return $options;
	}

	public function get_currency_options() {
        $options = Utils_CommonDataCommon::get_array('Currencies_Codes');
        $local_currencies = array_values(Utils_CurrencyFieldCommon::get_all_currencies());
        foreach($local_currencies as $k => $v) {
            if(key_exists($v, $options)) {
            	unset($options[$v]);
            }
        }
        foreach($options as $k => $v) $options[$k] = $k.' - '.$v;
		ksort($options);
		return $options;
	}

	public function currencies_settings() {
        if ($this->is_back()) return false;
		load_js('modules/Utils/CurrencyField/js/settings.js');
        eval_js('currency_settings();');
        Base_ActionBarCommon::add('back', __('Back'), $this->create_back_href());

        $form = $this->init_module(Libs_QuickForm::module_name());
        $form->addElement('header', null, '<h4>'.__('Currencies Settings').'</h4>');
        $form->addElement('static', null, '<br/>');
        $form->addElement('checkbox', 'subscribe', '', __('Enable exchange rates subscription'));
        $form->addElement('static', null, '<br/>');
        $form->addElement('header', null, '<b>'.__('Exchange Rate Tooltip').'</b>');
        $form->addElement('checkbox', 'exchange_rates', '', __('Display fiduciary money rates'));
        $form->addElement('checkbox', 'crypto_rates', '', __('Display cryptocurrencies rates'));
        $form->addElement('static', null, '<br/>');
        $form->addElement('header', null, '<b>'.__('Cryptocurrencies').'</b>');
        $form->addElement('checkbox', 'display_crypto', '', __('Display field value in default cryptocurrency'));


        $current['subscribe'] = Variable::get('curr_subscribe', false);;
        $current['exchange_rates'] = Variable::get('curr_exchange_rates', false);
        $current['crypto_rates'] = Variable::get('curr_crypto_rates', false);
        $current['display_crypto'] = Variable::get('curr_display_crypto', false);

        $form->setDefaults($current);
        Base_ActionBarCommon::add('save', __('Save'), $form->get_submit_form_href());
        if($form->validate_with_message('Settings saved',__('Problem encountered'))) {
        	$values = $form->exportValues();
			foreach($current as $k => $v) {
				if(key_exists($k, $values) && $values[$k] === "1" && $v !==$values[$k]) {
					Variable::set('curr_'.$k, true);
				} else if($current[$k] == "1") {
					Variable::set('curr_'.$k, false);
				}
			}
			if ($form->process(function () {
            	return true;
            })) {
                Base_BoxCommon::location('Utils_CurrencyField','admin');
            }
        }
        else $form->display();
		return true;
	}

	public function update_currencies_list() {
		Utils_CurrencyFieldInstall::create_currency_array();
	}

}

?>
