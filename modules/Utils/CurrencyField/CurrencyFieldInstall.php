<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-utils
 * @subpackage CurrencyField
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

use Money\Currencies\ISOCurrencies;

class Utils_CurrencyFieldInstall extends ModuleInstall {

	public function install() {
		Base_ThemeCommon::install_default_theme($this->get_type());
		DB::CreateTable('utils_currency',
					'id I AUTO KEY,'.
					'symbol C(16),'.
					'code C(8),'.
					'decimal_sign C(2),'.
					'thousand_sign C(2),'.
					'decimals I1,'.
					'active I1,'.
					'default_currency I1,'.
					'pos_before I1',
					array('constraints'=>''));

        foreach(self::get_default_currencies_array() as $currency) {
            DB::Execute('INSERT INTO utils_currency (symbol, code, decimal_sign, thousand_sign, decimals, pos_before, active, default_currency) VALUES (%s, %s, %s, %s, %d, %d, %d, %d)',
                $currency);
        }

		DB::CreateTable('utils_cryptocurrencies',
            'id I AUTO KEY,'.
            'code C(8),'.
            'active I1,'.
            'default_currency I1',
            ['constraints'=>'']);

        foreach(self::get_default_crypto_array() as $crypto) {
            DB::Execute('INSERT INTO utils_cryptocurrencies (code, active, default_currency) VALUES (%s, %d, %d)',
                $crypto);
        }
        Utils_CommonDataCommon::new_array('Countries_Currencies', self::get_all_countries_currencies());
        self::create_currency_array();
        self::create_crypto_array();
        return true;
	}
	
	public function uninstall() {
		DB::DropTable('utils_currency');
		DB::DropTable('utils_cryptocurrencies');
        Utils_CommonDataCommon::remove('Countries_Currencies');
        Utils_CommonDataCommon::remove('Cryptocurrencies_Codes');
        Utils_CommonDataCommon::remove('Currencies_Codes');
		Base_ThemeCommon::uninstall_default_theme($this->get_type());
		return true;
	}
	
	public function requires($v) {
		return array(
			array('name'=>Base_ThemeInstall::module_name(), 'version'=>0),
			array('name'=>Base_LangInstall::module_name(), 'version'=>0),
			array('name'=>Base_User_SettingsInstall::module_name(), 'version'=>0),
			array('name'=>Utils_TooltipInstall::module_name(), 'version'=>0),
			array('name'=>Libs_LeightboxInstall::module_name(), 'version'=>0),
			array('name'=>Libs_QuickFormInstall::module_name(), 'version'=>0)
		);
	}


	public function get_default_currencies_array() {
        return [
            ['$', 'USD', '.', ',', 2, 1, 1, 1],
            ['€', 'EUR', '.', ',', 2, 1, 1, 0],
            ['£', 'GBP', '.', ',', 2, 1, 1, 0],
            ['zł', 'PLN', '.', ' ', 2, 0, 1, 0]
        ];
    }

    public function get_default_crypto_array() {
        return [
            ['BTC', 1, 1],
            ['ETH', 1, 0],
            ['LTC', 1, 0]
        ];
    }

    public static function get_crypto_list() {
        return (array) json_decode(file_get_contents('https://bittrex.com/api/v1.1/public/getcurrencies'));
    }

    public function get_all_countries_currencies() {

        return (array) json_decode(file_get_contents('http://country.io/currency.json'));
    }

    public function create_crypto_array() {
        $list = self::get_crypto_list()['result'];
        $result = [];
        foreach($list as $k => $v) {
            $result[$v->Currency] = $v->CurrencyLong;
        }
        Utils_CommonDataCommon::new_array('Cryptocurrencies_Codes',$result);
        return true;
    }

    public static function create_currency_array() {
        foreach(new ISOCurrencies() as $crypto) {
            $iso[$crypto->getCode()] = "";
        }
        $currencies = json_decode(file_get_contents('https://openexchangerates.org/api/currencies.json'));

        foreach($currencies as $k => $v) {
            if(key_exists($k, $iso)) $ret[$k] = $v;
        }
        Utils_CommonDataCommon::new_array('Currencies_Codes', $ret);
        return true;
    }
}

?>