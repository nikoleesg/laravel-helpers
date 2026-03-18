<?php

namespace Nikoleesg\LaravelHelpers\Faker\Providers;

use Faker\Provider\en_SG\Person;

class SingaporePersonProvider extends Person
{
    protected static $lastName = [
        'Tan', 'Lim', 'Lee', 'Ng', 'Ong', 'Wong', 'Goh', 'Chua', 'Chan', 'Koh',
        'Teo', 'Ang', 'Poh', 'Neo', 'Sim', 'Chong', 'Chia', 'Yeo', 'Tay', 'Low'
    ];

    protected static $firstNameMale = [
        'Wei Jie', 'Jun Jie', 'Hao', 'Ming', 'Wei', 'Da', 'Qiang', 'Guo', 'An',
        'Gang', 'Bo', 'Wen', 'Chao', 'Cheng', 'Jian', 'Zhi', 'Hui', 'Xin', 'Long'
    ];

    protected static $firstNameFemale = [
        'Jing', 'Ting', 'Mei', 'Fang', 'Li', 'Min', 'Yan', 'Hua', 'Lan', 'Lian',
        'Ai', 'Yu', 'Shu', 'Qing', 'Na', 'Xia', 'Yun', 'Zhen', 'Ling', 'Xiu', 'Qun'
    ];

    protected static $formats = [
        '{{lastName}} {{firstName}}',
    ];

    protected static $maleNameFormats = [
        '{{lastName}} {{firstNameMale}}',
    ];

    protected static $femaleNameFormats = [
        '{{lastName}} {{firstNameFemale}}',
    ];
}
