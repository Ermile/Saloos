<?php
namespace lib\utility\location;
/** province managing **/
class provinces
{
	use tools;
	public static $data =
   [
	'East Azerbaijan'            => ['name'=> 'East Azerbaijan', 'localname'=> 'آذربایجان شرقی', 'population'=> '3724620', 'width'=> '37°9035733', 'length'=> '46°2682109', 'Phone_Code'=> '41', 'id'=> '1'],
	'West Azerbaijan'            => ['name'=> 'West Azerbaijan', 'localname'=> 'آذربایجان غربی', 'population'=> '3080576', 'width'=> '37°4550062', 'length'=> '45°00', 'Phone_Code'=> '44', 'id'=> '2'],
	'Ardabil'                    => ['name'=> 'Ardabil', 'localname'=> 'اردبیل', 'population'=> '1248488', 'width'=> '38°4853276', 'length'=> '47°8911209', 'Phone_Code'=> '45', 'id'=> '3'],
	'isfahan'                    => ['name'=> 'isfahan', 'localname'=> 'اصفهان', 'population'=> '4879312', 'width'=> '32°6546275', 'length'=> '51°6679826', 'Phone_Code'=> '31', 'id'=> '4'],
	'alborz'                     => ['name'=> 'alborz', 'localname'=> 'البرز', 'population'=> '2412513', 'width'=> '35°9960467', 'length'=> '50°9289246', 'Phone_Code'=> '26', 'id'=> '5'],
	'ilam'                       => ['name'=> 'ilam', 'localname'=> 'ایلام', 'population'=> '557599', 'width'=> '33°2957618', 'length'=> '46°670534', 'Phone_Code'=> '84', 'id'=> '6'],
	'bushehr'                    => ['name'=> 'bushehr', 'localname'=> 'بوشهر', 'population'=> '1032949', 'width'=> '28°9233837', 'length'=> '50°820314', 'Phone_Code'=> '77', 'id'=> '7'],
	'tehran'                     => ['name'=> 'tehran', 'localname'=> 'تهران', 'population'=> '12183391', 'width'=> '35°696111', 'length'=> '51°423056', 'Phone_Code'=> '21', 'id'=> '8'],
	'Chaharmahal and Bakhtiari'  => ['name'=> 'Chaharmahal and Bakhtiari', 'localname'=> 'چهارمحال و بختیاری', 'population'=> '895263', 'width'=> '31°9614348', 'length'=> '50°8456323', 'Phone_Code'=> '38', 'id'=> '9'],
	'South Khorasan'             => ['name'=> 'South Khorasan', 'localname'=> 'خراسان جنوبی', 'population'=> '662534', 'width'=> '32°5175643', 'length'=> '59°1041758', 'Phone_Code'=> '56', 'id'=> '10'],
	'Razavi Khorasan'            => ['name'=> 'Razavi Khorasan', 'localname'=> 'خراسان رضوی', 'population'=> '5994402', 'width'=> '35°1020253', 'length'=> '59°1041758', 'Phone_Code'=> '51', 'id'=> '11'],
	'North Khorasan'             => ['name'=> 'North Khorasan', 'localname'=> 'خراسان شمالی', 'population'=> '867727', 'width'=> '37°4710353', 'length'=> '57°1013188', 'Phone_Code'=> '58', 'id'=> '12'],
	'Khuzestan'                  => ['name'=> 'Khuzestan', 'localname'=> 'خوزستان', 'population'=> '4531720', 'width'=> '31°4360149', 'length'=> '49°041312', 'Phone_Code'=> '61', 'id'=> '13'],
	'zanjan'                     => ['name'=> 'zanjan', 'localname'=> 'زنجان', 'population'=> '1015734', 'width'=> '36°5018185', 'length'=> '48°3988186', 'Phone_Code'=> '24', 'id'=> '14'],
	'semnan'                     => ['name'=> 'semnan', 'localname'=> 'سمنان', 'population'=> '631218', 'width'=> '35°2255585', 'length'=> '54°4342138', 'Phone_Code'=> '23', 'id'=> '15'],
	'Sistan and Baluchestan'     => ['name'=> 'Sistan and Baluchestan', 'localname'=> 'سیستان و بلوچستان', 'population'=> '2534327', 'width'=> '27°5299906', 'length'=> '60°5820676', 'Phone_Code'=> '54', 'id'=> '16'],
	'fars'                       => ['name'=> 'fars', 'localname'=> 'فارس', 'population'=> '4596658', 'width'=> '29°1043813', 'length'=> '53°045893', 'Phone_Code'=> '71', 'id'=> '17'],
	'qazvin'                     => ['name'=> 'qazvin', 'localname'=> 'قزوین', 'population'=> '1201565', 'width'=> '36°0881317', 'length'=> '49°8547266', 'Phone_Code'=> '28', 'id'=> '18'],
	'qom'                        => ['name'=> 'qom', 'localname'=> 'قم', 'population'=> '1151672', 'width'=> '34°6399443', 'length'=> '50°8759419', 'Phone_Code'=> '25', 'id'=> '19'],
	'kordestan'                  => ['name'=> 'kordestan', 'localname'=> 'کردستان', 'population'=> '1493645', 'width'=> '35°9553579', 'length'=> '47°1362125', 'Phone_Code'=> '87', 'id'=> '20'],
	'kerman'                     => ['name'=> 'kerman', 'localname'=> 'کرمان', 'population'=> '2938988', 'width'=> '30°2839379', 'length'=> '57°0833628', 'Phone_Code'=> '34', 'id'=> '21'],
	'kermanshah'                 => ['name'=> 'kermanshah', 'localname'=> 'کرمانشاه', 'population'=> '1945227', 'width'=> '34°314167', 'length'=> '47°065', 'Phone_Code'=> '83', 'id'=> '22'],
	'Kohgiluyeh and Boyer-Ahmad' => ['name'=> 'Kohgiluyeh and Boyer-Ahmad', 'localname'=> 'کهگیلویه و بویراحمد', 'population'=> '658629', 'width'=> '30°6509479', 'length'=> '51°60525', 'Phone_Code'=> '74', 'id'=> '23'],
	'golestan'                   => ['name'=> 'golestan', 'localname'=> 'گلستان', 'population'=> '1777014', 'width'=> '37°2898123', 'length'=> '55°1375834', 'Phone_Code'=> '17', 'id'=> '24'],
	'gilan'                      => ['name'=> 'gilan', 'localname'=> 'گیلان', 'population'=> '2480874', 'width'=> '37°1171617', 'length'=> '49°5279996', 'Phone_Code'=> '13', 'id'=> '25'],
	'lorestan'                   => ['name'=> 'lorestan', 'localname'=> 'لرستان', 'population'=> '1754243', 'width'=> '33°5818394', 'length'=> '48°3988186', 'Phone_Code'=> '66', 'id'=> '26'],
	'mazandaran'                 => ['name'=> 'mazandaran', 'localname'=> 'مازندران', 'population'=> '3073943', 'width'=> '36°2262393', 'length'=> '52°5318604', 'Phone_Code'=> '11', 'id'=> '27'],
	'markazi'                    => ['name'=> 'markazi', 'localname'=> 'مرکزی', 'population'=> '1413959', 'width'=> '33°5093294', 'length'=> '-92°396119', 'Phone_Code'=> '86', 'id'=> '28'],
	'hormozgan'                  => ['name'=> 'hormozgan', 'localname'=> 'هرمزگان', 'population'=> '1578183', 'width'=> '27°138723', 'length'=> '55°1375834', 'Phone_Code'=> '76', 'id'=> '29'],
	'hamedan'                    => ['name'=> 'hamedan', 'localname'=> 'همدان', 'population'=> '1758268', 'width'=> '34°7607999', 'length'=> '48°3988186', 'Phone_Code'=> '81', 'id'=> '30'],
	'yazd'                       => ['name'=> 'yazd', 'localname'=> 'یزد', 'population'=> '1074428', 'width'=> '32°1006387', 'length'=> '54°4342138', 'Phone_Code'=> '35', 'id'=> '31'],
	];
}
?>