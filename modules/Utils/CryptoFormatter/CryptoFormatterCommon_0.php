<?php
defined("_VALID_ACCESS") || die('Direct access forbidden');

use Money\Number;

class Utils_CryptoFormatterCommon extends ModuleCommon {

    public function handle_value ($val, $currency_digits = 8 )
    {
        switch (gettype($val)) {
            case 'integer':
                return self::handle_integer($val, $currency_digits);
            case 'double':
                return self::handle_double($val, $currency_digits);
            case 'string':
                if (is_numeric($val)) {
                    switch(gettype($val + 0)) {
                        case 'integer':
                            return self::handle_integer($val, $currency_digits);
                        case 'double':
                            return self::handle_double($val, $currency_digits);
                    }
                }
                return null;
        }
    }

    public static function format_cryptocurrency ($val, $currency, $currency_digits = 8 )
    {
        if (!in_array($currency, array_values(Utils_CurrencyFieldCommon::get_all_cryptocurrencies()))) {
            return 'Wrong currency given';
        }
        return $currency . ' ' . self::handle_value($val, $currency_digits);
    }

    public function handle_integer ($val, $currency_digits) {
        return $val.'.'.str_pad('',$currency_digits, '0', STR_PAD_LEFT);
    }

    public function handle_double ($val, $currency_digits) {
        $val_in_sat = (number_format($val, $currency_digits) * pow(10, $currency_digits));
        $length = ceil(log10(abs($val_in_sat) + 1));
        if ($length > $currency_digits) {
            $wholes = substr((string)$val, 0, ($length - $currency_digits));
            $string = str_replace('.','',(string)$val);
            $satoshis = substr($string, -$currency_digits);
            return $wholes.'.'.str_pad($satoshis, 8,'0', STR_PAD_LEFT);
        } else {
            return "0.".str_pad($val_in_sat,$currency_digits,'0', STR_PAD_LEFT);
        }
    }
}
