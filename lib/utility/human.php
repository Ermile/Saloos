<?php
namespace lib\utility;

/** human: prenent human value **/
class human
{
	/**
	 * this library allow to change values to human type!
	 * v1.1
	 */


	/**
	 * [time description]
	 * @param  [type] $_time [description]
	 * @param  string $_from [description]
	 * @return [type]        [description]
	 */
	public static function time($_time, $_resultType = 'text', $_from = 'min')
	{
		// change from sec to min
		if($_from === 'sec')
		{
			$_time = floor($_time / 60);
		}
		$result = '';
		$hour   = floor($_time / 60);
		$min    = floor($_time % 60);

		// generate result by type of request
		switch ($_resultType)
		{
			case 'number':
				$result = $hour. ':'. $min;
				break;

			case 'text':
			default:
				if(is_int($hour))
				{
					$result = $hour. T_('hour');
				}
				if($min)
				{
					if($result)
					{
						$result = ', '. $min. T_('minute');
					}
					else
					{
						$result = $min. T_('minute');
					}
				}
				break;
		}

		// change numbers to selected language
		switch ($_resultType)
		{
			case 'persian':
			case 'fa':
			case 'english':
			case 'en':
			case 'arabic':
			case 'ar':
				$result = self::number($result, $_resultType);
				break;

			default:
				break;
		}
		// return final result
		return $result;
	}


	/**
	 * Converts all numbers to persian number
	 * @param  [type] $_txt input text or numbers
	 * @return [type]       [description]
	 */
	public static function number($_txt, $_lang = 'english')
	{
		$persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
		$arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
		$english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

		switch ($_lang)
		{
			case 'persian':
			case 'farsi':
			case 'fa':
				// convert english and arabic numbers to persian number
				$_txt = str_replace($english, $persian, $_txt);
				$_txt = str_replace($arabic, $persian, $_txt);
				break;

			case 'arabic':
			case 'ar':
				// convert english and arabic numbers to persian number
				$_txt = str_replace($persian, $arabic, $_txt);
				$_txt = str_replace($english, $arabic, $_txt);
				break;

			case 'english':
			case 'en':
			default:
				// convert english and arabic numbers to persian number
				$_txt = str_replace($persian, $english, $_txt);
				$_txt = str_replace($arabic, $english, $_txt);
				break;
		}
		// return result in selected language
		return $_txt;
	}


	/**
	 * Check if there RTL characters (Arabic, Persian, Hebrew)
	 * @param  [type]  $_string [description]
	 * @return boolean          [description]
	 */
	public static function is_rtl($_string)
	{
		$rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
		return preg_match($rtl_chars_pattern, $_string);
	}


	/**
	* Replace none persian character with persian character.
	* This method covers most character in arabic character table.
	*
	* @param string $_text
	* @return string
	*/
	public static function persian_text($_text)
	{
		$from   = [];
		$to     = [];
		$from[] = ['؆','؇','؈','؉','؊','؍','؎','ؐ','ؑ','ؒ','ؓ','ؔ','ؕ','ؖ','ؘ','ؙ','ؚ','؞','ٖ','ٗ','٘','ٙ','ٚ','ٛ','ٜ','ٝ','ٞ','ٟ','٪','٬','٭','ہ','ۂ','ۃ','۔','ۖ','ۗ','ۘ','ۙ','ۚ','ۛ','ۜ','۞','۟','۠','ۡ','ۢ','ۣ','ۤ','ۥ','ۦ','ۧ','ۨ','۩','۪','۫','۬','ۭ','ۮ','ۯ','ﮧ','﮲','﮳','﮴','﮵','﮶','﮷','﮸','﮹','﮺','﮻','﮼','﮽','﮾','﮿','﯀','﯁','ﱞ','ﱟ','ﱠ','ﱡ','ﱢ','ﱣ','ﹰ','ﹱ','ﹲ','ﹳ','ﹴ','ﹶ','ﹷ','ﹸ','ﹹ','ﹺ','ﹻ','ﹼ','ﹽ','ﹾ','ﹿ'];
		$to[]   = '';
		$from[] = ['أ','إ','ٱ','ٲ','ٳ','ٵ','ݳ','ݴ','ﭐ','ﭑ','ﺃ','ﺄ','ﺇ','ﺈ','ﺍ','ﺎ','𞺀','ﴼ','ﴽ','𞸀'];
		$to[]   = 'ا';
		$from[] = ['ٮ','ݕ','ݖ','ﭒ','ﭓ','ﭔ','ﭕ','ﺏ','ﺐ','ﺑ','ﺒ','𞸁','𞸜','𞸡','𞹡','𞹼','𞺁','𞺡'];
		$to[]   = 'ب';
		$from[] = ['ڀ','ݐ','ݔ','ﭖ','ﭗ','ﭘ','ﭙ','ﭚ','ﭛ','ﭜ','ﭝ'];
		$to[]   = 'پ';
		$from[] = ['ٹ','ٺ','ٻ','ټ','ݓ','ﭞ','ﭟ','ﭠ','ﭡ','ﭢ','ﭣ','ﭤ','ﭥ','ﭦ','ﭧ','ﭨ','ﭩ','ﺕ','ﺖ','ﺗ','ﺘ','𞸕','𞸵','𞹵','𞺕','𞺵'];
		$to[]   = 'ت';
		$from[] = ['ٽ','ٿ','ݑ','ﺙ','ﺚ','ﺛ','ﺜ','𞸖','𞸶','𞹶','𞺖','𞺶'];
		$to[]   = 'ث';
		$from[] = ['ڃ','ڄ','ﭲ','ﭳ','ﭴ','ﭵ','ﭶ','ﭷ','ﭸ','ﭹ','ﺝ','ﺞ','ﺟ','ﺠ','𞸂','𞸢','𞹂','𞹢','𞺂','𞺢'];
		$to[]   = 'ج';
		$from[] = ['ڇ','ڿ','ݘ','ﭺ','ﭻ','ﭼ','ﭽ','ﭾ','ﭿ','ﮀ','ﮁ','𞸃','𞺃'];
		$to[]   = 'چ';
		$from[] = ['ځ','ݮ','ݯ','ݲ','ݼ','ﺡ','ﺢ','ﺣ','ﺤ','𞸇','𞸧','𞹇','𞹧','𞺇','𞺧'];
		$to[]   = 'ح';
		$from[] = ['ڂ','څ','ݗ','ﺥ','ﺦ','ﺧ','ﺨ','𞸗','𞸷','𞹗','𞹷','𞺗','𞺷'];
		$to[]   = 'خ';
		$from[] = ['ڈ','ډ','ڊ','ڌ','ڍ','ڎ','ڏ','ڐ','ݙ','ݚ','ﺩ','ﺪ','𞺣','ﮂ','ﮃ','ﮈ','ﮉ'];
		$to[]   = 'د';
		$from[] = ['ﱛ','ﱝ','ﺫ','ﺬ','𞸘','𞺘','𞺸','ﮄ','ﮅ','ﮆ','ﮇ'];
		$to[]   = 'ذ';
		$from[] = ['٫','ڑ','ڒ','ړ','ڔ','ڕ','ږ','ݛ','ݬ','ﮌ','ﮍ','ﱜ','ﺭ','ﺮ','𞸓','𞺓','𞺳'];
		$to[]   = 'ر';
		$from[] = ['ڗ','ڙ','ݫ','ݱ','ﺯ','ﺰ','𞸆','𞺆','𞺦'];
		$to[]   = 'ز';
		$from[] = ['ﮊ','ﮋ','ژ'];
		$to[]   = 'ژ';
		$from[] = ['ښ','ݽ','ݾ','ﺱ','ﺲ','ﺳ','ﺴ','𞸎','𞸮','𞹎','𞹮','𞺎','𞺮'];
		$to[]   = 'س';
		$from[] = ['ڛ','ۺ','ݜ','ݭ','ݰ','ﺵ','ﺶ','ﺷ','ﺸ','𞸔','𞸴','𞹔','𞹴','𞺔','𞺴'];
		$to[]   = 'ش';
		$from[] = ['ڝ','ﺹ','ﺺ','ﺻ','ﺼ','𞸑','𞹑','𞸱','𞹱','𞺑','𞺱'];
		$to[]   = 'ص';
		$from[] = ['ڞ','ۻ','ﺽ','ﺾ','ﺿ','ﻀ','𞸙','𞸹','𞹙','𞹹','𞺙','𞺹'];
		$to[]   = 'ض';
		$from[] = ['ﻁ','ﻂ','ﻃ','ﻄ','𞸈','𞹨','𞺈','𞺨'];
		$to[]   = 'ط';
		$from[] = ['ڟ','ﻅ','ﻆ','ﻇ','ﻈ','𞸚','𞹺','𞺚','𞺺'];
		$to[]   = 'ظ';
		$from[] = ['؏','ڠ','ﻉ','ﻊ','ﻋ','ﻌ','𞸏','𞸯','𞹏','𞹯','𞺏','𞺯'];
		$to[]   = 'ع';
		$from[] = ['ۼ','ݝ','ݞ','ݟ','ﻍ','ﻎ','ﻏ','ﻐ','𞸛','𞸻','𞹛','𞹻','𞺛','𞺻'];
		$to[]   = 'غ';
		$from[] = ['؋','ڡ','ڢ','ڣ','ڤ','ڥ','ڦ','ݠ','ݡ','ﭪ','ﭫ','ﭬ','ﭭ','ﭮ','ﭯ','ﭰ','ﭱ','ﻑ','ﻒ','ﻓ','ﻔ','𞸐','𞸞','𞸰','𞹰','𞹾','𞺐','𞺰'];
		$to[]   = 'ف';
		$from[] = ['ٯ','ڧ','ڨ','ﻕ','ﻖ','ﻗ','ﻘ','𞸒','𞸟','𞸲','𞹒','𞹟','𞹲','𞺒','𞺲','؈'];
		$to[]   = 'ق';
		$from[] = ['ػ','ؼ','ك','ڪ','ګ','ڬ','ڭ','ڮ','ݢ','ݣ','ݤ','ݿ','ﮎ','ﮏ','ﮐ','ﮑ','ﯓ','ﯔ','ﯕ','ﯖ','ﻙ','ﻚ','ﻛ','ﻜ','𞸊','𞸪','𞹪'];
		$to[]   = 'ک';
		$from[] = ['ڰ','ڱ','ڲ','ڳ','ڴ','ﮒ','ﮓ','ﮔ','ﮕ','ﮖ','ﮗ','ﮘ','ﮙ','ﮚ','ﮛ','ﮜ','ﮝ'];
		$to[]   = 'گ';
		$from[] = ['ڵ','ڶ','ڷ','ڸ','ݪ','ﻝ','ﻞ','ﻟ','ﻠ','𞸋','𞸫','𞹋','𞺋','𞺫'];
		$to[]   = 'ل';
		$from[] = ['۾','ݥ','ݦ','ﻡ','ﻢ','ﻣ','ﻤ','𞸌','𞸬','𞹬','𞺌','𞺬'];
		$to[]   = 'م';
		$from[] = ['ڹ','ں','ڻ','ڼ','ڽ','ݧ','ݨ','ݩ','ﮞ','ﮟ','ﮠ','ﮡ','ﻥ','ﻦ','ﻧ','ﻨ','𞸍','𞸝','𞸭','𞹍','𞹝','𞹭','𞺍','𞺭'];
		$to[]   = 'ن';
		$from[] = ['ؤ','ٶ','ٷ','ۄ','ۅ','ۆ','ۇ','ۈ','ۉ','ۊ','ۋ','ۏ','ݸ','ݹ','ﯗ','ﯘ','ﯙ','ﯚ','ﯛ','ﯜ','ﯝ','ﯞ','ﯟ','ﯠ','ﯡ','ﯢ','ﯣ','ﺅ','ﺆ','ﻭ','ﻮ','𞸅','𞺅','𞺥'];
		$to[]   = 'و';
		$from[] = ['ة','ھ','ۀ','ە','ۿ','ﮤ','ﮥ','ﮦ','ﮩ','ﮨ','ﮪ','ﮫ','ﮬ','ﮭ','ﺓ','ﺔ','ﻩ','ﻪ','ﻫ','ﻬ','𞸤','𞹤','𞺄'];
		$to[]   = 'ه';
		$from[] = ['ؠ','ئ','ؽ','ؾ','ؿ','ى','ي','ٸ','ۍ','ێ','ې','ۑ','ے','ۓ','ݵ','ݶ','ݷ','ݺ','ݻ','ﮢ','ﮣ','ﮮ','ﮯ','ﮰ','ﮱ','ﯤ','ﯥ','ﯦ','ﯧ','ﯨ','ﯩ','ﯼ','ﯽ','ﯾ','ﯿ','ﺉ','ﺊ','ﺋ','ﺌ','ﻯ','ﻰ','ﻱ','ﻲ','ﻳ','ﻴ','𞸉','𞸩','𞹉','𞹩','𞺉','𞺩'];
		$to[]   = 'ی';
		$from[] = ['ٴ','۽','ﺀ'];
		$to[]   = 'ء';
		$from[] = ['ﻵ','ﻶ','ﻷ','ﻸ','ﻹ','ﻺ','ﻻ','ﻼ'];
		$to[]   = 'لا';
		$from[] = ['ﷲ','﷼','ﷳ','ﷴ','ﷵ','ﷶ','ﷷ','ﷸ','ﷹ','ﷺ','ﷻ'];
		$to[]   = ['الله','ریال','اکبر','محمد','صلعم','رسول','علیه','وسلم','صلی','صلی الله علیه وسلم','جل جلاله'];

		for($i=0; $i < count($from); $i++)
		{
			$_text = str_replace($from[$i],$to[$i],$_text);
		}
		preg_match('/([!\w\s{*}]+)/ui',$_text,$matches);
		if(!is_array($matches) || count($matches) < 1)
		{
			return null;
		}

		return (string) $matches[0];
	}
}
?>