<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

class vrcCurrencyConverter {
	
	private $from_currency;
	private $to_currency;
	private $prices;
	private $format;
	private $currencymap;
	public 	$api_provider;
	
	public function __construct($from, $to, $numbers, $format)
	{
		$this->from_currency = $from;
		$this->to_currency = $to;
		$this->prices = $numbers;
		$this->format = $format;
		$this->currencymap = array(
				'ALL' => array('symbol' => '76'),
				'AFN' => array('symbol' => '1547'),
				'ARS' => array('symbol' => '36'),
				'AWG' => array('symbol' => '402'),
				'AUD' => array('symbol' => '36'),
				'AZN' => array('symbol' => '1084'),
				'BSD' => array('symbol' => '36'),
				'BBD' => array('symbol' => '36'),
				'BYR' => array('symbol' => '112', 'decimals' => 0),
				'BZD' => array('symbol' => '66'),
				'BMD' => array('symbol' => '36'),
				'BOB' => array('symbol' => '36'),
				'BAM' => array('symbol' => '75'),
				'BWP' => array('symbol' => '80'),
				'BGN' => array('symbol' => '1083'),
				'BRL' => array('symbol' => '82'),
				'BND' => array('symbol' => '36'),
				'KHR' => array('symbol' => '6107'),
				'CAD' => array('symbol' => '36'),
				'KYD' => array('symbol' => '36'),
				'CLP' => array('symbol' => '36', 'decimals' => 0),
				'CNY' => array('symbol' => '165'),
				'COP' => array('symbol' => '36'),
				'CRC' => array('symbol' => '8353'),
				'HRK' => array('symbol' => '107'),
				'CUP' => array('symbol' => '8369'),
				'CZK' => array('symbol' => '75'),
				'DKK' => array('symbol' => '107'),
				'DOP' => array('symbol' => '82'),
				'XCD' => array('symbol' => '36'),
				'EGP' => array('symbol' => '163'),
				'SVC' => array('symbol' => '36'),
				'EEK' => array('symbol' => '107'),
				'EUR' => array('symbol' => '8364'),
				'FKP' => array('symbol' => '163'),
				'FJD' => array('symbol' => '36'),
				'GHC' => array('symbol' => '162'),
				'GIP' => array('symbol' => '163'),
				'GTQ' => array('symbol' => '81'),
				'GGP' => array('symbol' => '163'),
				'GYD' => array('symbol' => '36'),
				'HNL' => array('symbol' => '76'),
				'HKD' => array('symbol' => '36'),
				'HUF' => array('symbol' => '70', 'decimals' => 0),
				'ISK' => array('symbol' => '107', 'decimals' => 0),
				'IDR' => array('symbol' => '82'),
				'INR' => array('symbol' => '8377'),
				'IRR' => array('symbol' => '65020'),
				'IMP' => array('symbol' => '163'),
				'ILS' => array('symbol' => '8362'),
				'JMD' => array('symbol' => '74'),
				'JPY' => array('symbol' => '165', 'decimals' => 0),
				'JEP' => array('symbol' => '163'),
				'KZT' => array('symbol' => '1083'),
				'KPW' => array('symbol' => '8361'),
				'KRW' => array('symbol' => '8361', 'decimals' => 0),
				'KGS' => array('symbol' => '1083'),
				'LAK' => array('symbol' => '8365'),
				'LVL' => array('symbol' => '76'),
				'LBP' => array('symbol' => '163'),
				'LRD' => array('symbol' => '36'),
				'LTL' => array('symbol' => '76'),
				'MKD' => array('symbol' => '1076'),
				'MYR' => array('symbol' => '82'),
				'MUR' => array('symbol' => '8360'),
				'MXN' => array('symbol' => '36'),
				'MNT' => array('symbol' => '8366'),
				'MZN' => array('symbol' => '77', 'decimals' => 0),
				'NAD' => array('symbol' => '36'),
				'NPR' => array('symbol' => '8360'),
				'ANG' => array('symbol' => '402'),
				'NZD' => array('symbol' => '36'),
				'NIO' => array('symbol' => '67'),
				'NGN' => array('symbol' => '8358'),
				'NOK' => array('symbol' => '107'),
				'OMR' => array('symbol' => '65020', 'decimals' => 3),
				'PKR' => array('symbol' => '8360'),
				'PAB' => array('symbol' => '66'),
				'PYG' => array('symbol' => '71', 'decimals' => 0),
				'PEN' => array('symbol' => '83'),
				'PHP' => array('symbol' => '8369'),
				'PLN' => array('symbol' => '122'),
				'QAR' => array('symbol' => '65020'),
				'RON' => array('symbol' => '108;&#101;&#105'),
				'RUB' => array('symbol' => '1088'),
				'SHP' => array('symbol' => '163'),
				'SAR' => array('symbol' => '65020'),
				'RSD' => array('symbol' => '1044'),
				'SCR' => array('symbol' => '8360'),
				'SGD' => array('symbol' => '36'),
				'SBD' => array('symbol' => '36'),
				'SOS' => array('symbol' => '83'),
				'ZAR' => array('symbol' => '82'),
				'LKR' => array('symbol' => '8360'),
				'SEK' => array('symbol' => '107'),
				'CHF' => array('symbol' => '67'),
				'SRD' => array('symbol' => '36'),
				'SYP' => array('symbol' => '163'),
				'TWD' => array('symbol' => '78'),
				'THB' => array('symbol' => '3647'),
				'TTD' => array('symbol' => '84'),
				'TRY' => array('symbol' => '8378', 'decimals' => 0),
				'UAH' => array('symbol' => '8372'),
				'GBP' => array('symbol' => '163'),
				'USD' => array('symbol' => '36'),
				'UYU' => array('symbol' => '36'),
				'UZS' => array('symbol' => '1083'),
				'VEF' => array('symbol' => '66'),
				'VND' => array('symbol' => '8363'),
				'YER' => array('symbol' => '65020'),
				'ZWD' => array('symbol' => '90')
		);
		
		//This is the API's Provider that the Class will use to retrieve the conversion rate.
		//Supported Providers: ECB, fixer (deprecated), yahoo (deprecated)
		$this->api_provider = 'ECB';
	}

	/**
	 * Deprecated call to the Yahoo Finance APIs (shut down in November 2017).
	 * Retrieve the conversion rate between base currency and symbol currency.
	 *
	 * @return 	mixed 	false in case of errors, float in case of success
	 */
	private function callProviderYahoo()
	{
		//http://finance.yahoo.com/currency-converter
		$apis_url = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='. $this->from_currency . $this->to_currency .'=X';
		$fp = @fopen($apis_url, 'r');
		if ($fp) {
			$data = '';
			while (!feof($fp)) {
				$data .= fread($fp, 4096);
			}
			if (!empty($data)) {
				$data = str_replace("\"", "", $data);
				$rate_info = explode(',', $data);
				if (strlen($rate_info[1]) > 0 && floatval($rate_info[1]) > 0.00) {
					return (float)$rate_info[1];
				}
			}
		}
		return false;
	}

	/**
	 * Call to the Fixer Foreign exchange rates and currency conversion API.
	 * Retrieve the conversion rate between base currency and symbol currency.
	 *
	 * @return 	mixed 	false in case of errors, float in case of success
	 */
	private function callProviderFixer()
	{
		//http://fixer.io/
		$apis_url = 'https://api.fixer.io/latest?'.($this->from_currency != 'EUR' ? 'base='.$this->from_currency.'&' : '').'symbols='.$this->to_currency;
		$fp = @fopen($apis_url, 'r');
		if ($fp) {
			$data = '';
			while (!feof($fp)) {
				$data .= fread($fp, 4096);
			}
			$resp = json_decode($data);
			if (is_object($resp) && property_exists($resp, 'rates') && is_object($resp->rates)) {
				$prop = $this->to_currency;
				if (property_exists($resp->rates, $prop) && floatval($resp->rates->$prop) > 0.00) {
					return (float)$resp->rates->$prop;
				}
			}
		}
		return false;
	}

	/**
	 * Call to the European Central Bank exchange rates XML data.
	 * Retrieve the conversion rate between base currency and symbol currency.
	 * The exchange rates data is cached by downloading the file every day.
	 *
	 * @return 	mixed 	false in case of errors, float in case of success
	 * 
	 * @since 	June 2018
	 */
	private function callProviderECB()
	{
		// load exchange rates
		$exchange_rates = $this->loadECBRates();
		if (!$exchange_rates) {
			// something went wrong
			return false;
		}

		if (!isset($exchange_rates[$this->to_currency]) && $this->to_currency != 'EUR') {
			// we do not have the exchange rate to this currency.
			return false;
		}
		
		if ($this->from_currency == 'EUR') {
			// converting from EUR to a known currency
			return $this->to_currency == 'EUR' ? 1 : $exchange_rates[$this->to_currency];
		}

		if (!isset($exchange_rates[$this->from_currency])) {
			// converting from this currency is not allowed as we do not know it
			return false;
		}

		if ($this->from_currency == $this->to_currency) {
			// equal currencies should be stopped before this method
			return 1;
		}

		if ($this->to_currency == 'EUR') {
			// converting to EUR from a known currency
			return (1 / $exchange_rates[$this->from_currency]);
		}

		// conversion is not involving EUR, but both currencies are known (from : to = 1 : x)
		return ($exchange_rates[$this->to_currency] / $exchange_rates[$this->from_currency]);
	}

	/**
	 * Call to the European Central Bank exchange rates XML data.
	 * Retrieves the conversion rates of all currencies by caching the results,
	 * and by returning an array map with key-value pairs of currencycode-rate from EUR.
	 *
	 * @return 	mixed 	false in case of errors, array in case of success
	 * 
	 * @since 	June 2018
	 */
	private function loadECBRates()
	{
		$rates_doc = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
		$cache_rates = dirname(__FILE__) . DIRECTORY_SEPARATOR . date('Y-m-d') . '_ecb.xml';
		$xml_data = '';

		if (!file_exists($cache_rates)) {
			// cached rates are not available, attempt to download the information
			$expcache = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . '*_ecb.xml');
			// remove old expired cache files
			foreach ($expcache as $expf) {
				if (is_file($expf)) {
					@unlink($expf);
				}
			}
			// download
			$fp = @fopen($rates_doc, 'r');
			if (!$fp) {
				// cannot read exchange rates from external XML file
				return false;
			}
			$xml_data = '';
			while (!feof($fp)) {
				$xml_data .= fread($fp, 4096);
			}
			fclose($fp);
			// store cache file
			$fp = @fopen($cache_rates, 'w+');
			if (!$fp) {
				// cannot open cache file for writing. Exit.
				return false;
			}
			fwrite($fp, $xml_data);
			fclose($fp);
		}

		if (!file_exists($cache_rates)) {
			// could not load rates
			return false;
		}

		if (empty($xml_data)) {
			// read cached rates from XML file
			$fp = @fopen($cache_rates, 'r');
			if (!$fp) {
				// cannot read exchange rates from cached XML file
				return false;
			}
			$xml_data = '';
			while (!feof($fp)) {
				$xml_data .= fread($fp, 4096);
			}
			fclose($fp);
		}

		$exchange_obj = simplexml_load_string($xml_data);
		if (!$exchange_obj instanceof SimpleXMLElement) {
			// this file does not contain correct XML
			return false;
		}
		
		$exchange_rates = array();
		foreach ($exchange_obj->Cube->Cube->Cube as $exrate) {
			$attr = $exrate->attributes();
			$currency = (string)$attr->currency;
			$rate = (float)$attr->rate;
			if ($rate > 0.00) {
				$exchange_rates[$currency] = $rate;
			}
		}

		return $exchange_rates;
	}
	
	private function getConversionRate()
	{
		$session = JFactory::getSession();
		$ses_conversions = $session->get('vrcCurrencyConversions', '');
		$conversions_made = array();
		$data = '';
		$conv_rate = false;
		
		if (!empty($ses_conversions) && @is_array($ses_conversions) && @count($ses_conversions) > 0) {
			$conversions_made = $ses_conversions;
			if (array_key_exists($this->from_currency.'_'.$this->to_currency, $ses_conversions)) {
				if (strlen($ses_conversions[$this->from_currency.'_'.$this->to_currency]) > 0 && floatval($ses_conversions[$this->from_currency.'_'.$this->to_currency]) > 0.00) {
					$conv_rate = $ses_conversions[$this->from_currency.'_'.$this->to_currency];
				}
			}
		}

		if ($conv_rate === false) {
			
			$api_method = 'callProvider'.str_replace(' ', '', ucwords($this->api_provider));
			if (!method_exists($this, $api_method)) {
				return false;
			}
			$conv_rate = $this->{$api_method}();
			if ($conv_rate !== false) {
				//cache conversion rate into the session
				$conversions_made[$this->from_currency.'_'.$this->to_currency] = $conv_rate;
				$session->set('vrcCurrencyConversions', $conversions_made);
			}

		}
		
		return $conv_rate;
	}
	
	private function makeFloat($num)
	{
		$floated = $num;
		if (@is_array($this->format) && @count($this->format) == 3) {
			$decimals = '';
			if (strstr($num, $this->format[1]) !== false) {
				$decimals = substr($num, ((int)$this->format[0] - ((int)$this->format[0] * 2)));
			}
			$nosep = str_replace($this->format[1], '', $num);
			$nosep = str_replace($this->format[2], '', $nosep);
			$newdecimals = '';
			if ((int)$this->format[0] > 0 && !empty($decimals)) {
				$nosep = substr_replace($nosep, '', (strlen($decimals) - (strlen($decimals) * 2)));
				$decimalsabs = abs($decimals);
				if ($decimalsabs > 0) {
					$newdecimals = $decimals;
				}
			}
			$floated = floatval($nosep.(!empty($newdecimals) ? '.'.$newdecimals : ''));
		}

		return $floated;
	}
	
	private function currencySymbol()
	{
		if (array_key_exists($this->to_currency, $this->currencymap)) {
			$symbol = '&#'.$this->currencymap[$this->to_currency]['symbol'].';';	
		}else {
			$symbol = $this->to_currency;
		}

		return $symbol;
	}
	
	private function currencyFormat($num)
	{
		$num_decimals = (int)$this->format[0];
		if (array_key_exists($this->to_currency, $this->currencymap)) {
			if (array_key_exists('decimals', $this->currencymap[$this->to_currency])) {
				$num_decimals = $this->currencymap[$this->to_currency]['decimals'];
			}else {
				$num_decimals = 2;
			}
		}

		return number_format($num, $num_decimals, $this->format[1], $this->format[2]);
	}
	
	public function convert()
	{
		$conversion = array();
		if (empty($this->prices) || count($this->prices) == 0) {
			return $conversion;
		}
		
		$conv_rate = $this->getConversionRate();
		
		if ($conv_rate !== false) {
			$conv_symbol = $this->currencySymbol();
			foreach($this->prices as $k => $price) {
				$exchanged = $this->makeFloat($price) * $conv_rate;
				$conversion[$k]['symbol'] = $conv_symbol;
				$conversion[$k]['price'] = $this->currencyFormat($exchanged);
			}
		}
		
		return $conversion;
	}
	
}
