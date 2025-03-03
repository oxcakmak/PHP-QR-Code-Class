<?php

class PhpQrCode {
    // ReedSolomon class properties
    private static $singleton;
    private static $GF_EXP;
    private static $GF_LOG;

    // QR class properties
    private $debug = false;
    private $UP    = -1;
    private $DOWN  =  1;
    private $LEFT  = -1;
    private $RIGHT =  1;
    public $string;
    public $mode;
    public $ecl;
    private $mask = false;
    private $version = false;
    private $capacity = 0;
    private $ar_content = array();
    private $ar_content_block = array();
    private $ar_content_ecc = array();
    private $ar_content_interleaved = array();
    private $binary_data = '';
    private $ar_grid = array();
    private $qr_size = 0;
    private $log = array();
    private $ar_data = array(
        'MOD' => false,
        'CCI' => false,
        'DTA' => false,
        'END' => '0000'
    );
    /**
	 * Array of allowed QR modes
	 *
	 * @var string[]
	 */
	private $ar_mode = array('num', 'alpha', 'byte', 'kanji');


	
	/**
	 * Array of allowed error correcting levels
	 *
	 * @var string[]
	 */
	private $ar_ecl = array('L', 'M', 'Q', 'H');


	
	/**
	 * Array of offserts per mode/error correcting level
	 *
	 * @var int[]
	 */
	private $ar_offset = array('num' => 0, 'alpha' => 4, 'byte' => 8, 'kanji' => 12, 'L' => 0, 'M' => 1, 'Q' => 2, 'H' => 3);


	
	/**
	 * Array of mode iand character count indicators
	 *
	 * @var int[][]
	 */
	private $ar_config = array(
		'MOD' => array('num' => 1, 'alpha' => 2, 'byte' => 4, 'kanji' => 8),
		'CCI' => array('num' => 10, 'alpha' => 9, 'byte' => 8, 'kanji' => 8),
	);



	/**
	 * Table of alphanumeric characters
	 *
	 * @var string[]
	 */
	private $ar_alphatable = array(
		'0' => 0,   'A' => 10,   'K' => 20,   'U' => 30,   '+' => 40,
		'1' => 1,   'B' => 11,   'L' => 21,   'V' => 31,   '-' => 41,
		'2' => 2,   'C' => 12,   'M' => 22,   'W' => 32,   '.' => 42,
		'3' => 3,   'D' => 13,   'N' => 23,   'X' => 33,   '/' => 43,
		'4' => 4,   'E' => 14,   'O' => 24,   'Y' => 34,   ':' => 44,
		'5' => 5,   'F' => 15,   'P' => 25,   'Z' => 35,  
		'6' => 6,   'G' => 16,   'Q' => 26,   ' ' => 36,  
		'7' => 7,   'H' => 17,   'R' => 27,   '$' => 37,  
		'8' => 8,   'I' => 18,   'S' => 28,   '%' => 38,  
		'9' => 9,   'J' => 19,   'T' => 29,   '*' => 39,  
	);



	/**
	 * Cross reference table of max character length per
	 * mode, error correcting level and QR version
	 *
	 * @var int[]
	 */
	private $ar_maxlength = array(
		/*  NUMERIC_______________  ALPHANUMERIC__________  BINARY________________  (KANJI)________________    VERSION */
		/*  L     M     Q     H     L     M     Q     H     L     M     Q     H     L     M     Q     H                */
		      41,   34,   27,   17,   25,   20,   16,   10,   17,   14,   11,    7,   10,    8,    7,    4,    /*   1  */
		      77,   63,   48,   34,   47,   38,   29,   20,   32,   26,   20,   14,   20,   16,   12,    8,    /*   2  */
		     127,  101,   77,   58,   77,   61,   47,   35,   53,   42,   32,   24,   32,   26,   20,   15,    /*   3  */
		     187,  149,  111,   82,  114,   90,   67,   50,   78,   62,   46,   34,   48,   38,   28,   21,    /*   4  */
		     255,  202,  144,  106,  154,  122,   87,   64,  106,   84,   60,   44,   65,   52,   37,   27,    /*   5  */
		     322,  255,  178,  139,  195,  154,  108,   84,  134,  106,   74,   58,   82,   65,   45,   36,    /*   6  */
		     370,  293,  207,  154,  224,  178,  125,   93,  154,  122,   86,   64,   95,   75,   53,   39,    /*   7  */
		     461,  365,  259,  202,  279,  221,  157,  122,  192,  152,  108,   84,  118,   93,   66,   52,    /*   8  */
		     552,  432,  312,  235,  335,  262,  189,  143,  230,  180,  130,   98,  141,  111,   80,   60,    /*   9  */
		     652,  513,  364,  288,  395,  311,  221,  174,  271,  213,  151,  119,  167,  131,   93,   74,    /*  10  */
		     772,  604,  427,  331,  468,  366,  259,  200,  321,  251,  177,  137,  198,  155,  109,   85,    /*  11  */
		     883,  691,  489,  374,  535,  419,  296,  227,  367,  287,  203,  155,  226,  177,  125,   96,    /*  12  */
		    1022,  796,  580,  427,  619,  483,  352,  259,  425,  331,  241,  177,  262,  204,  149,  109,    /*  13  */
		    1101,  871,  621,  468,  667,  528,  376,  283,  458,  362,  258,  194,  282,  223,  159,  120,    /*  14  */
		    1250,  991,  703,  530,  758,  600,  426,  321,  520,  412,  292,  220,  320,  254,  180,  136,    /*  15  */
		    1408, 1082,  775,  602,  854,  656,  470,  365,  586,  450,  322,  250,  361,  277,  198,  154,    /*  16  */
		    1548, 1212,  876,  674,  938,  734,  531,  408,  644,  504,  364,  280,  397,  310,  224,  173,    /*  17  */
		    1725, 1346,  948,  746, 1046,  816,  574,  452,  718,  560,  394,  310,  442,  345,  243,  191,    /*  18  */
		    1903, 1500, 1063,  813, 1153,  909,  644,  493,  792,  624,  442,  338,  488,  384,  272,  208,    /*  19  */
		    2061, 1600, 1159,  919, 1249,  970,  702,  557,  858,  666,  482,  382,  528,  410,  297,  235,    /*  20  */
		    2232, 1708, 1224,  969, 1352, 1035,  742,  587,  929,  711,  509,  403,  572,  438,  314,  248,    /*  21  */
		    2409, 1872, 1358, 1056, 1460, 1134,  823,  640, 1003,  779,  565,  439,  618,  480,  348,  270,    /*  22  */
		    2620, 2059, 1468, 1108, 1588, 1248,  890,  672, 1091,  857,  611,  461,  672,  528,  376,  284,    /*  23  */
		    2812, 2188, 1588, 1228, 1704, 1326,  963,  744, 1171,  911,  661,  511,  721,  561,  407,  315,    /*  24  */
		    3057, 2395, 1718, 1286, 1853, 1451, 1041,  779, 1273,  997,  715,  535,  784,  614,  440,  330,    /*  25  */
		    3283, 2544, 1804, 1425, 1990, 1542, 1094,  864, 1367, 1059,  751,  593,  842,  652,  462,  365,    /*  26  */
		    3517, 2701, 1933, 1501, 2132, 1637, 1172,  910, 1465, 1125,  805,  625,  902,  692,  496,  385,    /*  27  */
		    3669, 2857, 2085, 1581, 2223, 1732, 1263,  958, 1528, 1190,  868,  658,  940,  732,  534,  405,    /*  28  */
		    3909, 3035, 2181, 1677, 2369, 1839, 1322, 1016, 1628, 1264,  908,  698, 1002,  778,  559,  430,    /*  29  */
		    4158, 3289, 2358, 1782, 2520, 1994, 1429, 1080, 1732, 1370,  982,  742, 1066,  843,  604,  457,    /*  30  */
		    4417, 3486, 2473, 1897, 2677, 2113, 1499, 1150, 1840, 1452, 1030,  790, 1132,  894,  634,  486,    /*  31  */
		    4686, 3693, 2670, 2022, 2840, 2238, 1618, 1226, 1952, 1538, 1112,  842, 1201,  947,  684,  518,    /*  32  */
		    4965, 3909, 2805, 2157, 3009, 2369, 1700, 1307, 2068, 1628, 1168,  898, 1273, 1002,  719,  553,    /*  33  */
		    5253, 4134, 2949, 2301, 3183, 2506, 1787, 1394, 2188, 1722, 1228,  958, 1347, 1060,  756,  590,    /*  34  */
		    5529, 4343, 3081, 2361, 3351, 2632, 1867, 1431, 2303, 1809, 1283,  983, 1417, 1113,  790,  605,    /*  35  */
		    5836, 4588, 3244, 2524, 3537, 2780, 1966, 1530, 2431, 1911, 1351, 1051, 1496, 1176,  832,  647,    /*  36  */
		    6153, 4775, 3417, 2625, 3729, 2894, 2071, 1591, 2563, 1989, 1423, 1093, 1577, 1224,  876,  673,    /*  37  */
		    6479, 5039, 3599, 2735, 3927, 3054, 2181, 1658, 2699, 2099, 1499, 1139, 1661, 1292,  923,  701,    /*  38  */
		    6743, 5313, 3791, 2927, 4087, 3220, 2298, 1774, 2809, 2213, 1579, 1219, 1729, 1362,  972,  750,    /*  39  */
		    7089, 5596, 3993, 3057, 4296, 3391, 2420, 1852, 2953, 2331, 1663, 1273, 1817, 1435, 1024,  784     /*  40  */
		/*  L     M     Q     H     L     M     Q     H     L     M     Q     H     L     M     Q     H                */
		/*  NUMERIC_______________  ALPHANUMERIC__________  BINARY________________  (KANJI)________________    VERSION */
	);



	/**
	 * Array containing error correcting codeword data
	 *
	 * Syntax: $ar_eccc_data['AB'] = array( C, D, E, F, G )
	 *   A: Version number
	 *   B: Error correction level
	 *   C: Number of error correcting codewords per data blocks
	 *   D: Number of Group-1 blocks
	 *   E: Number of data codewords per Group-1 block
	 *   F: Number of Group-2 blocks
	 *   G: Number of data codewords per Group-2 block
	 *
	 * @var int[][]
	 */
	private $ar_ecc_data = array(
		 '1L' => array( 7,  1,  19,  0,   0),  '1M' => array(10,  1,  16,  0,   0),  '1Q' => array(13,  1,  13,  0,   0),  '1H' => array(17,  1,   9,  0,   0),
		 '2L' => array(10,  1,  34,  0,   0),  '2M' => array(16,  1,  28,  0,   0),  '2Q' => array(22,  1,  22,  0,   0),  '2H' => array(28,  1,  16,  0,   0),
		 '3L' => array(15,  1,  55,  0,   0),  '3M' => array(26,  1,  44,  0,   0),  '3Q' => array(18,  2,  17,  0,   0),  '3H' => array(22,  2,  13,  0,   0),
		 '4L' => array(20,  1,  80,  0,   0),  '4M' => array(18,  2,  32,  0,   0),  '4Q' => array(26,  2,  24,  0,   0),  '4H' => array(16,  4,   9,  0,   0),
		 '5L' => array(26,  1, 108,  0,   0),  '5M' => array(24,  2,  43,  0,   0),  '5Q' => array(18,  2,  15,  2,  16),  '5H' => array(22,  2,  11,  2,  12),
		 '6L' => array(18,  2,  68,  0,   0),  '6M' => array(16,  4,  27,  0,   0),  '6Q' => array(24,  4,  19,  0,   0),  '6H' => array(28,  4,  15,  0,   0),
		 '7L' => array(20,  2,  78,  0,   0),  '7M' => array(18,  4,  31,  0,   0),  '7Q' => array(18,  2,  14,  4,  15),  '7H' => array(26,  4,  13,  1,  14),
		 '8L' => array(24,  2,  97,  0,   0),  '8M' => array(22,  2,  38,  2,  39),  '8Q' => array(22,  4,  18,  2,  19),  '8H' => array(26,  4,  14,  2,  15),
		 '9L' => array(30,  2, 116,  0,   0),  '9M' => array(22,  3,  36,  2,  37),  '9Q' => array(20,  4,  16,  4,  17),  '9H' => array(24,  4,  12,  4,  13),
		'10L' => array(18,  2,  68,  2,  69), '10M' => array(26,  4,  43,  1,  44), '10Q' => array(24,  6,  19,  2,  20), '10H' => array(28,  6,  15,  2,  16),
		'11L' => array(20,  4,  81,  0,   0), '11M' => array(30,  1,  50,  4,  51), '11Q' => array(28,  4,  22,  4,  23), '11H' => array(24,  3,  12,  8,  13),
		'12L' => array(24,  2,  92,  2,  93), '12M' => array(22,  6,  36,  2,  37), '12Q' => array(26,  4,  20,  6,  21), '12H' => array(28,  7,  14,  4,  15),
		'13L' => array(26,  4, 107,  0,   0), '13M' => array(22,  8,  37,  1,  38), '13Q' => array(24,  8,  20,  4,  21), '13H' => array(22, 12,  11,  4,  12),
		'14L' => array(30,  3, 115,  1, 116), '14M' => array(24,  4,  40,  5,  41), '14Q' => array(20, 11,  16,  5,  17), '14H' => array(24, 11,  12,  5,  13),
		'15L' => array(22,  5,  87,  1,  88), '15M' => array(24,  5,  41,  5,  42), '15Q' => array(30,  5,  24,  7,  25), '15H' => array(24, 11,  12,  7,  13),
		'16L' => array(24,  5,  98,  1,  99), '16M' => array(28,  7,  45,  3,  46), '16Q' => array(24, 15,  19,  2,  20), '16H' => array(30,  3,  15, 13,  16),
		'17L' => array(28,  1, 107,  5, 108), '17M' => array(28, 10,  46,  1,  47), '17Q' => array(28,  1,  22, 15,  23), '17H' => array(28,  2,  14, 17,  15),
		'18L' => array(30,  5, 120,  1, 121), '18M' => array(26,  9,  43,  4,  44), '18Q' => array(28, 17,  22,  1,  23), '18H' => array(28,  2,  14, 19,  15),
		'19L' => array(28,  3, 113,  4, 114), '19M' => array(26,  3,  44, 11,  45), '19Q' => array(26, 17,  21,  4,  22), '19H' => array(26,  9,  13, 16,  14),
		'20L' => array(28,  3, 107,  5, 108), '20M' => array(26,  3,  41, 13,  42), '20Q' => array(30, 15,  24,  5,  25), '20H' => array(28, 15,  15, 10,  16),
		'21L' => array(28,  4, 116,  4, 117), '21M' => array(26, 17,  42,  0,   0), '21Q' => array(28, 17,  22,  6,  23), '21H' => array(30, 19,  16,  6,  17),
		'22L' => array(28,  2, 111,  7, 112), '22M' => array(28, 17,  46,  0,   0), '22Q' => array(30,  7,  24, 16,  25), '22H' => array(24, 34,  13,  0,   0),
		'23L' => array(30,  4, 121,  5, 122), '23M' => array(28,  4,  47, 14,  48), '23Q' => array(30, 11,  24, 14,  25), '23H' => array(30, 16,  15, 14,  16),
		'24L' => array(30,  6, 117,  4, 118), '24M' => array(28,  6,  45, 14,  46), '24Q' => array(30, 11,  24, 16,  25), '24H' => array(30, 30,  16,  2,  17),
		'25L' => array(26,  8, 106,  4, 107), '25M' => array(28,  8,  47, 13,  48), '25Q' => array(30,  7,  24, 22,  25), '25H' => array(30, 22,  15, 13,  16),
		'26L' => array(28, 10, 114,  2, 115), '26M' => array(28, 19,  46,  4,  47), '26Q' => array(28, 28,  22,  6,  23), '26H' => array(30, 33,  16,  4,  17),
		'27L' => array(30,  8, 122,  4, 123), '27M' => array(28, 22,  45,  3,  46), '27Q' => array(30,  8,  23, 26,  24), '27H' => array(30, 12,  15, 28,  16),
		'28L' => array(30,  3, 117, 10, 118), '28M' => array(28,  3,  45, 23,  46), '28Q' => array(30,  4,  24, 31,  25), '28H' => array(30, 11,  15, 31,  16),
		'29L' => array(30,  7, 116,  7, 117), '29M' => array(28, 21,  45,  7,  46), '29Q' => array(30,  1,  23, 37,  24), '29H' => array(30, 19,  15, 26,  16),
		'30L' => array(30,  5, 115, 10, 116), '30M' => array(28, 19,  47, 10,  48), '30Q' => array(30, 15,  24, 25,  25), '30H' => array(30, 23,  15, 25,  16),
		'31L' => array(30, 13, 115,  3, 116), '31M' => array(28,  2,  46, 29,  47), '31Q' => array(30, 42,  24,  1,  25), '31H' => array(30, 23,  15, 28,  16),
		'32L' => array(30, 17, 115,  0,   0), '32M' => array(28, 10,  46, 23,  47), '32Q' => array(30, 10,  24, 35,  25), '32H' => array(30, 19,  15, 35,  16),
		'33L' => array(30, 17, 115,  1, 116), '33M' => array(28, 14,  46, 21,  47), '33Q' => array(30, 29,  24, 19,  25), '33H' => array(30, 11,  15, 46,  16),
		'34L' => array(30, 13, 115,  6, 116), '34M' => array(28, 14,  46, 23,  47), '34Q' => array(30, 44,  24,  7,  25), '34H' => array(30, 59,  16,  1,  17),
		'35L' => array(30, 12, 121,  7, 122), '35M' => array(28, 12,  47, 26,  48), '35Q' => array(30, 39,  24, 14,  25), '35H' => array(30, 22,  15, 41,  16),
		'36L' => array(30,  6, 121, 14, 122), '36M' => array(28,  6,  47, 34,  48), '36Q' => array(30, 46,  24, 10,  25), '36H' => array(30,  2,  15, 64,  16),
		'37L' => array(30, 17, 122,  4, 123), '37M' => array(28, 29,  46, 14,  47), '37Q' => array(30, 49,  24, 10,  25), '37H' => array(30, 24,  15, 46,  16),
		'38L' => array(30,  4, 122, 18, 123), '38M' => array(28, 13,  46, 32,  47), '38Q' => array(30, 48,  24, 14,  25), '38H' => array(30, 42,  15, 32,  16),
		'39L' => array(30, 20, 117,  4, 118), '39M' => array(28, 40,  47,  7,  48), '39Q' => array(30, 43,  24, 22,  25), '39H' => array(30, 10,  15, 67,  16),
		'40L' => array(30, 19, 118,  6, 119), '40M' => array(28, 18,  47, 31,  48), '40Q' => array(30, 34,  24, 34,  25), '40H' => array(30, 20,  15, 61,  16),
	);



	/**
	 * Number of remainder bits per version number
	 *
	 * @var int[]
	 */
	private $ar_remainder_bits = array(false,
		0,7,7,7,7,7,0,0,0,0,
		0,0,0,3,3,3,3,3,3,3,
		4,4,4,4,4,4,4,3,3,3,
		3,3,3,3,0,0,0,0,0,0,
	);



	/**
	 * Finder and alignment patterns in array format
	 *
	 * @var int[][][]
	 */
	private $ar_pattern = array(
		'finder' => array(
			array(1,1,1,1,1,1,1,),
			array(1,0,0,0,0,0,1,),
			array(1,0,1,1,1,0,1,),
			array(1,0,1,1,1,0,1,),
			array(1,0,1,1,1,0,1,),
			array(1,0,0,0,0,0,1,),
			array(1,1,1,1,1,1,1,),
		),

		'align' => array(
			array(1,1,1,1,1),
			array(1,0,0,0,1),
			array(1,0,1,0,1),
			array(1,0,0,0,1),
			array(1,1,1,1,1),
		),
	);



	/**
	 * Array of alignment pattern positions per version number
	 *
	 * @var int[][]
	 */
	private $ar_alignment_positions = array(false,
		array(), array(6,18), array(6,22), array(6,26), array(6,30),
		array(6,34), array(6,22,38), array(6,24,42), array(6,26,46), array(6,28,50),
		array(6,30,54), array(6,32,58), array(6,34,62), array(6,26,46,66), array(6,26,48,70),
		array(6,26,50,74), array(6,30,54,78), array(6,30,56,82), array(6,30,58,86), array(6,34,62,90),
		array(6,28,50,72,94), array(6,26,50,74,98), array(6,30,54,78,102), array(6,28,54,80,106), array(6,32,58,84,110),
		array(6,30,58,86,114), array(6,34,62,90,118), array(6,26,50,74,98,122), array(6,30,54,78,102,126), array(6,26,52,78,104,130),
		array(6,30,56,82,108,134), array(6,34,60,86,112,138), array(6,30,58,86,114,142), array(6,34,62,90,118,146), array(6,30,54,78,102,126,150),
		array(6,24,50,76,102,128,154), array(6,28,54,80,106,132,158), array(6,32,58,84,110,136,162), array(6,26,54,82,110,138,166), array(6,30,58,86,114,142,170)
	);



	/**
	 * Array of format strings based on error correction level and mask
	 *
	 * @var string[]
	 */
	private $ar_format_string = array(
		'L0' => '111011111000100', 'L1' => '111001011110011', 'L2' => '111110110101010', 'L3' => '111100010011101',
		'L4' => '110011000101111', 'L5' => '110001100011000', 'L6' => '110110001000001', 'L7' => '110100101110110',
		'M0' => '101010000010010', 'M1' => '101000100100101', 'M2' => '101111001111100', 'M3' => '101101101001011',
		'M4' => '100010111111001', 'M5' => '100000011001110', 'M6' => '100111110010111', 'M7' => '100101010100000',
		'Q0' => '011010101011111', 'Q1' => '011000001101000', 'Q2' => '011111100110001', 'Q3' => '011101000000110',
		'Q4' => '010010010110100', 'Q5' => '010000110000011', 'Q6' => '010111011011010', 'Q7' => '010101111101101',
		'H0' => '001011010001001', 'H1' => '001001110111110', 'H2' => '001110011100111', 'H3' => '001100111010000',
		'H4' => '000011101100010', 'H5' => '000001001010101', 'H6' => '000110100001100', 'H7' => '000100000111011',
	);



	/**
	 * Array of version strings based on version number
	 *
	 * @var string[]
	 */
	private $ar_version_string = array(false,
		false,                false,                false,                false,                false,                 //  1 -  5 
		false,                '000111110010010100', '001000010110111100', '001001101010011001', '001010010011010011',  //  6 - 10
		'001011101111110110', '001100011101100010', '001101100001000111', '001110011000001101', '001111100100101000',  // 11 - 15
		'010000101101111000', '010001010001011101', '010010101000010111', '010011010100110010', '010100100110100110',  // 16 - 20
		'010101011010000011', '010110100011001001', '010111011111101100', '011000111011000100', '011001000111100001',  // 21 - 25
		'011010111110101011', '011011000010001110', '011100110000011010', '011101001100111111', '011110110101110101',  // 26 - 30
		'011111001001010000', '100000100111010101', '100001011011110000', '100010100010111010', '100011011110011111',  // 31 - 35
		'100100101100001011', '100101010000101110', '100110101001100100', '100111010101000001', '101000110001101001',  // 36 - 40
	);



	/**
	 * Array of format strings based on error correction level and mask
	 *
	 * @var string[]
	 */
	private $ar_default = array('string' => '', 'mode' => 'byte', 'ecl' => 'M');
    
    // Copy all other properties from QR class...
    // [Keep all the existing array definitions from QR class]

    public function initTable() {
        self::$GF_EXP = array_fill(0,512,1);
        self::$GF_LOG = array_fill(0,256,0);
        $x = 1;
        for($i = 1; $i < 255; $i++){
            $x <<= 1;
            if ($x & 0x100)
                $x = $x ^ 0x11d;
            self::$GF_EXP[$i] = $x;
            self::$GF_LOG[$x] = $i;
        }
        for($i = 255; $i < 512; $i++){
            self::$GF_EXP[$i] = self::$GF_EXP[$i-255];
        }
    }
    
    /**
     * To get the singleton
     * @return ReedSolomon
     */
    public static function GetInstance(){
        if (self::$singleton == null){
            self::$singleton = new ReedSolomon();
            self::$singleton->initTable();
        }
        return self::$singleton;
    }
    
    /**
     * @param int $x
     * @param int $y
     */
    public function gf_mul($x, $y) {
        if ($x == 0 || $y == 0) return 0;
        return self::$GF_EXP[self::$GF_LOG[$x] + self::$GF_LOG[$y]];
    }
    
    /**
     * Unused method on encoding
     * @param int $x
     * @param int $y
     */
    public function gf_div($x, $y) {
        if ($y == 0) throw new Exception('Zero division');
        if ($x == 0) return 0;
        return self::$GF_EXP[self::$GF_LOG[$x] + 255 - self::$GF_LOG[$y]];
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param array $q
     */
    public function gf_poly_mul($p, $q){
        $r = array_fill(0, count($p) + count($q) - 1, 0);
        
        for ($j = 0; $j < count($q); $j++)
            for ($i = 0; $i < count($p); $i++)
                $r[$i + $j] ^= self::gf_mul($p[$i], $q[$j]);
        return $r;
    }

    /**
     * Unused method on encoding
     * @param array $p
     * @param int $x
     */
    public function gf_poly_scale($p, $x) {
        $r = array(); for($i=0;$i<count($p);$i++) $r[$i]=0;
        
        for ($i = 0; $i < count($p); $i++){
            $r[$i] = self::gf_mul($p[$i], $x);
        }
        return $r;
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param array $q
     */
    public function gf_poly_add($p, $q){
        $r = array(); for($i=0;$i<max(count($p), count($q)); $i++) $r[$i]=0;
        
        for($i=0; $i < count($p); $i++)
            $r[$i + count($r) - count($p)] = $p[$i];
        for($i=0; $i < count($q); $i++)
            $r[$i + count($r) - count($q)] ^= $q[$i];
        return $r;
    }
    
    /**
     * Unused method on encoding
     * @param array $p
     * @param int $x
     */
    public function gf_poly_eval($p, $x){
        $y = $p[0];
        for ($i = 1; $i < count($p); $i++)
            $y = self::gf_mul($y, $x) ^ $p[$i];
        return $y;
    }
    
    /**
     * @param int $nsymb
     */
    public function rs_generator_poly($nsymb){
        $g = array(1);
        for($i = 0; $i < $nsymb; $i++){
            $g = self::gf_poly_mul($g, array(1, self::$GF_EXP[$i]));
        }
        return $g;
    }
    
    /**
     * @param array $msg_in
     * @param int $nsymb
     */
    public function rs_encode_msg($msg_in, $nsymb){
        $gen = self::rs_generator_poly($nsymb);

        $msg_out = array_fill(0, count($msg_in) + $nsymb, 0);
        
        for ($i = 0; $i < count($msg_in); $i++)
            $msg_out[$i] = $msg_in[$i];
            
        for ($i = 0; $i < count($msg_in); $i++){
            $coef = $msg_out[$i];
            if ($coef != 0){
                for($j=0;$j < count($gen); $j++){
                    $msg_out[$i + $j] ^= self::gf_mul($gen[$j], $coef);
                }
            }
        }
        for ($i = 0; $i < count($msg_in); $i++)
             $msg_out[$i] = $msg_in[$i];
        
        return $msg_out;
    }

    // [Keep all other ReedSolomon methods]

    /**
	 * QR code generation constructor
	 *
	 * @param string $string
	 * @param string $ecl
	 * @param string $mode
	 */
	function __construct($string, $ecl = 'M', $mode = 'byte') {

		$this->log[] = '==[ START ]=============================';

		// Set string, mode and error correction level
		$this->string = $string;
		$this->mode   = in_array($mode, $this->ar_mode) ? $mode : $this->ar_default['mode'];
		$this->ecl    = in_array($ecl,  $this->ar_ecl)  ? $ecl  : $this->ar_default['ecl'];

		$this->log[] = "String:   {$this->string}";
		$this->log[] = "Mode:     {$this->mode}";
		$this->log[] = "EC level: {$this->ecl}";
		$this->log[] = '';

		// Sanitize the input string (in case of numeric / alphanumeric data)
		$this->clean_string();

		// Find the optimal version and its maximum capacity
		$this->get_version_and_capacity();

		// Get the correct mode indicator for the current QR code
		$this->get_mode_indicator();

		// Get the character count indicator for the input string
		$this->get_cci();

		// Encode the data to binary values
		$this->encode_data_binary();

		// Add all data together and split in bytes
		$this->finalize_data();

		// Add error correcting codewords to the data
		$this->add_ecc_words();

		// Interleace the codes if necessary
		$this->interleave();

		// Convert all data to a binary string
		$this->convert_to_binary();

		// Create the empty QR code grid
		$this->create_empty_grid();

		// Reserve the QR code grid with patterns
		$this->add_patterns(false);

		// Add data
		$this->add_binary_data();

		// Choose and apply the best mask
		$this->choose_mask();
		$this->apply_mask();

		// Fill the QR code grid with patterns
		$this->add_patterns(true);

	}



	/**
	 * Enable or disable debug mode
	 *
	 * @param bool $debug
	 * @return void
	 */
	public function set_debug_mode($debug) {
		$this->debug = !!$debug;
	}



	/**
	 * Clean the input if it's in NUM or ALPHA mode.
	 *
	 * @return void
	 */
	function clean_string() {
		
		switch($this->mode) {
			case 'num':
				$this->string *= 1;
				break;

			case 'alpha':
				$this->string = preg_replace('#[^A-Z 0-9\$%\*\+\-\./\:]#', '', strtoupper($this->string));
				break;

			default:
				break;
		}

		$this->log[] = 'String cleaned, result: ' . $this->string;

	}



	/**
	 * Get the version number and maximum capacity of the QR code
	 * based on the input string
	 *
	 * @return void
	 */
	function get_version_and_capacity() {

		$length = strlen($this->string);
		$offset = $this->ar_offset[$this->mode] + $this->ar_offset[$this->ecl];
		$current_version = 0;

		for($x = 0; $x < count($this->ar_maxlength); $x += 16) {
			$current_version++;
			$current_offset = $offset + $x;
			if($this->ar_maxlength[$current_offset] >= $length) {
				$this->version  = $current_version;
				$this->capacity =
					($this->ar_ecc_data[$this->version . $this->ecl][1] * $this->ar_ecc_data[$this->version . $this->ecl][2]) +
					($this->ar_ecc_data[$this->version . $this->ecl][3] * $this->ar_ecc_data[$this->version . $this->ecl][4]);

					$this->log[] = 'Capacity version: ' . $this->capacity .'.'. $this->version;

				return;
			}
		}

		$this->capacity = 0;
		$this->version  = false;
		$this->log[] = 'No capacity/version set';
	}



	/**
	 * Get the mode indicator based on the QR mode
	 *
	 * @return void
	 */
	function get_mode_indicator() {
		$this->ar_data['MOD'] = $this->_binarize($this->ar_config['MOD'][$this->mode], 4);
		$this->log[] = 'Mode indicator set: ' . $this->ar_data['MOD'];
	}



	/**
	 * Get the character count indicator based on QR mode and string length
	 *
	 * @return void
	 */
	function get_cci() {
		$this->ar_data['CCI'] = $this->_binarize(strlen($this->string), $this->ar_config['CCI'][$this->mode]);
		$this->log[] = 'CCI set: ' . $this->ar_data['CCI'];
	}



	/**
	 * Encode the input data
	 *
	 * @return void
	 */
	function encode_data_binary() {
		$binary_data = '';

		switch($this->mode) {
			case 'num':
				$ar_data = str_split('' . $this->string, 3);
				foreach($ar_data as $data_codeword) {
					$data_codeword *= 1;
					$bitlength = 3 * strlen($data_codeword) + 1;
					$binary_data .= $this->_binarize($data_codeword, $bitlength);
				}
				break;

			case 'alpha':
				$ar_data = str_split('' . $this->string, 2);
				foreach($ar_data as $data_codeword) {
					if(strlen($data_codeword) == 1)
					{
						$binary_data .= $this->_binarize($this->ar_alphatable[$data_codeword], 6);
					}

					else
					{
						$ar_data_codeword = str_split($data_codeword, 1);
						$binary_data .= $this->_binarize(45 * $this->ar_alphatable[$ar_data_codeword[0]] + $this->ar_alphatable[$ar_data_codeword[1]], 10);
					}
				}
				break;

			case 'byte':
				for($x=0; $x<strlen($this->string); $x++) {
					$data_codeword = substr($this->string, $x, 1);
					$binary_data .= $this->_binarize(ord($data_codeword), 8);
				}
				break;

			case 'kanji':
				exit('Kanji is not implemented at this point.');

			default:
				break;
		}

		$this->ar_data['DTA'] = $binary_data;
		$this->log[] = 'Binary data set: ' . $this->ar_data['DTA'];

	}



	/**
	 * Combine indicators, data and end byte, fill up with filler bytes
	 *
	 * @return void
	 */
	function finalize_data() {
		$this->log[] = '';
		$this->log[] = 'Set combined binary data:';

		// Combine all binary data
		$combined = $this->ar_data['MOD'] . $this->ar_data['CCI'] . $this->ar_data['DTA'] . $this->ar_data['END'];
		$this->log[] = ' - ' . $combined;

		// Fill string up with zeroes
		$combined .= str_repeat('0', ceil(strlen($combined) * 8) / 8 - strlen($combined));
		$this->log[] = ' - ' . $combined;

		// Split string in bytes
		$ar_content_binary = str_split($combined, 8);
		$this->log[] = ' - ' . implode(' ', $ar_content_binary);

		// Add filler bytes (236, 17) until it equals the QR length
		$filler = 236;
		while(count($ar_content_binary) < $this->capacity) {
			$ar_content_binary[] = $this->_binarize($filler);
			$filler = 253 - $filler;
		}
		$this->log[] = ' - ' . implode(' ', $ar_content_binary);

		foreach($ar_content_binary as $binary_value) {
			$this->ar_content[] = bindec($binary_value);
		}
		$this->log[] = ' - ' . implode(' ', $this->ar_content);
		$this->log[] = 'Binary data length: ' . count($this->ar_content);
	}



	/**
	 * Generate and add the error correcting codewords
	 *
	 * @return void
	 */
	function add_ecc_words() {
		// Initialize Reed Solomon class
		$this->log[] = '';
		$this->log[] = 'Adding error-correcting codewords...';

		require_once('reedSolomon.php');
		$ob_rs = ReedSolomon::GetInstance();

		// Get appropriate Reed Solomon settings
		$ar_rs_settings = $this->ar_ecc_data[$this->version . $this->ecl];
		$this->log[] = 'Reed-Solomon settings: ' . implode(' ', $ar_rs_settings);

	    // Encode Group-1 blocks
		for($x = 0; $x < $ar_rs_settings[1]; $x++)
		{
			$ar_block = array_slice($this->ar_content, $x * $ar_rs_settings[2], $ar_rs_settings[2]);
			$this->ar_content_block[] = $ar_block;
			$ar_ecc   = $ob_rs->rs_encode_msg($ar_block, $ar_rs_settings[0]);
			$this->ar_content_ecc[] = array_slice($ar_ecc, -1 * $ar_rs_settings[0]);
		}

		// Encode Group-2 blocks
	    $offset = $ar_rs_settings[1] * $ar_rs_settings[2];
	    for($x = 0; $x < $ar_rs_settings[3]; $x++) {
			$ar_block = array_slice($this->ar_content, $offset + $x * $ar_rs_settings[4], $ar_rs_settings[4]);
			$this->ar_content_block[] = $ar_block;
			$ar_ecc   = $ob_rs->rs_encode_msg($ar_block, $ar_rs_settings[0]);
			$this->ar_content_ecc[] = array_slice($ar_ecc, $ar_rs_settings[4]);
	    }

		$this->log[] = '';
		$this->log[] = 'Content blocks: ';
		foreach($this->ar_content_block as $log_ar_content_block) {
			$this->log[] = ' - ' . implode(' ', $log_ar_content_block);
		}

		$this->log[] = '';
		$this->log[] = 'Error-correcting codewords: ';
		foreach($this->ar_content_ecc as $log_ar_content_ecc) {
			$this->log[] = ' - ' . implode(' ', $log_ar_content_ecc);
		}

	}



	/**
	 * Interleave content blocks and error correcting codeword blocks
	 *
	 * @return void
	 */
	function interleave() {
		$this->log[] = '';
		$this->log[] = 'Interleaving...';

		// Do not interleave if there's only one block
		if(count($this->ar_content_block) == 1) {
			// Simply combine content and error correcting codewords
			$this->ar_content_interleaved = array_merge(reset($this->ar_content_block), reset($this->ar_content_ecc));

			$this->log[] = 'Single block interleave:';
			$this->log[] = ' - ' . implode(' ', $this->ar_content_interleaved);
			$this->log[] = 'Interleaved content length: ' . count($this->ar_content_interleaved);

			return;
		}

		// Get the largest content block size
		$maxsize = 0;
		foreach($this->ar_content_block as $ar_test) $maxsize = max($maxsize, count($ar_test));

		// Interleave the content blocks
		$this->ar_content_interleaved = array();
		for($x=0; $x < $maxsize; $x++) {
			foreach($this->ar_content_block as $ar_current_block)
			{
				if(isset($ar_current_block[$x]))
				{
					$this->ar_content_interleaved[] = $ar_current_block[$x];
				}
			}
		}

		// Repeat this trick for error correcting codewords
		$maxsize = 0;
		foreach($this->ar_content_ecc as $ar_test) $maxsize = max($maxsize, count($ar_test));
		for($x=0; $x < $maxsize; $x++) {
			foreach($this->ar_content_ecc as $ar_current_ecc)
			{
				if(isset($ar_current_ecc[$x]))
				{
					$this->ar_content_interleaved[] = $ar_current_ecc[$x];
				}
			}
		}

		$this->log[] = 'Multiple block interleave:';
		$this->log[] = ' - ' . implode(' ', $this->ar_content_interleaved);
		$this->log[] = 'Interleaved content length: ' . count($this->ar_content_interleaved);

	}



	/**
	 * Convert interleaved data to binary data
	 *
	 * @return void
	 */
	function convert_to_binary() {
		$this->log[] = '';
		$this->log[] = 'Converting to binary data...';

		// Reset binary data
		$this->binary_data = '';

		foreach($this->ar_content_interleaved as $codeword) {
			$this->binary_data .= $this->_binarize($codeword);
		}

		// Add trailing zeroes
		if(!empty($this->ar_remainder_bits[$this->version])) {
			$this->binary_data .= str_repeat('0', $this->ar_remainder_bits[$this->version]);
		}

		$this->log[] = 'Result: ' . $this->binary_data;
		$this->log[] = '(' . strlen($this->binary_data) . ' bits)';
	}



	/**
	 * Create an empty grid based on QR size
	 *
	 * @return void
	 */
	function create_empty_grid() {
		$this->log[] = '';
		$this->qr_size = $this->version * 4 + 17;
		$this->ar_grid = array_fill(0, $this->qr_size, array_fill(0, $this->qr_size, false));
		$this->log[] = 'Created an empty grid.';
	}



	/**
	 * Add the default QR code patterns
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_patterns($defined = true) {
		$this->add_timing_patterns($defined);
		$this->add_finder_patterns($defined);
		$this->add_separators($defined);
		$this->add_alignment_patterns($defined);
		$this->add_dark_module($defined);
		$this->add_format_information($defined);
		$this->add_version_information($defined);
		$this->log[] = $defined ? 'Patterns added to grid.' : 'Patterns prepared in grid.';
	}



	/**
	 * Add timing pattern to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_timing_patterns($defined = true) {
		// Horizontal timing pattern
		$ar_timing_pattern = array(array_fill(0, $this->qr_size, $defined ? 1 : 3));
		foreach($ar_timing_pattern[0] as $position => $void) {
			if($position % 2 == 1) {
				$ar_timing_pattern[0][$position] = $defined ? 0 : 3;
			}
		}
		$this->_inject($ar_timing_pattern, 0, 6);

		// Vertical timing pattern
		$ar_timing_pattern = array();
		for($x = 0; $x < $this->qr_size; $x++) {
			$ar_timing_pattern[] = array($defined ? ($x % 2 == 0 ? 1 : 0) : 3);
		}
		$this->_inject($ar_timing_pattern, 6, 0);
	}



	/**
	 * Add finder patterns to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_finder_patterns($defined = true) {
		$ar_pattern = $this->ar_pattern['finder'];
		if(!$defined) {
			foreach($ar_pattern as $k=>$v) {
				foreach($v as $vk => $vv) {
					$ar_pattern[$k][$vk] = 2;
				}
			}
		}

		$this->_inject($ar_pattern, 0, 0);
		$this->_inject($ar_pattern, -7, 0);
		$this->_inject($ar_pattern, 0, -7);
	}



	/**
	 * Add separators to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_separators($defined = true) {
		$separator_bit = $defined ? 0 : 2;
		$this->_inject($this->_box(8, 1, $separator_bit),  7,  0);
		$this->_inject($this->_box(1, 8, $separator_bit),  0,  7);
		$this->_inject($this->_box(8, 1, $separator_bit),  7, -8);
		$this->_inject($this->_box(1, 8, $separator_bit),  0, -8);
		$this->_inject($this->_box(8, 1, $separator_bit), -8,  0);
		$this->_inject($this->_box(1, 8, $separator_bit), -8,  7);
	}



	/**
	 * Add alignment patterns to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_alignment_patterns($defined = true) {
		$ar_pattern = $this->ar_pattern['align'];
		if(!$defined) {
			foreach($ar_pattern as $k=>$v) {
				foreach($v as $vk => $vv) {
					$ar_pattern[$k][$vk] = 3;
				}
			}
		}

		$check_to = array(
			array(4,4),
			array(4, $this->qr_size-9),
			array($this->qr_size-9, 4)
		);

		$ar_alignment_positions = $this->ar_alignment_positions[$this->version];

		foreach($ar_alignment_positions as $position_x) {
			foreach($ar_alignment_positions as $position_y) {
				$offset_x = $position_x - 2;
				$offset_y = $position_y - 2;

				$check = array($offset_x, $offset_y);
				if( !in_array($check, $check_to)) {
					$this->_inject($ar_pattern, $offset_x, $offset_y);
				}
			}
		}
	}



	/**
	 * Add the dark module to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_dark_module($defined = true) {
		$this->_fill($defined ? 1 : 2, -8, 8);
	}



	/**
	 * Add the format information to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_format_information($defined = true) {
		// Reserve room
		if(!$defined) {
			$this->_inject($this->_box(8, 1),  8, -8, true);
			$this->_inject($this->_box(1, 8), -8,  8, true);
			$this->_inject($this->_box(9, 1),  8,  0, true);
			$this->_inject($this->_box(1, 9),  0,  8, true);
			return;
		}

		// Actual format information here
		$ar = array_reverse(str_split($this->ar_format_string[$this->ecl . $this->mask], 1));

		// Fill the format information bit by bit
		$this->_inv_fill($ar[0],   8,  0); $this->_inv_fill($ar[1],   8,  1); $this->_inv_fill($ar[2],   8,  2);
		$this->_inv_fill($ar[3],   8,  3); $this->_inv_fill($ar[4],   8,  4); $this->_inv_fill($ar[5],   8,  5);
		$this->_inv_fill($ar[6],   8,  7); $this->_inv_fill($ar[7],   8,  8); $this->_inv_fill($ar[8],   7,  8);
		$this->_inv_fill($ar[9],   5,  8); $this->_inv_fill($ar[10],  4,  8); $this->_inv_fill($ar[11],  3,  8);
		$this->_inv_fill($ar[12],  2,  8); $this->_inv_fill($ar[13],  1,  8); $this->_inv_fill($ar[14],  0,  8);
		$this->_inv_fill($ar[0],  -1,  8); $this->_inv_fill($ar[1],  -2,  8); $this->_inv_fill($ar[2],  -3,  8);
		$this->_inv_fill($ar[3],  -4,  8); $this->_inv_fill($ar[4],  -5,  8); $this->_inv_fill($ar[5],  -6,  8);
		$this->_inv_fill($ar[6],  -7,  8); $this->_inv_fill($ar[7],  -8,  8); $this->_inv_fill($ar[8],   8, -7);
		$this->_inv_fill($ar[9],   8, -6); $this->_inv_fill($ar[10],  8, -5); $this->_inv_fill($ar[11],  8, -4);
		$this->_inv_fill($ar[12],  8, -3); $this->_inv_fill($ar[13],  8, -2); $this->_inv_fill($ar[14],  8, -1);
	}



	/**
	 * Add the version information to QR grid
	 *
	 * @param boolean $defined
	 * @return void
	 */
	function add_version_information($defined = true) {
		// Only version 7 and up
		if($this->version < 7) return;

		// Reserve room
		if(!$defined) {
			$this->_inject($this->_box(3, 6), 0, -11);
			$this->_inject($this->_box(6, 3), -11, 0);
		}

		// Actual version information here
		else
		{
			$version_string = $this->ar_version_string[$this->version];
			$version_string = strrev($version_string);

			$tmp = str_split($version_string, 3);
			foreach($tmp as $ttmp) {
				$ar_version_pattern_right[] = str_split($ttmp);
			}

			$ar_version_pattern_left = array();
			foreach($ar_version_pattern_right as $k1 => $tmp1) {
				foreach($tmp1 as $k2 => $tmp2) {
					$ar_version_pattern_left[$k2][$k1] = $tmp2;
				}
			}

			$this->_inject($ar_version_pattern_left,    0, -11);
			$this->_inject($ar_version_pattern_right, -11,   0);
		}
	}



	/**
	 * Add the actual data to the QR grid
	 *
	 * @return void
	 */
	function add_binary_data() {
		$bits = strlen($this->binary_data);
		$ar_coordinate = array('x' => $this->qr_size - 1, 'y' => $this->qr_size - 1);
		$vDirection = $this->UP;
		$hDirection = $this->LEFT;

		for($i=0; $i<$bits; $i++)
		{
			$bit = substr($this->binary_data, $i, 1) * 1;

			while($this->ar_grid[$ar_coordinate['y']][$ar_coordinate['x']] !== false) {
				// Move pointer
				$ar_coordinate['x'] += $hDirection;
				if($hDirection == $this->LEFT) {
					$hDirection = $this->RIGHT;
				}
				else
				{
					$ar_coordinate['y'] += $vDirection;
					$hDirection = $this->LEFT;
				}

				// Check vertical timing pattern
				if($ar_coordinate['x'] == 6) {
					$ar_coordinate['x']--;
				}

				// Check OOB
				if($ar_coordinate['y'] == -1 || $ar_coordinate['y'] == $this->qr_size)
				{
					$vDirection = $vDirection == $this->UP ? $this->DOWN : $this->UP;
					$hDirection = $this->LEFT;

					$ar_coordinate['x'] += $hDirection;
					$ar_coordinate['x'] += $hDirection;
					$ar_coordinate['y'] += $vDirection;
				}
			}

			$this->ar_grid[$ar_coordinate['y']][$ar_coordinate['x']] = $bit;
		}
	}



	/**
	 * Define the mask that should be used
	 *
	 * 0 ) SCORE = 0
	 * 1a) Row with 5 or more black pixels: SCORE += (row length - 2)
	 * 1b) Column with 5 or more black pixels: SCORE += (column length - 2)
	 * 1c) Row with 5 or more white pixels: SCORE += (row length - 2)
	 * 1d) Column with 5 or more white pixels: SCORE += (column length - 2)
	 * 2a) Number of 2x2 black pixel blocks: SCORE += (3 x number)
	 * 2b) Number of 2x2 white pixel blocks: SCORE += (3 x nubmer)
	 * 3a) 10111010000 in a row: SCORE += 40
	 * 3b) 00001011101 in a column: SCORE += 40
	 * 3c) 10111010000 in a row: SCORE += 40
	 * 3d) 00001011101 in a column: SCORE += 40
	 * 4 ) SCORE += 10 * min( abs((ceil(20 * # of 1s / size^2) * 5) - 50) / 5), abs((ceil(20 * # of 1s / size^2) * 5) - 50) / 5) )
	 * 
	 * @return void
	 */
	function choose_mask() {
		$this->log[] = '';
		$this->log[] = 'Choosing a mask based on penalizations...';

		// Make a grid backup
		$ar_grid_backup  = $this->ar_grid;
		$ar_mask_penalty = array();

		// Test all masks
		for($cmask = 0; $cmask <= 7; $cmask++) {

			// Use original grid
			$this->ar_grid = $ar_grid_backup;

			// Apply current mask
			$this->mask = $cmask;
			$this->apply_mask();
			$this->add_patterns(true);

			// Reset score
			$score = 0;

			// Create 90 degrees turned grid
			$ar_grid_reversed = array();
			foreach($this->ar_grid as $k1 => $v1) {
				foreach($v1 as $k2 => $v2) {
					$ar_grid_reversed[$k2][$k1] = $v2;
				}
			}

			// Create strings
			$str_grid = '';
			foreach($this->ar_grid as $row) {
				$str_grid .= implode('', $row) . '_';
			}
			$str_grid = rtrim($str_grid, '_');

			$str_grid_reversed = '';
			foreach($ar_grid_reversed as $row) {
				$str_grid_reversed .= implode('', $row) . '_';
			}
			$str_grid_reversed = rtrim($str_grid_reversed, '_');

			// Check 1a
			$ar_test = explode('_', $str_grid);
			foreach($ar_test as $test) {
				$ar_subtest = explode('0', $test);
				foreach($ar_subtest as $subtest) {
					$length = strlen($subtest);
					if($length >= 5) {
						$score += $length - 2;
					}
				}
			}
			$this->log[] = 'Check 1a: ' . $score;

			// Check 1b
			$ar_test = explode('_', $str_grid_reversed);
			foreach($ar_test as $test) {
				$ar_subtest = explode('0', $test);
				foreach($ar_subtest as $subtest) {
					$length = strlen($subtest);
					if($length >= 5) {
						$score += $length - 2;
					}
				}
			}
			$this->log[] = 'Check 1b: ' . $score;

			// Check 1c
			$ar_test = explode('_', $str_grid);
			foreach($ar_test as $test) {
				$ar_subtest = explode('1', $test);
				foreach($ar_subtest as $subtest) {
					$length = strlen($subtest);
					if($length >= 5) {
						$score += $length - 2;
					}
				}
			}
			$this->log[] = 'Check 1c: ' . $score;

			// Check 1d
			$ar_test = explode('_', $str_grid_reversed);
			foreach($ar_test as $test) {
				$ar_subtest = explode('1', $test);
				foreach($ar_subtest as $subtest) {
					$length = strlen($subtest);
					if($length >= 5) {
						$score += $length - 2;
					}
				}
			}
			$this->log[] = 'Check 1d: ' . $score;

			// Check 2a & 2b
			for($x=0; $x < $this->qr_size - 1; $x++) {
				for($y=0; $y < $this->qr_size - 1; $y++) {
					$test = $this->ar_grid[$x][$y]
						. $this->ar_grid[$x+1][$y]
						. $this->ar_grid[$x][$y+1]
						. $this->ar_grid[$x+1][$y+1];

					if($test == '0000' || $test == '1111') {
						$score += 3;
					}
				}
			}

			$this->log[] = 'Check 2 : ' . $score;

			// Check 3
			$strlen = strlen($str_grid);
			for($x=0; $x<$strlen-11; $x++) {
				if(substr($str_grid, $x, 11) == '10111010000') {
					$score += 40;
				}

				if(substr($str_grid, $x, 11) == '00001011101') {
					$score += 40;
				}

				if(substr($str_grid_reversed, $x, 11) == '10111010000') {
					$score += 40;
				}

				if(substr($str_grid_reversed, $x, 11) == '00001011101') {
					$score += 40;
				}
			}
			$this->log[] = 'Check 3 : ' . $score;

			// Check 4
			$str_grid = str_replace('_', '', $str_grid);
			$pct = 100 * substr_count($str_grid, '1') / strlen($str_grid);
			$this->log[] = 'Pct 4: '. $pct;
			$score += min(abs(floor( ($pct-50) / 5)), abs(ceil( ($pct-50) / 5))) * 5;
			$this->log[] = 'Check 4 : ' . $score;

			$ar_mask_penalty[$cmask] = $score;
		}

		// Set mask with lowest penalty
		$min = false;
		foreach($ar_mask_penalty as $mask => $penalty) {
			if($penalty < $min || $min === false) {
				$min = $penalty;
				$this->mask = $mask;
			}
		}
		$this->log[] = 'Mask: ' . $this->mask;

		// Reset unmasked grid
		$this->ar_grid = $ar_grid_backup;

		// Currently set via constructor
		$this->log[] = 'Mask defined by penalization: ' . $this->mask;
	}



	/**
	 * Apply the mask to the QR code
	 *
	 * @return void
	 */
	function apply_mask() {
		foreach($this->ar_grid as $y => $ar) {
			foreach($ar as $x => $value) {
				if($value > 1) continue;

				if( $this->_mask($x, $y, $this->mask) ) {
					$this->ar_grid[$y][$x] = 1 - $value;
				}
			}
		}
		$this->log[] = 'Mask applied to grid.';
		$this->log[] = '';
	}



	/**
	 * Helper function to make a binary padded string from an integer
	 *
	 * @param int $input
	 * @param int $length
	 * @return void
	 */
	function _binarize($input = 0, $length = 8) {
		return str_pad(decbin($input), $length, '0', STR_PAD_LEFT);
	}



	/**
	 * Helper function to create a box array for the grid
	 *
	 * @param int $width
	 * @param int $height
	 * @param mixed $value
	 * @return int[]
	 */
	function _box($width, $height, $value = 2) {
		return array_fill(0, $width, array_fill(0, $height, $value));
	}



	/**
	 * Fill a pixel in the QR grid
	 *
	 * @param int $value
	 * @param int $row
	 * @param int $col
	 * @return void
	 */
	function _fill($value, $row, $col) {
		if($row < 0) $row += $this->qr_size;
		if($col < 0) $col += $this->qr_size;
		$this->ar_grid[$row][$col] = $value;
	}



	/**
	 * Inverted fill function (_fill with row/col exchanged)
	 *
	 * @param int $value
	 * @param int $col
	 * @param int $row
	 * @return void
	 */
	function _inv_fill($value, $col, $row) {
		$this->_fill($value, $row, $col);
	}



	/**
	 * Insert $ar_pattern into the QR grid
	 * Start at coordinates ($offset_x, $offset_y)
	 * Skip a field if it is filled and $skip_filled is true
	 * 
	 * @param int[] $ar_pattern
	 * @param int $offset_x
	 * @param int $offset_y
	 * @param bool $skip_filled
	 * @return void
	 */
	function _inject($ar_pattern, $offset_x = 0, $offset_y = 0, $skip_filled = false) {
		if($offset_x < 0) $offset_x += $this->qr_size;
		if($offset_y < 0) $offset_y += $this->qr_size;

		foreach($ar_pattern as $row => $ar_row) {
			$row += $offset_y;
			foreach($ar_row as $col => $value) {
				$col += $offset_x;
				if(!$skip_filled || $this->ar_grid[$row][$col] === false) {
					$this->_fill($value, $row, $col);
				}
			}
		}
	}



	/**
	 * Check if mask applies to current cell in the QR grid
	 * 
	 * @param int $col
	 * @param int $row
	 * @param int $mask
	 * @return boolean
	 */
	function _mask($col, $row, $mask = 0) {
		switch($mask) {
			case 1:
				return $row % 2 == 0;

			case 2:
				return $col % 3 == 0;

			case 3:
				return ($col + $row) % 3 == 0;

			case 4:
				return ( floor($row / 2) + floor($col / 3) ) % 2 == 0;

			case 5:
				return (($row * $col) % 2) + (($row * $col) % 3) == 0;

			case 6:
				return ( (($row * $col) % 2) + (($row * $col) % 3) ) % 2 == 0;

			case 7:
				return ( (($row + $col) % 2) + (($row * $col) % 3) ) % 2 == 0;

			case 0: default:
				return ($col + $row) % 2 == 0;
		}
	}



	/**
	 * Return an HTML table formatted QR code
	 * 
	 * @return string
	 */
	function return_html() {

		$html = '<table>';
		foreach($this->ar_grid as $ar_row) {
			$html .= '<tr>';

			foreach($ar_row as $col) {
				$html .= '<td class="' . ($col === false ? 'td' : 'm'.$col) . '"></td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
		return $html;
	}



	/**
	 * Return the QR code as a multidimensional array
	 * 
	 * @return int[][]
	 */
	function return_data() {
		return $this->ar_grid;
	}



	/**
	 * Output the QR code as an image
	 * 
	 * @return void
	 */
	function return_image($pixel_size = null, $padding = null) {

		// Set or override pixel size
		$pixel_size *= 1;
		if(empty($pixel_size)) $pixel_size = 4;
		$pixel_size = min($pixel_size, 10);

		// Set or override padding
		$padding *= 1;
		if(empty($padding)) $padding = 3;
		$padding = min($padding, 5);

		// Create image identifier
		$width  = $pixel_size * (4 + count($this->ar_grid[0]));
		$height = $pixel_size * (4 + count($this->ar_grid));
		$im = imagecreatetruecolor($width, $height);

		// Allocate colors
		$white = imagecolorallocate($im, 255, 255, 255);
		$black = imagecolorallocate($im,   0,   0,   0);
		imagefill($im, 1, 1, $white);

		// Color pixels
		foreach($this->ar_grid as $row_number => $ar_row) {
			foreach($ar_row as $col_number => $tile) {
				$x1 = (2 * $pixel_size) + ($col_number * $pixel_size);
				$x2 = $x1 + $pixel_size - 1;
				$y1 = (2 * $pixel_size) + ($row_number * $pixel_size);
				$y2 = $y1 + $pixel_size - 1;
				$color = $tile ? $black : $white;
				imagefilledrectangle($im, $x1, $y1, $x2, $y2, $color);
			}
		}

		ob_start();
        imagepng($im);
        $stringdata = ob_get_contents();
        imagedestroy($im);
        ob_end_clean();
        return 'data:image/png;base64,' . base64_encode($stringdata);
	}


	/**
	 * __destruct function for debug / log output purposes
	 * 
	 * @return void
	 */
	function __destruct() {
		if($this->debug) {
			echo '<pre>'; print_r($this->log); echo '</pre>';
		}
	}

    // Keep all the remaining methods from both classes...
}
?>