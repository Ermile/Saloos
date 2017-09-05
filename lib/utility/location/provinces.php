<?php
namespace lib\utility\location;
/** province managing **/
class provinces
{
	use tools;
	public static $data =
[
	'east azerbaijan'            => ['name' => 'east azerbaijan', 'localname' => 'آذربایجان شرقی', 'population' => '3724620', 'width' => '37°9035733', 'length' => '46°2682109', 'phone_code' => '41', 'id' => '1',],
	'west azerbaijan'            => ['name' => 'west azerbaijan', 'localname' => 'آذربایجان غربی', 'population' => '3080576', 'width' => '37°4550062', 'length' => '45°00', 'phone_code' => '44', 'id' => '2',],
	'ardabil'                    => ['name' => 'ardabil', 'localname' => 'اردبیل', 'population' => '1248488', 'width' => '38°4853276', 'length' => '47°8911209', 'phone_code' => '45', 'id' => '3',],
	'isfahan'                    => ['name' => 'isfahan', 'localname' => 'اصفهان', 'population' => '4879312', 'width' => '32°6546275', 'length' => '51°6679826', 'phone_code' => '31', 'id' => '4',],
	'alborz'                     => ['name' => 'alborz', 'localname' => 'البرز', 'population' => '2412513', 'width' => '35°9960467', 'length' => '50°9289246', 'phone_code' => '26', 'id' => '5',],
	'ilam'                       => ['name' => 'ilam', 'localname' => 'ایلام', 'population' => '557599', 'width' => '33°2957618', 'length' => '46°670534', 'phone_code' => '84', 'id' => '6',],
	'bushehr'                    => ['name' => 'bushehr', 'localname' => 'بوشهر', 'population' => '1032949', 'width' => '28°9233837', 'length' => '50°820314', 'phone_code' => '77', 'id' => '7',],
	'tehran'                     => ['name' => 'tehran', 'localname' => 'تهران', 'population' => '12183391', 'width' => '35°696111', 'length' => '51°423056', 'phone_code' => '21', 'id' => '8',],
	'chaharmahal and bakhtiari'  => ['name' => 'chaharmahal and bakhtiari', 'localname' => 'چهارمحال و بختیاری', 'population' => '895263', 'width' => '31°9614348', 'length' => '50°8456323', 'phone_code' => '38', 'id' => '9',],
	'south khorasan'             => ['name' => 'south khorasan', 'localname' => 'خراسان جنوبی', 'population' => '662534', 'width' => '32°5175643', 'length' => '59°1041758', 'phone_code' => '56', 'id' => '10',],
	'razavi khorasan'            => ['name' => 'razavi khorasan', 'localname' => 'خراسان رضوی', 'population' => '5994402', 'width' => '35°1020253', 'length' => '59°1041758', 'phone_code' => '51', 'id' => '11',],
	'north khorasan'             => ['name' => 'north khorasan', 'localname' => 'خراسان شمالی', 'population' => '867727', 'width' => '37°4710353', 'length' => '57°1013188', 'phone_code' => '58', 'id' => '12',],
	'khuzestan'                  => ['name' => 'khuzestan', 'localname' => 'خوزستان', 'population' => '4531720', 'width' => '31°4360149', 'length' => '49°041312', 'phone_code' => '61', 'id' => '13',],
	'zanjan'                     => ['name' => 'zanjan', 'localname' => 'زنجان', 'population' => '1015734', 'width' => '36°5018185', 'length' => '48°3988186', 'phone_code' => '24', 'id' => '14',],
	'semnan'                     => ['name' => 'semnan', 'localname' => 'سمنان', 'population' => '631218', 'width' => '35°2255585', 'length' => '54°4342138', 'phone_code' => '23', 'id' => '15',],
	'sistan and baluchestan'     => ['name' => 'sistan and baluchestan', 'localname' => 'سیستان و بلوچستان', 'population' => '2534327', 'width' => '27°5299906', 'length' => '60°5820676', 'phone_code' => '54', 'id' => '16',],
	'fars'                       => ['name' => 'fars', 'localname' => 'فارس', 'population' => '4596658', 'width' => '29°1043813', 'length' => '53°045893', 'phone_code' => '71', 'id' => '17',],
	'qazvin'                     => ['name' => 'qazvin', 'localname' => 'قزوین', 'population' => '1201565', 'width' => '36°0881317', 'length' => '49°8547266', 'phone_code' => '28', 'id' => '18',],
	'qom'                        => ['name' => 'qom', 'localname' => 'قم', 'population' => '1151672', 'width' => '34°6399443', 'length' => '50°8759419', 'phone_code' => '25', 'id' => '19',],
	'kordestan'                  => ['name' => 'kordestan', 'localname' => 'کردستان', 'population' => '1493645', 'width' => '35°9553579', 'length' => '47°1362125', 'phone_code' => '87', 'id' => '20',],
	'kerman'                     => ['name' => 'kerman', 'localname' => 'کرمان', 'population' => '2938988', 'width' => '30°2839379', 'length' => '57°0833628', 'phone_code' => '34', 'id' => '21',],
	'kermanshah'                 => ['name' => 'kermanshah', 'localname' => 'کرمانشاه', 'population' => '1945227', 'width' => '34°314167', 'length' => '47°065', 'phone_code' => '83', 'id' => '22',],
	'kohgiluyeh and boyer-ahmad' => ['name' => 'kohgiluyeh and boyer-ahmad', 'localname' => 'کهگیلویه و بویراحمد', 'population' => '658629', 'width' => '30°6509479', 'length' => '51°60525', 'phone_code' => '74', 'id' => '23',],
	'golestan'                   => ['name' => 'golestan', 'localname' => 'گلستان', 'population' => '1777014', 'width' => '37°2898123', 'length' => '55°1375834', 'phone_code' => '17', 'id' => '24',],
	'gilan'                      => ['name' => 'gilan', 'localname' => 'گیلان', 'population' => '2480874', 'width' => '37°1171617', 'length' => '49°5279996', 'phone_code' => '13', 'id' => '25',],
	'lorestan'                   => ['name' => 'lorestan', 'localname' => 'لرستان', 'population' => '1754243', 'width' => '33°5818394', 'length' => '48°3988186', 'phone_code' => '66', 'id' => '26',],
	'mazandaran'                 => ['name' => 'mazandaran', 'localname' => 'مازندران', 'population' => '3073943', 'width' => '36°2262393', 'length' => '52°5318604', 'phone_code' => '11', 'id' => '27',],
	'markazi'                    => ['name' => 'markazi', 'localname' => 'مرکزی', 'population' => '1413959', 'width' => '33°5093294', 'length' => '-92°396119', 'phone_code' => '86', 'id' => '28',],
	'hormozgan'                  => ['name' => 'hormozgan', 'localname' => 'هرمزگان', 'population' => '1578183', 'width' => '27°138723', 'length' => '55°1375834', 'phone_code' => '76', 'id' => '29',],
	'hamedan'                    => ['name' => 'hamedan', 'localname' => 'همدان', 'population' => '1758268', 'width' => '34°7607999', 'length' => '48°3988186', 'phone_code' => '81', 'id' => '30',],
	'yazd'                       => ['name' => 'yazd', 'localname' => 'یزد', 'population' => '1074428', 'width' => '32°1006387', 'length' => '54°4342138', 'phone_code' => '35', 'id' => '31',],
];

}
?>