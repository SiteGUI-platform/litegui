<?php
namespace LiteGUI\Traits;
require_once(__DIR__ .'/Http.php');
//Helper is used by both front controller and apps. 
//Its method shouldn't need a db connection because controller has dbm while apps have db
trait Helper {
    use Http;
    protected function hashify ($str, $static = false) {
        //salt must remain constant if the output is to be stored for long time.
        $salt = ($static)? '' : __DIR__ . $this->config['salt'];
        //$secret = @md5(getmyinode() . getlastmod()); unfortunately it isnt this file 
        $encrypted = base64_encode(@hash('sha256', $str .'::'. $salt .($this->config['static_hash']??'::Hj234r90knm21437un^&3389$%') ));

        return str_replace('=', '', strtr($encrypted, '+/', '-_')); //url-safe
    }

    //Url safe encryption
    protected function encode ($str, $static = false) {
        $iv_num_bytes = openssl_cipher_iv_length('aes-256-ctr');
        $iv = openssl_random_pseudo_bytes($iv_num_bytes, $isStrongCrypto);
        if (!$isStrongCrypto) {
            throw new \Exception("Traits\Helper::encode using not a strong key");
        }
        //salt must remain constant if the output is to be stored for long time.
        $salt = ($static)? '' : __DIR__ . $this->config['salt'];
        // Hash the key
        $keyhash = @openssl_digest($salt . ($this->config['static_salt']??'::2re897%^JH24P0AKNL24NO24'), 'sha256', true);

        $encrypted = base64_encode($iv . @openssl_encrypt($str, 'aes-256-ctr', $keyhash, OPENSSL_RAW_DATA, $iv));
        
        return str_replace('=', '', strtr($encrypted, '+/', '-_')); //url-safe
    }
    //if error about length then the input $str is not encoded, it should be encoded string with length > $iv_num_bytes (should checked length before running decode)
    protected function decode ($str, $static = false) {
        $iv_num_bytes = openssl_cipher_iv_length('aes-256-ctr');
        // Hash the key
        $salt = ($static)? '' : __DIR__ . $this->config['salt'];
        $keyhash = @openssl_digest($salt . ($this->config['static_salt']??'::2re897%^JH24P0AKNL24NO24'), 'sha256', true);

        $remainder = strlen($str) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $str .= str_repeat('=', $padlen);
        }
        $str = base64_decode(strtr($str, '-_', '+/'));
        
        if (strlen($str) < $iv_num_bytes)
        {
            echo ('Traits\Helper::decode - data length '. strlen($str) .' is less than iv length '. $iv_num_bytes);
        }
        $iv  = substr($str, 0, $iv_num_bytes);
        $encrypted = substr($str, $iv_num_bytes);

        return @openssl_decrypt($encrypted, 'aes-256-ctr', $keyhash, OPENSSL_RAW_DATA, $iv);
    }

    protected function createCaptcha($key){
        $max_number = 100000;
        $secret_number = mt_rand(0, $max_number);
        $salt = substr($_SESSION['token'], mt_rand(0,20), 12) .'?expires='. time() + 3600;// .'sec='. $secret_number;
        $challenge = bin2hex(hash('sha256', $salt . $secret_number, true));
        $signature = bin2hex(hash_hmac('sha256', $challenge, $key, true));
        return json_encode([
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $max_number,
            'salt' => $salt,
            'signature' => $signature
        ]);
    }

    protected function verifyCaptcha($key, $payload){
        $payload = json_decode(base64_decode($payload??''), true);
        //print_r($payload);
        if (empty($payload['algorithm']) OR 
            empty($payload['salt']) OR 
            empty($payload['number']) OR 
            empty($payload['challenge']) OR 
            empty($payload['signature']) OR 
            $payload['algorithm'] != 'SHA-256'
        ){
            return false;
        }

        if ( str_contains($payload['salt'], 'expires=') ){
            parse_str(str_replace('?', '&', $payload['salt']), $params);
            if (!empty($params['expires']) && $params['expires'] < time()){
                return false;    
            } 
        }

        if ( !hash_equals($payload['challenge'], bin2hex(hash('sha256', $payload['salt'] . $payload['number'], true))) ){
            return false;
        } 

        if ( !hash_equals($payload['signature'], bin2hex(hash_hmac('sha256', $payload['challenge'], $key, true))) ){
            return false;   
        } 

        return true;
    }

    //translate and replace variables provided by $vars, :name, :Name, :NAME will be replaced using corresponding cases.
    /*
    $this->trans(
        [
            0 =>  'Click <a href=":link">here</a> to create 0 new :type', 
            1 =>  '<a href=":link">Click here to create a new :type</a>', 
            15 => 'Click <a href=":link">here</a> to create many :types'
        ],
        [   
            'link' => $links['edit'], 
            'type' => $type
        ],
        $count
    );
    */
    function trans($str, $vars = '', $plural = 0)
    {   
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                if ($plural >= $key) $choosen = $value;
            }
            $str = isset($choosen)? $choosen : array_shift($str);
        }
        if (!empty($str) AND !empty($this->config['lang'][$str])) {
            $str = $this->config['lang'][$str];
        }
        if (is_array($vars) AND !empty($vars)) {
            foreach ($vars as $key => $value) {
                $value = $this->trans($value); //also translate vars
                $search[] = ':'. $key;
                $replace[] = $value;
                
                $search[] = ':'. strtoupper($key);
                $replace[] = strtoupper($value);
                
                $search[] = ':'. ucfirst($key);
                $replace[] = ucfirst($value);
            } 
            $str = str_replace($search, $replace, $str);           
        }
        return $str;
    }

    function _e($str, $vars = '', $plural = 0)
    {
        return $this->trans($str, $vars, $plural);
    }

    protected function sanitizeFileName($filename) {
        $special_chars = array(
            '?',
            '[',
            ']',
            '\\',
            '+',
            '=',
            '<',
            '>',
            ':',
            ';',
            ',',
            '\'',
            '"',
            '&',
            (''. '$'),
            '#',
            '*',
            '@',
            '%',
            '^',
            '(',
            ')',
            '|',
            '~',
            '`',
            '!',
            '{',
            '}',
            chr(0)
        );
        $utf8 = [
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
            //vietnamese
            '/[áàảãạăắặằẳẵâấầẩẫậ]/u'         => 'a',
            '/đ/'                            => 'd',
            '/[éèẻẽẹêếềểễệ]/u'               => 'e',                
            '/[íìỉĩị]/u'                     => 'i',
            '/[óòỏõọôốồổỗộơớờởỡợ]/u'         => 'o',
            '/[úùủũụưứừửữự]/u'               => 'u',
            '/[ýỳỷỹỵ]/u'                     => 'y',
            '/[ÁÀẢÃẠĂẮẶẰẲẴÂẤẦẨẪẬ]/u'         => 'A',
            '/Đ/'                            => 'D',
            '/[ÉÈẺẼẸÊẾỀỂỄỆ]/u'               => 'E',
            '/[ÍÌỈĨỊ]/u'                     => 'I',
            '/[ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢ]/u'         => 'O',
            '/[ÚÙỦŨỤƯỨỪỬỮỰ]/u'               => 'U',
            '/[ÝỲỶỸỴ]/u'                     => 'Y',
        ];

        $filename = str_replace($special_chars, '', $filename);
        $filename = preg_replace(array_keys($utf8), $utf8, $filename); // translate special characters 
        $filename = preg_replace(['@[\s-]+@', '@/+@'], ['-', '/'], $filename); // double-slashes could be used to comment out 
        $filename = trim($filename, '.-_');
        return mb_strtolower($filename);
    }

    function formatAppName($slug) {
        //return name in alphanumeric and -_ only, //urldecode to support 💬
        return ucwords( basename( $this->sanitizeFileName(urldecode($slug)) ), '_');//user_management => User_Management
    }
    function formatAppLabel($slug) {
        return ucwords(str_replace(['_', '-'], ' ', basename( $this->sanitizeFileName(urldecode($slug)) ))); //User_Management => User Management
    }
    function trimRootNs($namespace){
        if (strpos($namespace, '\\') !== false){
            return substr($namespace, strpos($namespace, '\\') + 1); //remove root namespace SiteGUI\ABC\XYZ => ABC\XYZ
        } else {
            return $namespace;
        }    
    }

    static function getCountry($c){
        return json_decode('{"AF":"Afghanistan","AX":"Aland Islands","AL":"Albania","DZ":"Algeria","AD":"Andorra","AO":"Angola","AI":"Anguilla","AQ":"Antarctica","AG":"Antigua &amp; Barbuda","AR":"Argentina","AM":"Armenia","AW":"Aruba","AC":"Ascension Island","AU":"Australia","AT":"Austria","AZ":"Azerbaijan","BS":"Bahamas","BH":"Bahrain","BD":"Bangladesh","BB":"Barbados","BY":"Belarus","BE":"Belgium","BZ":"Belize","BJ":"Benin","BM":"Bermuda","BT":"Bhutan","BO":"Bolivia","BA":"Bosnia &amp; Herzegovina","BW":"Botswana","BV":"Bouvet Island","BR":"Brazil","IO":"British Indian Ocean Territory","VG":"British Virgin Islands","BN":"Brunei","BG":"Bulgaria","BF":"Burkina Faso","BI":"Burundi","KH":"Cambodia","CM":"Cameroon","CA":"Canada","CV":"Cape Verde","BQ":"Caribbean Netherlands","KY":"Cayman Islands","CF":"Central African Republic","TD":"Chad","CL":"Chile","CN":"China","CO":"Colombia","KM":"Comoros","CG":"Congo - Brazzaville","CD":"Congo - Kinshasa","CK":"Cook Islands","CR":"Costa Rica","CI":"Côte d’Ivoire","HR":"Croatia","CW":"Curaçao","CY":"Cyprus","CZ":"Czechia","DK":"Denmark","DJ":"Djibouti","DM":"Dominica","DO":"Dominican Republic","EC":"Ecuador","EG":"Egypt","SV":"El Salvador","GQ":"Equatorial Guinea","ER":"Eritrea","EE":"Estonia","SZ":"Eswatini","ET":"Ethiopia","FK":"Falkland Islands","FO":"Faroe Islands","FJ":"Fiji","FI":"Finland","FR":"France","GF":"French Guiana","PF":"French Polynesia","TF":"French Southern Territories","GA":"Gabon","GM":"Gambia","GE":"Georgia","DE":"Germany","GH":"Ghana","GI":"Gibraltar","GR":"Greece","GL":"Greenland","GD":"Grenada","GP":"Guadeloupe","GU":"Guam","GT":"Guatemala","GG":"Guernsey","GN":"Guinea","GW":"Guinea-Bissau","GY":"Guyana","HT":"Haiti","HN":"Honduras","HK":"Hong Kong SAR China","HU":"Hungary","IS":"Iceland","IN":"India","ID":"Indonesia","IQ":"Iraq","IE":"Ireland","IM":"Isle of Man","IL":"Israel","IT":"Italy","JM":"Jamaica","JP":"Japan","JE":"Jersey","JO":"Jordan","KZ":"Kazakhstan","KE":"Kenya","KI":"Kiribati","XK":"Kosovo","KW":"Kuwait","KG":"Kyrgyzstan","LA":"Laos","LV":"Latvia","LB":"Lebanon","LS":"Lesotho","LR":"Liberia","LY":"Libya","LI":"Liechtenstein","LT":"Lithuania","LU":"Luxembourg","MO":"Macao SAR China","MG":"Madagascar","MW":"Malawi","MY":"Malaysia","MV":"Maldives","ML":"Mali","MT":"Malta","MQ":"Martinique","MR":"Mauritania","MU":"Mauritius","YT":"Mayotte","MX":"Mexico","MD":"Moldova","MC":"Monaco","MN":"Mongolia","ME":"Montenegro","MS":"Montserrat","MA":"Morocco","MZ":"Mozambique","MM":"Myanmar (Burma)","NA":"Namibia","NR":"Nauru","NP":"Nepal","NL":"Netherlands","NC":"New Caledonia","NZ":"New Zealand","NI":"Nicaragua","NE":"Niger","NG":"Nigeria","NU":"Niue","MK":"North Macedonia","NO":"Norway","OM":"Oman","PK":"Pakistan","PS":"Palestinian Territories","PA":"Panama","PG":"Papua New Guinea","PY":"Paraguay","PE":"Peru","PH":"Philippines","PN":"Pitcairn Islands","PL":"Poland","PT":"Portugal","PR":"Puerto Rico","QA":"Qatar","KR":"Republic of Korea","RE":"Réunion","RO":"Romania","RU":"Russia","RW":"Rwanda","WS":"Samoa","SM":"San Marino","ST":"São Tomé &amp; Príncipe","SA":"Saudi Arabia","SN":"Senegal","RS":"Serbia","SC":"Seychelles","SL":"Sierra Leone","SG":"Singapore","SX":"Sint Maarten","SK":"Slovakia","SI":"Slovenia","SB":"Solomon Islands","SO":"Somalia","ZA":"South Africa","GS":"South Georgia &amp; South Sandwich Islands","SS":"South Sudan","ES":"Spain","LK":"Sri Lanka","BL":"St. Barthélemy","SH":"St. Helena","KN":"St. Kitts &amp; Nevis","LC":"St. Lucia","MF":"St. Martin","PM":"St. Pierre &amp; Miquelon","VC":"St. Vincent &amp; Grenadines","SR":"Suriname","SJ":"Svalbard &amp; Jan Mayen","SE":"Sweden","CH":"Switzerland","TW":"Taiwan","TJ":"Tajikistan","TZ":"Tanzania","TH":"Thailand","TL":"Timor-Leste","TG":"Togo","TK":"Tokelau","TO":"Tonga","TT":"Trinidad &amp; Tobago","TA":"Tristan da Cunha","TN":"Tunisia","TR":"Turkey","TM":"Turkmenistan","TC":"Turks &amp; Caicos Islands","TV":"Tuvalu","UG":"Uganda","UA":"Ukraine","AE":"United Arab Emirates","GB":"United Kingdom","US":"United States","UY":"Uruguay","UZ":"Uzbekistan","VU":"Vanuatu","VA":"Vatican City","VE":"Venezuela","VN":"Vietnam","WF":"Wallis &amp; Futuna","EH":"Western Sahara","YE":"Yemen","ZM":"Zambia","ZW":"Zimbabwe"}')?->$c;
    }
    static function getLanguages($k = '') { //https://www.alchemysoftware.com/livedocs/ezscript/Topics/Catalyst/Language.htm
        $langs = array(
            'aa' => 'afar',
            'ab' => 'abkhaz',
            'ae' => 'avestan',
            'af' => 'afrikaans',
            'ak' => 'akan',
            'am' => 'amharic',
            'an' => 'aragonese',
            'ar' => 'arabic',
            'ar-bh' => 'arabic (BH)',
            'ar-dz' => 'arabic (DZ)',
            'ar-eg' => 'arabic (EG)',
            'ar-iq' => 'arabic (IQ)',
            'ar-jo' => 'arabic (JO)',
            'ar-kw' => 'arabic (KW)',
            'ar-lb' => 'arabic (LB)',
            'ar-ly' => 'arabic (LY)',
            'ar-ma' => 'arabic (MA)',
            'ar-om' => 'arabic (OM)',
            'ar-qa' => 'arabic (QA)',
            'ar-sa' => 'arabic (SA)',
            'ar-sy' => 'arabic (SY)',
            'ar-tn' => 'arabic (TN)',
            'ar-ae' => 'arabic (AE)',
            'ar-ye' => 'arabic (YE)',
            'as' => 'assamese',
            'av' => 'avaric',
            'ay' => 'aymara',
            'az' => 'azerbaijani',
            'ba' => 'bashkir',
            'be' => 'belarusian',
            'bg' => 'bulgarian',
            'bh' => 'bihari',
            'bi' => 'bislama',
            'bm' => 'bambara',
            'bn' => 'bengali',
            'bo' => 'tibetan',
            'br' => 'breton',
            'bs' => 'bosnian',
            'ca' => 'catalan',
            'ce' => 'chechen',
            'ch' => 'chamorro',
            'co' => 'corsican',
            'cr' => 'cree',
            'cs' => 'czech',
            'cu' => 'church slavic',
            'cv' => 'chuvash',
            'cy' => 'welsh',
            'da' => 'danish',
            'de' => 'german',
            'de-at' => 'german (AT)',
            'de-ch' => 'german (CH)',
            'de-li' => 'german (LI)',
            'de-lu' => 'german (LU)',
            'dv' => 'divehi',
            'dz' => 'dzongkha',
            'ee' => 'ewe',
            'el' => 'greek',
            'en' => 'english',
            'en-au' => 'english (AU)',
            'en-ca' => 'english (CA)',
            'en-hk' => 'english (HK)',
            'en-ie' => 'english (IE)',
            'en-in' => 'english (IN)',
            'en-my' => 'english (MY)',
            'en-nz' => 'english (NZ)',
            'en-ph' => 'english (PH)',
            'en-za' => 'english (SA)',
            'en-sg' => 'english (SG)',
            'en-gb' => 'english (UK)',
            'eo' => 'esperanto',
            'es' => 'spanish',
            'es-ar' => 'spanish (AR)',
            'es-bo' => 'spanish (BO)',
            'es-cl' => 'spanish (CL)',
            'es-co' => 'spanish (CO)',
            'es-cr' => 'spanish (CR)',
            'es-cu' => 'spanish (CU)',
            'es-do' => 'spanish (DO)',
            'es-ec' => 'spanish (EC)',
            'es-gt' => 'spanish (GT)',
            'es-hn' => 'spanish (HN)',
            'es-mx' => 'spanish (MX)',
            'es-ni' => 'spanish (NI)',
            'es-pa' => 'spanish (PA)',
            'es-pe' => 'spanish (PE)',
            'es-pr' => 'spanish (PR)',
            'es-py' => 'spanish (PY)',
            'es-sv' => 'spanish (SV)',
            'es-us' => 'spanish (US)',
            'es-uy' => 'spanish (UY)',
            'es-ve' => 'spanish (VE)',
            'et' => 'estonian',
            'eu' => 'basque',
            'fa' => 'persian',
            'ff' => 'fula',
            'fi' => 'finnish',
            'fj' => 'fijian',
            'fo' => 'faroese',
            'fr' => 'french',
            'fr-be' => 'french (BE)',
            'fr-ch' => 'french (CH)',
            'fr-ci' => 'french (CI)',
            'fr-cm' => 'french (CM)',
            'fr-ca' => 'french (CA)',
            'fr-cd' => 'french (CD)',
            'fr-ht' => 'french (HT)',
            'fr-lu' => 'french (LU)',
            'fr-ml' => 'french (ML)',
            'fr-ma' => 'french (MA)',
            'fr-re' => 'french (RE)',
            'fr-sn' => 'french (SN)',
            'fy' => 'western frisian',
            'ga' => 'irish',
            'gd' => 'gaelic',
            'gl' => 'galician',
            'gn' => 'guaranã­',
            'gu' => 'gujarati',
            'gv' => 'manx',
            'ha' => 'hausa',
            'he' => 'hebrew',
            'hi' => 'hindi',
            'ho' => 'hiri motu',
            'hr' => 'croatian',
            'ht' => 'haitian',
            'hu' => 'hungarian',
            'hy' => 'armenian',
            'hz' => 'herero',
            'ia' => 'interlingua',
            'id' => 'indonesian',
            'ie' => 'interlingue',
            'ig' => 'igbo',
            'ii' => 'nuosu',
            'ik' => 'inupiaq',
            'io' => 'ido',
            'is' => 'icelandic',
            'it' => 'italian',
            'iu' => 'inuktitut',
            'ja' => 'japanese',
            'jv' => 'javanese',
            'ka' => 'georgian',
            'kg' => 'kongo',
            'ki' => 'kikuyu',
            'kj' => 'kwanyama',
            'kk' => 'kazakh',
            'kl' => 'kalaallisut',
            'km' => 'khmer',
            'kn' => 'kannada',
            'ko' => 'korean',
            'kr' => 'kanuri',
            'ks' => 'kashmiri',
            'ku' => 'kurdish',
            'kv' => 'komi',
            'kw' => 'cornish',
            'ky' => 'kyrgyz',
            'la' => 'latin',
            'lb' => 'luxembourgish',
            'lg' => 'luganda',
            'li' => 'limburgish',
            'ln' => 'lingala',
            'lo' => 'lao',
            'lt' => 'lithuanian',
            'lu' => 'luba-katanga',
            'lv' => 'latvian',
            'mg' => 'malagasy',
            'mh' => 'marshallese',
            'mi' => 'maori',
            'mk' => 'macedonian',
            'ml' => 'malayalam',
            'mn' => 'mongolian',
            'mr' => 'marathi',
            'ms' => 'malay',
            'mt' => 'maltese',
            'my' => 'burmese',
            'na' => 'nauru',
            'nb' => 'norwegian bokmal',
            'nd' => 'north ndebele',
            'ne' => 'nepali',
            'ng' => 'ndonga',
            'nl' => 'dutch',
            'nn' => 'norwegian nynorsk',
            'no' => 'norwegian',
            'nr' => 'south ndebele',
            'nv' => 'navajo',
            'ny' => 'nyanja',
            'oc' => 'occitan',
            'oj' => 'ojibwe',
            'om' => 'oromo',
            'or' => 'oriya',
            'os' => 'ossetian',
            'pa' => 'panjabi',
            'pi' => 'pali',
            'pl' => 'polish',
            'ps' => 'pashto',
            'pt' => 'portuguese',
            'qu' => 'quechua',
            'rm' => 'romansh',
            'rn' => 'kirundi',
            'ro' => 'romanian',
            'ru' => 'russian',
            'rw' => 'kinyarwanda',
            'sa' => 'sanskrit',
            'sc' => 'sardinian',
            'sd' => 'sindhi',
            'se' => 'northern sami',
            'sg' => 'sango',
            'si' => 'sinhala',
            'sk' => 'slovak',
            'sl' => 'slovene',
            'sm' => 'samoan',
            'sn' => 'shona',
            'so' => 'somali',
            'sq' => 'albanian',
            'sr' => 'serbian',
            'ss' => 'swati',
            'st' => 'southern sotho',
            'su' => 'sundanese',
            'sv' => 'swedish',
            'sw' => 'swahili',
            'ta' => 'tamil',
            'te' => 'telugu',
            'tg' => 'tajik',
            'th' => 'thai',
            'ti' => 'tigrinya',
            'tk' => 'turkmen',
            'tl' => 'tagalog',
            'tn' => 'tswana',
            'to' => 'tonga',
            'tr' => 'turkish',
            'ts' => 'tsonga',
            'tt' => 'tatar',
            'tw' => 'twi',
            'ty' => 'tahitian',
            'ug' => 'uighur',
            'uk' => 'ukrainian',
            'ur' => 'urdu',
            'uz' => 'uzbek',
            've' => 'venda',
            'vi' => 'Tiếng Việt',
            'vo' => 'volapak',
            'wa' => 'walloon',
            'wo' => 'wolof',
            'xh' => 'xhosa',
            'yi' => 'yiddish',
            'yo' => 'yoruba',
            'za' => 'zhuang',
            'zh' => '中文',
            'zh-hk' => '中文 (HK)',
            'zh-mo' => '中文 (MO)',
            'zh-sg' => '华语 (SG)',
            'zh-tw' => '国语 (TW)',
            'zu' => 'zulu',
        );
        return !empty($k)? $langs[$k] : $langs;   
    }

    function getTimezones(){
        $timestamp = time();
        $current_tz = date_default_timezone_get();
        foreach(timezone_identifiers_list() as $key => $tz) {
            date_default_timezone_set($tz);
            $zones[$key]['identifier'] = $tz;
            $zones[$key]['offset'] = date('P', $timestamp);
        }

        // Sort the array by offset,identifier ascending
        usort($zones, function($a, $b) {
            return ($a['offset'] == $b['offset'])? strcmp($a['identifier'], $b['identifier']) 
                : str_replace(':', '', $a['offset']) - str_replace(':', '', $b['offset']);
        });
        //restore current tz
        date_default_timezone_set($current_tz);
        
        return $zones;
    }

    /*May cause confusion - used real (obfus) id instead
    function encodeId ($id) {
        return 100*intval($id);
    }

    function decodeId ($id) {
        return intval($id/100);
    }*/
    /**
     * Pluralizes English nouns.
     *
     * @access public
     * @static
     * @param    string    $word    English noun to pluralize
     * @return string Plural noun
     */
    function pluralize($word){
        $plural = array(
            '/(quiz)$/i'               => '\1zes',
            '/^(ox)$/i'                => '\1en',
            '/([m|l])ouse$/i'          => '\1ice',
            '/(matr|vert|ind)ix|ex$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i'         => '\1es',
            '/([^aeiouy]|qu)ies$/i'    => '\1y',
            '/([^aeiouy]|qu)y$/i'      => '\1ies',
            '/(hive)$/i'               => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i'                  => 'ses',
            '/([ti])um$/i'             => '\1a',
            '/(buffal|tomat)o$/i'      => '\1oes',
            '/(bu)s$/i'                => '\1ses',
            '/(alias|status)/i'        => '\1es',
            '/(octop|vir)us$/i'        => '\1i',
            '/(ax|test)is$/i'          => '\1es',
            '/s$/i'                    => 's',
            '/$/'                      => 's');

        $uncountable = [
            'equipment', 
            'information', 
            'rice', 
            'money', 
            'fish', 
            'sheep', 
            'appstore',
            'staff', 
            'cart', 
            'config', 
            'upgrade',
            'freelance',
            'hardware',
            'software',
            'shipping',
            'blog',
            '💬',
            '👍',
            '👎🏾',
            '♡',
        ];

        $irregular = [
            'person' => 'people',
            'man'    => 'men',
            'child'  => 'children',
            'sex'    => 'sexes',
        ];

        $lowercased_word = strtolower($word);

        foreach($uncountable as $_uncountable){
            if(substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable){
                return $word;
            }
        }

        foreach($irregular as $_plural => $_singular){
            if(preg_match('/(' . $_plural . ')$/i', $word, $arr)){
                return preg_replace('/(' . $_plural . ')$/i', substr($arr[0], 0, 1) . substr($_singular, 1), $word);
            }
        }

        foreach($plural as $rule => $replacement){
            if(preg_match($rule, $word)){
                return preg_replace($rule, $replacement, $word);
            }
        }
        return false;
    }
    // remove array items matching value, e.g: remove empty value except string 0 
    function array_remove_by_values(array $haystack, array $values = [ '', false, null, [] ]) {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = self::array_remove_by_values($haystack[$key], $values);
            }

            if (in_array($haystack[$key], $values, true)) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }
    
    protected function flatten($arr, &$flatten, $pre = ''){
        if (is_array($arr)){
            foreach ($arr??[] AS $k => $v){
                if (is_array($v)){
                    $this->flatten($v, $flatten, $pre .'__'. $k );
                } else {
                    $flatten[ trim($pre .'__'. $k, '_') ] = $v;
                }
            }       
        } else {
            $flatten[ trim($pre, '_') ] = $arr;
        }
    }   
    protected function flatten2($arr, $pre = ''){
        $flatten = [];
        if (is_array($arr)){
            foreach ($arr??[] AS $k => $v){
                if (is_array($v)){
                    $flatten += $this->flatten2($v, $pre .'__'. $k );
                } else {
                    $flatten[ trim($pre .'__'. $k, '_') ] = $v;
                }
            }
        } else {
            $flatten[ trim($pre, '_') ] = $arr;
        }   
        return $flatten;
    }
    //Convert a string into an array using __ as the separator, $pre can be used to select only keys equal or starting with $pre__
    protected function unflatten(&$arr, $pre = ''){
        if (is_array($arr)){
            foreach ($arr??[] AS $k => $v){
                $parts = null;
                if ($pre){
                    if ($k == $pre OR str_starts_with($k, $pre .'__') ){
                       $parts = explode('__', $k);     
                    }
                } elseif ( str_contains($k, '__') ){
                    $parts = explode('__', $k);
                }
                if ($parts){
                    $temp = &$arr;
                    foreach ($parts as $part) {
                        $temp = &$temp[$part];
                    }
                    $temp = $v;
                }
            }   
        }
    }

    function console_log($output, $with_tags = true) {
        $js_code = 'console.log('. json_encode($output, JSON_HEX_TAG) .');';
        if ($with_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }
}    
?>